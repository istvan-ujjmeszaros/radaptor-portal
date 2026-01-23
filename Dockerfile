FROM php:8.4-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install dependencies (SQLite, curl)
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set document root to public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Install Dart Sass (standalone binary - fast, no Node.js needed)
ARG DART_SASS_VERSION=1.83.4
RUN curl -fsSL https://github.com/sass/dart-sass/releases/download/${DART_SASS_VERSION}/dart-sass-${DART_SASS_VERSION}-linux-x64.tar.gz \
    | tar -xz -C /opt \
    && ln -s /opt/dart-sass/sass /usr/local/bin/sass

# Set working directory
WORKDIR /var/www/html

# Download Bootstrap SCSS source (no npm needed)
ARG BOOTSTRAP_VERSION=5.3.3
RUN mkdir -p /var/www/html/vendor/bootstrap \
    && curl -fsSL https://github.com/twbs/bootstrap/archive/v${BOOTSTRAP_VERSION}.tar.gz \
    | tar -xz -C /var/www/html/vendor/bootstrap --strip-components=1

# Copy application files
COPY . .

# Build CSS from SCSS
RUN mkdir -p /var/www/html/public/assets/css \
    && sass /var/www/html/public/assets/scss/main.scss /var/www/html/public/assets/css/style.css --style=compressed --no-source-map

# Ensure data directory is writable
RUN mkdir -p /var/www/html/data && chmod 777 /var/www/html/data

EXPOSE 80
