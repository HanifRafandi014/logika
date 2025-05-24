import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.decomposition import PCA
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score, davies_bouldin_score
import os

def run_clustering_and_recommendation(data, lomba_pilihan):
    df = pd.DataFrame(data)

    # Cek kolom yang dibutuhkan sesuai lomba
    required_cols = []
    if lomba_pilihan == 'duta-logika':
        required_cols = ['Matematika', 'IPA']
    elif lomba_pilihan == 'semaphore':
        required_cols = ['Bahasa Indonesia', 'Bahasa Inggris']
    elif lomba_pilihan == 'tik':
        required_cols = ['Skor Tes TIK', 'Status Tes TIK']
    else:
        return {"error": "Lomba tidak dikenali. Harap pilih dari ['duta-logika', 'semaphore', 'tik']"}, 400

    # Pastikan semua kolom yang dibutuhkan ada di dataframe input
    if not all(col in df.columns for col in required_cols):
        missing_cols = [col for col in required_cols if col not in df.columns]
        return {"error": f"Kolom yang dibutuhkan tidak ditemukan: {', '.join(missing_cols)}. Harap pastikan data input memiliki semua kolom yang diperlukan."}, 400

    # Inisialisasi cluster_categories
    cluster_categories = {}

    # Logika penentuan Cluster awal dan Kategori
    if lomba_pilihan == 'duta-logika':
        def tentukan_cluster_duta_logika(row):
            if row['Matematika'] > 80 and row['IPA'] > 80:
                return 2  # Unggul keduanya
            elif row['Matematika'] > row['IPA']:
                return 1  # Unggul Matematika
            else:
                return 0  # Unggul IPA
        df['Cluster_Initial'] = df.apply(tentukan_cluster_duta_logika, axis=1)
        cluster_categories = {
            0: "Siswa unggul IPA",
            1: "Siswa unggul Matematika",
            2: "Siswa yang unggul keduanya"
        }
    elif lomba_pilihan == 'semaphore':
        def tentukan_cluster_semaphore(row):
            if row['Bahasa Indonesia'] > 80 and row['Bahasa Inggris'] > 80:
                return 2  # Unggul keduanya
            elif row['Bahasa Inggris'] > row['Bahasa Indonesia']:
                return 1  # Unggul Bahasa Inggris
            else:
                return 0  # Unggul Bahasa Indonesia
        df['Cluster_Initial'] = df.apply(tentukan_cluster_semaphore, axis=1)
        cluster_categories = {
            0: "Siswa unggul Bahasa Indonesia",
            1: "Siswa unggul Bahasa Inggris",
            2: "Siswa yang unggul keduanya"
        }
    elif lomba_pilihan == 'tik':
        def tentukan_cluster_tik(row):
            if row['Skor Tes TIK'] > 80 and row['Status Tes TIK'] > 80:
                return 2  # Unggul keduanya
            elif row['Status Tes TIK'] > row['Skor Tes TIK']:
                return 1  # Unggul Status Tes TIK
            else:
                return 0  # Unggul Skor Tes TIK
        df['Cluster_Initial'] = df.apply(tentukan_cluster_tik, axis=1)
        cluster_categories = {
            0: "Siswa unggul Skor Tes TIK",
            1: "Siswa unggul Status Tes TIK",
            2: "Siswa yang unggul keduanya"
        }

    # Pilih kolom untuk clustering
    akademik_columns = ["Matematika", "Olahraga", "IPA", "IPS", "Bahasa Indonesia", "Bahasa Inggris"]
    non_akademik_columns = [
        "Skor Tes Bahasa", "Status Tes Bahasa", "Skor Tes TIK", "Status Tes TIK",
        "Kehadiran", "Status SKU", "Pencapaian TKK", "Status TKK",
        "Skor Penerapan", "Hasta Karya"
    ]

    # Filter kolom yang benar-benar ada di dataframe input
    # Ini penting karena data input dari Laravel mungkin tidak memiliki semua kolom ini.
    # Ambil hanya kolom yang relevan dan ada di df
    relevant_columns = [col for col in (akademik_columns + non_akademik_columns) if col in df.columns]

    # Pastikan ada kolom numerik untuk clustering
    if not relevant_columns:
        return {"error": "Tidak ada kolom numerik yang relevan untuk proses clustering. Pastikan data input mengandung kolom-kolom seperti nilai akademik atau non-akademik."}, 400

    df_combined = df[relevant_columns].copy()

    # Normalisasi
    scaler = StandardScaler()
    normalized_data = scaler.fit_transform(df_combined)
    df_normalized = pd.DataFrame(normalized_data, columns=df_combined.columns)

    # PCA
    # Cek apakah jumlah komponen PCA lebih kecil dari jumlah fitur
    n_components_pca = min(2, df_normalized.shape[1])
    if n_components_pca == 0: # Pastikan ada setidaknya 1 fitur untuk PCA
        return {"error": "Tidak cukup fitur numerik untuk melakukan PCA."}, 400

    pca = PCA(n_components=n_components_pca)
    df_pca = pca.fit_transform(df_normalized)

    # Clustering akhir dengan k = 3
    k = 3
    final_kmeans = KMeans(n_clusters=k, random_state=42, n_init=10)
    df['Cluster'] = final_kmeans.fit_predict(df_pca)

    # Hitung silhouette score
    try:
        score = silhouette_score(df_pca, df['Cluster'])
    except Exception as e:
        score = "N/A" # Tidak bisa dihitung jika hanya 1 cluster atau data terlalu sedikit

    # Tambah kolom kategori berdasarkan cluster
    df['Kategori'] = df['Cluster'].map(cluster_categories)

    # Hitung Davies-Bouldin Index
    try:
        dbi_score = davies_bouldin_score(df_pca, df['Cluster'])
    except Exception as e:
        dbi_score = "N/A" # Tidak bisa dihitung jika hanya 1 cluster atau data terlalu sedikit

    # Ambil 3 siswa terbaik dari tiap cluster berdasarkan kriteria lomba
    hasil_rekomendasi = []
    for cluster_id in sorted(df['Cluster'].unique()):
        df_cluster = df[df['Cluster'] == cluster_id]

        top_siswa = pd.DataFrame() # Inisialisasi dengan DataFrame kosong

        if lomba_pilihan == 'duta-logika':
            if cluster_id == 0: # Unggul IPA
                top_siswa = df_cluster.sort_values(by='IPA', ascending=False).head(3)
            elif cluster_id == 1: # Unggul Matematika
                top_siswa = df_cluster.sort_values(by='Matematika', ascending=False).head(3)
            else: # Unggul keduanya
                top_siswa = df_cluster.assign(Total=lambda x: x['Matematika'] + x['IPA']) \
                                      .sort_values(by='Total', ascending=False).head(3)
        elif lomba_pilihan == 'semaphore':
            if cluster_id == 0: # Unggul Bahasa Indonesia
                top_siswa = df_cluster.sort_values(by='Bahasa Indonesia', ascending=False).head(3)
            elif cluster_id == 1: # Unggul Bahasa Inggris
                top_siswa = df_cluster.sort_values(by='Bahasa Inggris', ascending=False).head(3)
            else: # Unggul keduanya
                top_siswa = df_cluster.assign(Total=lambda x: x['Bahasa Indonesia'] + x['Bahasa Inggris']) \
                                      .sort_values(by='Total', ascending=False).head(3)
        elif lomba_pilihan == 'tik':
            if cluster_id == 0: # Unggul Skor Tes TIK
                top_siswa = df_cluster.sort_values(by='Skor Tes TIK', ascending=False).head(3)
            elif cluster_id == 1: # Unggul Status Tes TIK
                top_siswa = df_cluster.sort_values(by='Status Tes TIK', ascending=False).head(3)
            else: # Unggul keduanya
                top_siswa = df_cluster.assign(Total=lambda x: x['Skor Tes TIK'] + x['Status Tes TIK']) \
                                      .sort_values(by='Total', ascending=False).head(3)

        if not top_siswa.empty:
            hasil_rekomendasi.append(top_siswa)

    df_rekomendasi = pd.concat(hasil_rekomendasi) if hasil_rekomendasi else pd.DataFrame()

    return {
        "message": f"Clustering dan Rekomendasi untuk lomba '{lomba_pilihan}' berhasil.",
        "silhouette_score": round(score, 4) if isinstance(score, float) else score,
        "davies_bouldin_index": round(dbi_score, 4) if isinstance(dbi_score, float) else dbi_score,
        "full_data_with_cluster": df.to_dict(orient='records'),
        "recommendations": df_rekomendasi.to_dict(orient='records')
    }