FROM php:8.2-cli

WORKDIR /app

# Salin seluruh file proyek ke dalam container
COPY . /app

# Buat folder uploads_produk jika belum ada dan berikan izin akses penuh
RUN mkdir -p /app/uploads_produk && chmod -R 777 /app/uploads_produk

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080"]
