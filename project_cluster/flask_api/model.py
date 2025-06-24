import warnings
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.decomposition import PCA
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score, davies_bouldin_score
import numpy as np
import traceback # Import traceback untuk debugging error

# Mengabaikan peringatan KMeans (terutama terkait n_init)
warnings.filterwarnings("ignore", category=FutureWarning)

class StudentRecommender:
    def __init__(self, competitions_data_config, data_file_path):
        self.competitions_data = competitions_data_config
        self.data_file_path = data_file_path
        self.df_main = None
        self.kmeans_model = None
        self.scaler = None
        self.pca = None
        self.all_features = []
        self.cluster_to_lomba_map = {}
        self.lomba_to_cluster_map = {}
        self.k_final = 0
        self._all_generated_recommendations_df = None # Untuk menyimpan hasil rekomendasi final

    def load_and_preprocess_data(self):
        try:
            self.df_main = pd.read_excel(self.data_file_path)
        except UnicodeDecodeError:
            try:
                self.df_main = pd.read_excel(self.data_file_path)
            except Exception as e:
                raise Exception(f"Error saat memuat data dari excel (setelah mencoba latin1 dan cp1252): {e}\n{traceback.format_exc()}")
        except FileNotFoundError:
            raise Exception(f"Error: File '{self.data_file_path}' tidak ditemukan. Pastikan file ada di lokasi yang ditentukan dan dapat diakses.")
        except Exception as e:
            raise Exception(f"Error saat memuat data dari excel: {e}\n{traceback.format_exc()}")

        # Kumpulkan semua fitur yang digunakan oleh semua lomba
        self.all_features = sorted(list(set(var for comp in self.competitions_data for var in comp['Variabel yang Digunakan'])))

        # Hapus fitur dari daftar yang tidak ada di DataFrame
        missing_features = [f for f in self.all_features if f not in self.df_main.columns]
        if missing_features:
            self.all_features = [f for f in self.all_features if f not in missing_features]

        if not self.all_features:
            raise ValueError("Tidak ada fitur yang valid untuk clustering. Harap periksa kembali konfigurasi lomba dan data siswa.")

        df_clustering_data = self.df_main[self.all_features].copy()

        # Tangani nilai yang hilang dengan mengisi rata-rata kolom
        for col in df_clustering_data.columns:
            if df_clustering_data[col].isnull().any():
                df_clustering_data[col].fillna(df_clustering_data[col].mean(), inplace=True)

        # Normalisasi data
        self.scaler = StandardScaler()
        normalized_data = self.scaler.fit_transform(df_clustering_data)
        self.df_normalized = pd.DataFrame(normalized_data, columns=self.all_features)

        # Reduksi dimensi menggunakan PCA
        n_components_pca = min(2, self.df_normalized.shape[1], self.df_normalized.shape[0] - 1)
        if n_components_pca < 1:
            self.df_pca = self.df_normalized
        else:
            self.pca = PCA(n_components=n_components_pca)
            self.df_pca = self.pca.fit_transform(self.df_normalized)

    def perform_clustering(self):
        NUM_CLUSTERS = len(self.competitions_data)
        self.k_final = min(NUM_CLUSTERS, self.df_pca.shape[0] - 1)
        if self.k_final < 1:
            raise Exception("Tidak cukup data (siswa) untuk melakukan clustering.")
        elif self.k_final < NUM_CLUSTERS:
            pass # Removed print for debugging

        self.kmeans_model = KMeans(n_clusters=self.k_final, random_state=42, n_init=10)
        self.df_main['Cluster'] = self.kmeans_model.fit_predict(self.df_pca)

        # Pemetaan Cluster ID ke nama Lomba
        self.cluster_to_lomba_map = {}
        self.lomba_to_cluster_map = {}
        for i in range(self.k_final):
            if i < len(self.competitions_data):
                lomba_name = self.competitions_data[i]['Lomba']
                self.cluster_to_lomba_map[i] = lomba_name
                self.lomba_to_cluster_map[lomba_name] = i
            else:
                self.cluster_to_lomba_map[i] = f'Tidak Terdefinisi (Cluster {i} Tanpa Lomba Terkait)'

        self.df_main['Kategori Cluster'] = self.df_main['Cluster'].map(self.cluster_to_lomba_map)

    def generate_recommendations(self):
        if self.df_main is None or self.kmeans_model is None:
            raise Exception("Data belum dimuat atau clustering belum dilakukan. Harap panggil load_and_preprocess_data() dan perform_clustering() terlebih dahulu.")

        # Jika rekomendasi sudah pernah di-generate, kembalikan yang sudah ada (untuk caching)
        if self._all_generated_recommendations_df is not None:
            return self._all_generated_recommendations_df.copy() # Return a copy to prevent external modification

        all_recommendations_list = []
        students_assigned_ids = set() # Set untuk melacak ID siswa yang sudah direkomendasikan
        lomba_current_fill_count = {comp['Lomba']: 0 for comp in self.competitions_data} # Lacak berapa siswa yang sudah direkomendasikan untuk setiap lomba

        # --- Fase 1: Rekomendasi berdasarkan Cluster Utama ---
        for comp in self.competitions_data:
            lomba_name = comp['Lomba']
            required_vars = comp['Variabel yang Digunakan']
            required_students = comp['Jumlah Siswa yang Dibutuhkan']

            missing_vars_in_data = [v for v in required_vars if v not in self.df_main.columns]
            if missing_vars_in_data:
                continue

            target_cluster_id = self.lomba_to_cluster_map.get(lomba_name)

            if target_cluster_id is None:
                continue

            students_in_target_cluster = self.df_main[
                (self.df_main['Cluster'] == target_cluster_id) &
                (~self.df_main['Id Siswa'].isin(students_assigned_ids))
            ].copy()

            if students_in_target_cluster.empty:
                continue

            if required_vars:
                students_in_target_cluster['Avg_Required_Score'] = students_in_target_cluster[required_vars].mean(axis=1)
                students_in_target_cluster = students_in_target_cluster.sort_values(
                    by='Avg_Required_Score', ascending=False
                )
            else:
                continue

            selected_students = students_in_target_cluster.head(required_students)

            if selected_students.empty:
                continue

            for index, row in selected_students.iterrows():
                if row['Id Siswa'] not in students_assigned_ids:
                    all_recommendations_list.append({
                        'ID Siswa': row['Id Siswa'],
                        'Nama Siswa': row['Nama'],
                        'Lomba Rekomendasi': lomba_name,
                        'Kategori Cluster': row['Kategori Cluster'],
                        'Rata-rata Skor Lomba': row['Avg_Required_Score'],
                        'Fase Rekomendasi': 'Fase 1 (Cluster)'
                    })
                    students_assigned_ids.add(row['Id Siswa'])
                    lomba_current_fill_count[lomba_name] += 1

        # --- Fase 2: Iterasi untuk mengisi lomba yang belum memenuhi kebutuhan ---
        MAX_ITERATIONS = 5 # Batasi iterasi untuk mencegah infinite loop
        for iteration in range(MAX_ITERATIONS):
            changes_made_in_iteration = False
            
            undersubscribed_competitions = [
                comp for comp in self.competitions_data
                if lomba_current_fill_count[comp['Lomba']] < comp['Jumlah Siswa yang Dibutuhkan']
            ]

            if not undersubscribed_competitions:
                break
            
            df_unassigned_students = self.df_main[~self.df_main['Id Siswa'].isin(students_assigned_ids)].copy()

            if df_unassigned_students.empty:
                break

            potential_assignments_this_iteration = []
            
            for comp_to_fill in undersubscribed_competitions:
                lomba_name = comp_to_fill['Lomba']
                required_vars = comp_to_fill['Variabel yang Digunakan']
                required_students_total = comp_to_fill['Jumlah Siswa yang Dibutuhkan']
                
                num_needed = required_students_total - lomba_current_fill_count[lomba_name]
                
                if num_needed <= 0:
                    continue 

                available_candidates = df_unassigned_students.copy()

                missing_vars_in_candidates = [v for v in required_vars if v not in available_candidates.columns]
                if missing_vars_in_candidates:
                    continue
                
                if not required_vars:
                    continue 
                    
                available_candidates['Avg_Required_Score'] = available_candidates[required_vars].mean(axis=1)
                available_candidates = available_candidates.sort_values(by='Avg_Required_Score', ascending=False)
                
                selected_for_this_lomba = available_candidates.head(num_needed)
                
                for index, row in selected_for_this_lomba.iterrows():
                    if row['Id Siswa'] not in students_assigned_ids: 
                        potential_assignments_this_iteration.append({
                            'ID Siswa': row['Id Siswa'],
                            'Nama Siswa': row['Nama'],
                            'Lomba Rekomendasi': lomba_name,
                            'Kategori Cluster': row['Kategori Cluster'], 
                            'Rata-rata Skor Lomba': row['Avg_Required_Score'],
                            'Fase Rekomendasi': 'Fase 2 (Pengisian)'
                        })
                        students_assigned_ids.add(row['Id Siswa']) 
                        lomba_current_fill_count[lomba_name] += 1
                        changes_made_in_iteration = True
                
            all_recommendations_list.extend(potential_assignments_this_iteration)

            if not changes_made_in_iteration:
                break 
        
        # Simpan DataFrame final untuk caching dan pengurutan
        self._all_generated_recommendations_df = pd.DataFrame(all_recommendations_list).sort_values(
            by=['Lomba Rekomendasi', 'Rata-rata Skor Lomba'], ascending=[True, False]
        )
        
        if self._all_generated_recommendations_df.empty:
            return pd.DataFrame() 
        
        return self._all_generated_recommendations_df.copy() # Mengembalikan salinan