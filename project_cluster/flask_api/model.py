# Rewriting the StudentRecommender class to include Fase 3 (Versatile), ranking, and threshold logic
import warnings
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.decomposition import PCA
from sklearn.cluster import KMeans

warnings.filterwarnings("ignore", category=FutureWarning)

class StudentRecommender:
    def __init__(self, competitions_data_config, student_scores_df):
        self.competitions_data = competitions_data_config
        self.df_main = student_scores_df.copy()
        self.kmeans_model = None
        self.scaler = None
        self.pca = None
        self.df_pca = None
        self.all_features = []
        self.cluster_to_lomba_map = {}
        self.lomba_to_cluster_map = {}
        self.k_final = 0
        self._all_generated_recommendations_df = None

    def load_and_preprocess_data(self):
        if self.df_main is None or self.df_main.empty:
            raise ValueError("Data siswa kosong atau tidak tersedia.")

        # Ambil semua variabel dari semua lomba
        self.all_features = sorted(
            list(set(var for comp in self.competitions_data for var in comp['Variabel yang Digunakan']))
        )

        # Hanya pakai variabel yang ada di data siswa
        self.all_features = [f for f in self.all_features if f in self.df_main.columns]

        if not self.all_features:
            raise ValueError("Tidak ada fitur yang cocok antara data siswa dan konfigurasi lomba.")

        df_clustering_data = self.df_main[self.all_features].copy()

        # Isi nilai kosong dengan rata-rata kolom
        df_clustering_data = df_clustering_data.fillna(df_clustering_data.mean())

        # Normalisasi
        self.scaler = StandardScaler()
        normalized_data = self.scaler.fit_transform(df_clustering_data)
        self.df_normalized = pd.DataFrame(normalized_data, columns=self.all_features)

        # PCA
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
            raise Exception("Tidak cukup data siswa untuk clustering.")

        self.kmeans_model = KMeans(n_clusters=self.k_final, random_state=42, n_init=10)
        self.df_main['Cluster'] = self.kmeans_model.fit_predict(self.df_pca)

        # Pemetaan cluster ke lomba
        self.cluster_to_lomba_map = {}
        self.lomba_to_cluster_map = {}
        for i in range(self.k_final):
            if i < len(self.competitions_data):
                lomba_name = self.competitions_data[i]['Lomba']
                self.cluster_to_lomba_map[i] = lomba_name
                self.lomba_to_cluster_map[lomba_name] = i
            else:
                self.cluster_to_lomba_map[i] = f'Cluster {i} (Tidak Terdefinisi)'

        self.df_main['Kategori Cluster'] = self.df_main['Cluster'].map(self.cluster_to_lomba_map)

    def generate_recommendations(self):
        if self.df_main is None or self.kmeans_model is None:
            raise Exception("Clustering belum dilakukan.")

        if self._all_generated_recommendations_df is not None:
            return self._all_generated_recommendations_df.copy()

        all_recommendations_list = []
        students_assigned_ids = set()
        lomba_current_fill_count = {comp['Lomba']: 0 for comp in self.competitions_data}

        # === FASE 1: berdasarkan cluster ===
        for comp in self.competitions_data:
            lomba_name = comp['Lomba']
            required_vars = [v for v in comp['Variabel yang Digunakan'] if v in self.df_main.columns]
            required_students = comp['Jumlah Siswa yang Dibutuhkan']

            if not required_vars:
                continue

            target_cluster_id = self.lomba_to_cluster_map.get(lomba_name)
            if target_cluster_id is None:
                continue

            cluster_students = self.df_main[
                (self.df_main['Cluster'] == target_cluster_id) &
                (~self.df_main['ID Siswa'].isin(students_assigned_ids))
            ].copy()

            cluster_students['Avg_Required_Score'] = cluster_students[required_vars].mean(axis=1)
            cluster_students = cluster_students.sort_values(by='Avg_Required_Score', ascending=False)

            selected = cluster_students.head(required_students)

            for _, row in selected.iterrows():
                if row['ID Siswa'] not in students_assigned_ids:
                    all_recommendations_list.append({
                        'ID Siswa': row['ID Siswa'],
                        'Nama Siswa': row['Nama Siswa'],
                        'Lomba Rekomendasi': lomba_name,
                        'Kategori Cluster': row['Kategori Cluster'],
                        'Rata-rata Skor Lomba': row['Avg_Required_Score'],
                        'Fase Rekomendasi': 'Fase 1 (Cluster)'
                    })
                    students_assigned_ids.add(row['ID Siswa'])
                    lomba_current_fill_count[lomba_name] += 1

        # === FASE 2: isi kekurangan dengan siswa sisa ===
        for _ in range(10):  # max 10 iterasi
            df_unassigned = self.df_main[~self.df_main['ID Siswa'].isin(students_assigned_ids)].copy()
            if df_unassigned.empty:
                break

            changes = False
            for comp in self.competitions_data:
                lomba_name = comp['Lomba']
                required_vars = [v for v in comp['Variabel yang Digunakan'] if v in df_unassigned.columns]
                required_students = comp['Jumlah Siswa yang Dibutuhkan']
                sisa = required_students - lomba_current_fill_count[lomba_name]
                if sisa <= 0 or not required_vars:
                    continue

                df_unassigned['Avg_Required_Score'] = df_unassigned[required_vars].mean(axis=1)
                df_unassigned_sorted = df_unassigned.sort_values(by='Avg_Required_Score', ascending=False)
                selected = df_unassigned_sorted.head(sisa)

                for _, row in selected.iterrows():
                    if row['ID Siswa'] not in students_assigned_ids:
                        all_recommendations_list.append({
                            'ID Siswa': row['ID Siswa'],
                            'Nama Siswa': row['Nama Siswa'],
                            'Lomba Rekomendasi': lomba_name,
                            'Kategori Cluster': row['Kategori Cluster'],
                            'Rata-rata Skor Lomba': row['Avg_Required_Score'],
                            'Fase Rekomendasi': 'Fase 2 (Pengisian)'
                        })
                        students_assigned_ids.add(row['ID Siswa'])
                        lomba_current_fill_count[lomba_name] += 1
                        changes = True

            if not changes:
                break

        self._all_generated_recommendations_df = pd.DataFrame(all_recommendations_list).sort_values(
            by=['Lomba Rekomendasi', 'Rata-rata Skor Lomba'], ascending=[True, False]
        )

        return self._all_generated_recommendations_df.copy()

    def generate_versatile_students(self, threshold=30, top_n=8):
        if self._all_generated_recommendations_df is None:
            self.generate_recommendations()

        recommended_ids = set(self._all_generated_recommendations_df['ID Siswa'].unique())
        df_eligible = self.df_main[self.df_main['ID Siswa'].isin(recommended_ids)].copy()
        candidates = []

        for _, row in df_eligible.iterrows():
            count = 0
            total_score = 0
            for comp in self.competitions_data:
                required_vars = [v for v in comp['Variabel yang Digunakan'] if v in row.index]
                if not required_vars:
                    continue
                score = row[required_vars].mean()
                if pd.notna(score) and score > threshold:
                    count += 1
                    total_score += score
            if count > 1:
                candidates.append({
                    'ID Siswa': row['ID Siswa'],
                    'Nama Siswa': row['Nama Siswa'],
                    'Jumlah Lomba Potensial': count,
                    'Total Skor Potensial': round(total_score, 2)
                })

        df_versatile = pd.DataFrame(candidates).sort_values(
            by=['Jumlah Lomba Potensial', 'Total Skor Potensial'],
            ascending=[False, False]
        ).head(top_n)

        self._versatile_students_df = df_versatile.copy()
        return df_versatile.copy()

    def generate_final_assignments_from_versatile(self, threshold=30):
        if self._versatile_students_df is None:
            self.generate_versatile_students(threshold=threshold)

        df_result = []
        for comp in self.competitions_data:
            lomba = comp['Lomba']
            required_vars = [v for v in comp['Variabel yang Digunakan'] if v in self.df_main.columns]
            required = comp['Jumlah Siswa yang Dibutuhkan']
            if not required_vars:
                continue

            df_candidates = self.df_main[self.df_main['ID Siswa'].isin(self._versatile_students_df['ID Siswa'])].copy()
            df_candidates['Skor'] = df_candidates[required_vars].mean(axis=1)
            df_candidates = df_candidates[df_candidates['Skor'] > threshold].sort_values(by='Skor', ascending=False)
            df_selected = df_candidates.head(required)
            df_selected = df_selected[['ID Siswa', 'Nama Siswa', 'Skor']].copy()
            df_selected['Lomba Rekomendasi'] = lomba
            df_selected['Fase Rekomendasi'] = 'Fase 3 (Versatile)'
            df_result.append(df_selected)

        self._final_versatile_assignments_df = pd.concat(df_result, ignore_index=True)
        return self._final_versatile_assignments_df.copy()
    
    def get_required_student_count(self, lomba_name):
        for comp in self.competitions_data:
            if comp['Lomba'].strip().lower() == lomba_name.strip().lower():
                return comp['Jumlah Siswa yang Dibutuhkan']
        return 0
