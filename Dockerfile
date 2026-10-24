FROM php:8.2-apache

# Cài đặt thư viện GMP và cURL cần thiết cho xác thực Eufy & Tuya IoT
RUN apt-get update && apt-get install -y \
    libgmp-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install gmp curl \
    && rm -rf /var/lib/apt/lists/*

# Bật mod_rewrite
RUN a2enmod rewrite

# Sao chép mã nguồn vào Apache web root
COPY . /var/www/html/

# Phân quyền thư mục lưu trữ data & log
RUN mkdir -p /var/www/html/data && chmod -R 777 /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
