FROM python:3.12-slim

WORKDIR /app

# Install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy project files
COPY . .

# Set environment variables Flask
ENV FLASK_APP=app.py
ENV FLASK_RUN_HOST=0.0.0.0
ENV FLASK_RUN_PORT=5000

# Jalankan Flask (host 0.0.0.0 supaya bisa diakses dari luar container)
CMD ["flask", "run", "--host=0.0.0.0", "--port=5000"]

##
