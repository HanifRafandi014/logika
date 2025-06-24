from flask import Flask, jsonify, request
from model import StudentRecommender
import os
from flask_cors import CORS
import traceback # Untuk melacak error lebih detail
import pandas as pd

app = Flask(__name__)

# Konfigurasi CORS: Izinkan permintaan dari frontend Laravel Anda (misalnya, yang berjalan di http://127.0.0.1:8000)
CORS(app, resources={r"/api/*": {"origins": "http://127.0.0.1:8000"}})

# Konfigurasi Lomba (sama dengan yang di model.py)
COMPETITIONS_DATA = [
    {'Lomba': 'Pionering', 'Jumlah Siswa yang Dibutuhkan': 6, 'Variabel yang Digunakan': ['Matematika', 'IPA', 'Skor Penerapan', 'Status SKU', 'Hasta Karya']},
    {'Lomba': 'Administrasi_Regu', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['IPS', 'Bahasa Indonesia', 'Kehadiran', 'Status SKU']},
    {'Lomba': 'Packing_Pengembaraan', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['IPA', 'Skor Penerapan', 'Status SKU', 'Kehadiran']},
    {'Lomba': 'Semboyan_dan_Isyarat', 'Jumlah Siswa yang Dibutuhkan': 7, 'Variabel yang Digunakan': ['Bahasa Indonesia', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Sketsa_Panorama', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['IPS', 'Bahasa Indonesia', 'Hasta Karya']},
    {'Lomba': 'Peta_Pita_dan_Peta_Perjalanan', 'Jumlah Siswa yang Dibutuhkan': 8, 'Variabel yang Digunakan': ['Matematika', 'IPS', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Menaksir', 'Jumlah Siswa yang Dibutuhkan': 8, 'Variabel yang Digunakan': ['Matematika', 'IPA']},
    {'Lomba': 'Pertolongan_Pertama', 'Jumlah Siswa yang Dibutuhkan': 8, 'Variabel yang Digunakan': ['IPA', 'Skor Penerapan', 'Status TKK', 'Pencapaian TKK']},
    {'Lomba': 'Masak_Rimba', 'Jumlah Siswa yang Dibutuhkan': 3, 'Variabel yang Digunakan': ['IPA', 'Status TKK', 'Pencapaian TKK']},
    {'Lomba': 'Bivak', 'Jumlah Siswa yang Dibutuhkan': 3, 'Variabel yang Digunakan': ['IPA', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Obat_dan_Ramuan_Tradisional', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['IPA', 'IPS', 'Status TKK', 'Pencapaian TKK']},
    {'Lomba': 'Baris_Berbaris_Tongkat', 'Jumlah Siswa yang Dibutuhkan': 8, 'Variabel yang Digunakan': ['Olahraga', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Senam_Pramuka', 'Jumlah Siswa yang Dibutuhkan': 8, 'Variabel yang Digunakan': ['Olahraga', 'Skor Penerapan']},
    {'Lomba': 'E-Sport', 'Jumlah Siswa yang Dibutuhkan': 5, 'Variabel yang Digunakan': ['Skor Tes TIK', 'Olahraga', 'Kehadiran']},
    {'Lomba': 'Robotik', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['Skor Tes TIK', 'IPA', 'Matematika', 'Status Tes TIK']},
    {'Lomba': 'Coding', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['Skor Tes TIK', 'Status Tes TIK', 'Matematika', 'Skor Penerapan']},
    {'Lomba': 'Panjat_Dinding', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Olahraga', 'IPA', 'Skor Penerapan']},
    {'Lomba': 'Memanah', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Olahraga', 'Matematika', 'Skor Penerapan']},
    {'Lomba': 'Renang', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Olahraga', 'Skor Penerapan']},
    {'Lomba': 'Halang_Rintang', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Olahraga', 'Kehadiran', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Pidato', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Bahasa Indonesia', 'Bahasa Inggris', 'Skor Tes Bahasa', 'Status Tes Bahasa', 'Status SKU']},
    {'Lomba': 'Melukis_Poster', 'Jumlah Siswa yang Dibutuhkan': 1, 'Variabel yang Digunakan': ['Hasta Karya', 'Bahasa Indonesia', 'Status SKU']},
    {'Lomba': 'Hasta_Karya', 'Jumlah Siswa yang Dibutuhkan': 4, 'Variabel yang Digunakan': ['Hasta Karya', 'Skor Penerapan', 'Status SKU']},
    {'Lomba': 'Reportase', 'Jumlah Siswa yang Dibutuhkan': 2, 'Variabel yang Digunakan': ['Bahasa Indonesia', 'Bahasa Inggris', 'Skor Tes Bahasa', 'Status Tes Bahasa']}
]

# Path ke file data Excel. Pastikan file ini ada di direktori yang sama dengan app.py
DATA_FILE_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'data_dummy_adjusted_final.xlsx')

recommender = None
try:
    recommender = StudentRecommender(COMPETITIONS_DATA, DATA_FILE_PATH)
    recommender.load_and_preprocess_data()
    recommender.perform_clustering()
    recommender.generate_recommendations() # Panggil sekali untuk mengisi cache
except Exception as e:
    traceback.print_exc()
    recommender = None

@app.route('/')
def home():
    return jsonify({"message": "API Rekomendasi Siswa Pramuka", "status": "running"})

@app.route('/api/recommendations', methods=['GET'])
def get_all_recommendations():
    if recommender is None:
        return jsonify({"error": "Model machine learning belum siap atau gagal dimuat. Periksa log server Flask."}), 500

    try:
        recommendations_df = recommender.generate_recommendations()
        
        if recommendations_df.empty:
            return jsonify({"message": "Tidak ada rekomendasi yang dihasilkan. Mungkin tidak ada siswa yang cocok atau data kosong."}), 200

        recommendations_json = recommendations_df.to_dict(orient='records')
        
        return jsonify(recommendations_json), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": f"Gagal menghasilkan semua rekomendasi: {str(e)}"}), 500

@app.route('/api/recommendations/<lomba_name>', methods=['GET'])
def get_recommendations_for_lomba(lomba_name):
    if recommender is None:
        return jsonify({"error": "Model machine learning belum siap atau gagal dimuat. Periksa log server Flask."}), 500

    try:
        clean_lomba_name_from_request = lomba_name.strip().lower()

        all_recommendations_df = recommender.generate_recommendations()
        
        if all_recommendations_df.empty:
            return jsonify({"message": "Tidak ada rekomendasi yang dihasilkan secara keseluruhan."}), 200

        # Filter rekomendasi berdasarkan nama lomba
        lomba_recs = all_recommendations_df[
            all_recommendations_df['Lomba Rekomendasi'].astype(str).str.strip().str.lower() == clean_lomba_name_from_request
        ].copy()
        
        if lomba_recs.empty:
            return jsonify({"message": f"Tidak ada rekomendasi yang dihasilkan untuk lomba '{lomba_name}'. Pastikan nama lomba benar dan ada siswa yang direkomendasikan."}), 200

        # Tentukan jumlah siswa yang dibutuhkan untuk lomba ini dari konfigurasi
        required_num = next((comp['Jumlah Siswa yang Dibutuhkan'] for comp in COMPETITIONS_DATA if comp['Lomba'].strip().lower() == clean_lomba_name_from_request), 0)
        
        display_limit = required_num if required_num > 0 else len(lomba_recs)

        display_df = lomba_recs.head(display_limit)

        # Pilih kolom yang relevan untuk dikirim ke frontend
        recommendations_json = display_df[[
            'ID Siswa', 'Nama Siswa', 'Lomba Rekomendasi', 'Kategori Cluster', 'Rata-rata Skor Lomba', 'Fase Rekomendasi'
        ]].to_dict(orient='records')
        
        return jsonify(recommendations_json), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": f"Gagal menghasilkan rekomendasi untuk {lomba_name}: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)