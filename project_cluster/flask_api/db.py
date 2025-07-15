from sqlalchemy import create_engine, MetaData
from sqlalchemy.orm import sessionmaker
import os

# Konfigurasi dari .env Laravel
DB_USER = 'root'
DB_PASSWORD = ''  # Kosong sesuai dengan .env Anda
DB_HOST = '127.0.0.1'
DB_PORT = '3306'
DB_NAME = 'arvegatuPramuka'

# Gunakan koneksi MySQL dengan pymysql
DATABASE_URL = f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"

# Buat engine dan session
engine = create_engine(DATABASE_URL, echo=False)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
metadata = MetaData()
