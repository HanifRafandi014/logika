from sqlalchemy import create_engine, MetaData
from sqlalchemy.orm import sessionmaker
import os

# Ambil variabel environment (sesuai dengan .env Laravel)
DB_USER = os.getenv("DB_USERNAME", "root")
DB_PASSWORD = os.getenv("DB_PASSWORD", "root")
DB_HOST = os.getenv("DB_HOST", "db")  # gunakan service name dari docker-compose
DB_PORT = os.getenv("DB_PORT", "3306")
DB_NAME = os.getenv("DB_DATABASE", "app_db")

# Format koneksi SQLAlchemy
DATABASE_URL = f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"

# Buat engine & session
engine = create_engine(DATABASE_URL, echo=False, pool_pre_ping=True)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
metadata = MetaData()
