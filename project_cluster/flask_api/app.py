# app.py

from flask import Flask, jsonify, request
from flask_cors import CORS
from sqlalchemy import text
import traceback
import pandas as pd

from model import StudentRecommender
from db import SessionLocal

app = Flask(__name__)
CORS(app, resources={r"/api/*": {"origins": "http://127.0.0.1:8000"}})

def fetch_student_data():
    db = SessionLocal()
    try:
        query = """
        SELECT s.id AS id_siswa, s.nama AS nama_siswa, na.mata_pelajaran AS nama_variabel, na.nilai
        FROM nilai_akademiks na
        JOIN siswas s ON s.id = na.siswa_id
        UNION
        SELECT s.id AS id_siswa, s.nama AS nama_siswa, nn.kategori AS nama_variabel, nn.nilai
        FROM nilai_non_akademiks nn
        JOIN siswas s ON s.id = nn.siswa_id
        """
        df = pd.read_sql(text(query), db.bind)

        # Tambah status SKU
        sku_df = pd.read_sql(text("""
            SELECT siswa_id, MAX(status) AS status_sku
            FROM penilaian_skus
            GROUP BY siswa_id
        """), db.bind)
        sku_df.rename(columns={"siswa_id": "id_siswa", "status_sku": "Status SKU"}, inplace=True)

        # Tambah status SKK
        skk_df = pd.read_sql(text("""
            SELECT siswa_id, MAX(status) AS status_skk
            FROM penilaian_skks
            GROUP BY siswa_id
        """), db.bind)
        skk_df.rename(columns={"siswa_id": "id_siswa", "status_skk": "Status SKK"}, inplace=True)

        # Gabung
        pivot_df = df.pivot_table(
            index=['id_siswa', 'nama_siswa'],
            columns='nama_variabel',
            values='nilai'
        ).reset_index()

        merged_df = pivot_df.merge(sku_df, how='left', on='id_siswa')
        merged_df = merged_df.merge(skk_df, how='left', on='id_siswa')

        merged_df['Status SKU'] = merged_df['Status SKU'].fillna(0)
        merged_df['Status SKK'] = merged_df['Status SKK'].fillna(0)

        # Rename untuk model compatibility
        merged_df.rename(columns={
            'id_siswa': 'ID Siswa',
            'nama_siswa': 'Nama Siswa'
        }, inplace=True)

        return merged_df
    finally:
        db.close()

def fetch_competitions_data():
    db = SessionLocal()
    try:
        query = """
        SELECT l.jumlah_siswa, vc.jenis_lomba, vc.variabel_akademiks, vc.variabel_non_akademiks
        FROM lombas l
        JOIN variabel_clusterings vc ON vc.id = l.variabel_clustering_id
        WHERE l.status = 1
        """
        result = db.execute(text(query))
        competitions = []
        for row in result:
            var_akademik = eval(row.variabel_akademiks or '[]')
            var_non_akademik = eval(row.variabel_non_akademiks or '[]')
            competitions.append({
                'Lomba': row.jenis_lomba,
                'Jumlah Siswa yang Dibutuhkan': row.jumlah_siswa,
                'Variabel yang Digunakan': var_akademik + var_non_akademik
            })
        return competitions
    finally:
        db.close()

# Inisialisasi recommender
recommender = None
try:
    student_df = fetch_student_data()
    competitions_data = fetch_competitions_data()

    recommender = StudentRecommender(competitions_data, student_df)
    recommender.load_and_preprocess_data()
    recommender.perform_clustering()
    recommender.generate_recommendations()

except Exception as e:
    traceback.print_exc()
    recommender = None

@app.route('/')
def home():
    return jsonify({"message": "API Rekomendasi Siswa Pramuka", "status": "running"})

@app.route('/api/recommendations', methods=['GET'])
def get_all_recommendations():
    if recommender is None:
        return jsonify({"error": "Model belum siap."}), 500

    try:
        df = recommender.generate_recommendations()
        if df.empty:
            return jsonify({"message": "Tidak ada rekomendasi yang dihasilkan."}), 200
        return jsonify(df.to_dict(orient='records')), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route('/api/recommendations/<lomba_name>', methods=['GET'])
def get_recommendations_for_lomba(lomba_name):
    if recommender is None:
        return jsonify({"error": "Model belum siap."}), 500

    try:
        clean_lomba = lomba_name.strip().lower()
        df = recommender.generate_recommendations()

        df_filtered = df[df['Lomba Rekomendasi'].str.strip().str.lower() == clean_lomba]
        if df_filtered.empty:
            return jsonify({"message": f"Tidak ada rekomendasi untuk {lomba_name}."}), 200

        required_num = recommender.get_required_student_count(clean_lomba)
        display_df = df_filtered.head(required_num if required_num > 0 else len(df_filtered))

        return jsonify(display_df[[
            'ID Siswa', 'Nama Siswa', 'Lomba Rekomendasi', 'Kategori Cluster', 'Rata-rata Skor Lomba', 'Fase Rekomendasi'
        ]].to_dict(orient='records')), 200

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
