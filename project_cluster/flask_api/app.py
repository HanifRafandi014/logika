from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os
from model import run_clustering_and_recommendation # Import fungsi dari model.py

app = Flask(__name__)
CORS(app) # Mengizinkan CORS agar Laravel bisa mengaksesnya

# Lokasi file data Excel (relatif terhadap app.py)
# Gunakan os.path.join untuk platform independent path
EXCEL_FILE_PATH = os.path.join(os.path.dirname(__file__), 'data_dummy_adjusted_final.xlsx')
SHEET_NAME = 'Sheet1'

@app.route('/cluster-and-recommend', methods=['POST'])
def cluster_and_recommend():
    try:
        # Ambil data dari request JSON
        request_data = request.json
        lomba_pilihan = request_data.get('lomba_pilihan')

        if not lomba_pilihan:
            return jsonify({"error": "Parameter 'lomba_pilihan' tidak ditemukan."}), 400

        # Muat data dari Excel (ini akan dilakukan setiap kali request)
        # Jika data Anda besar dan tidak berubah, pertimbangkan untuk memuatnya sekali
        # saat aplikasi dimulai atau menyimpan model yang sudah dilatih.
        try:
            df_excel = pd.read_excel(EXCEL_FILE_PATH, sheet_name=SHEET_NAME)
            # Pastikan kolom 'Nama' ada dan relevan untuk rekomendasi
            if 'Nama' not in df_excel.columns:
                return jsonify({"error": "Kolom 'Nama' tidak ditemukan di file Excel."}), 400

            # Convert DataFrame ke list of dicts untuk diteruskan ke fungsi clustering
            data_from_excel = df_excel.to_dict(orient='records')
        except FileNotFoundError:
            return jsonify({"error": f"File Excel tidak ditemukan: {EXCEL_FILE_PATH}"}), 500
        except Exception as e:
            return jsonify({"error": f"Gagal membaca file Excel: {str(e)}"}), 500

        # Jalankan fungsi clustering dan rekomendasi
        results = run_clustering_and_recommendation(data_from_excel, lomba_pilihan)

        # Cek jika ada error dari fungsi model
        if isinstance(results, tuple) and len(results) == 2 and isinstance(results[0], dict) and "error" in results[0]:
            return jsonify(results[0]), results[1]

        return jsonify(results), 200

    except Exception as e:
        return jsonify({"error": f"Terjadi kesalahan internal: {str(e)}"}), 500

if __name__ == '__main__':
    # Pastikan file Excel ada saat startup
    if not os.path.exists(EXCEL_FILE_PATH):
        print(f"ERROR: File Excel tidak ditemukan di {EXCEL_FILE_PATH}")
        print("Pastikan 'data_dummy_adjusted_final.xlsx' berada di folder 'flask_api'.")
    else:
        print(f"File Excel ditemukan: {EXCEL_FILE_PATH}")

    app.run(host='0.0.0.0', port=5000, debug=True) # Jalankan di port 5000