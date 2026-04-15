FROM php:8.3-fpm

RUN apt-get update && \
    apt-get install --quiet --yes --no-install-recommends \
        git \
        sudo \
        wget \
        curl \
        libzip-dev \
        libxml2-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        unzip \
        libreoffice \
        libicu-dev \
        libpq-dev \
        nodejs \
        npm \
        vim && \
    docker-php-ext-install zip pdo pdo_pgsql pgsql soap intl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) gd && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/*


# libreoffice
RUN ln -s /usr/lib/libreoffice/program/soffice /usr/local/bin

#nvm
ENV NODE_VERSION=18.20.0
ENV NVM_DIR /tmp/nvm
WORKDIR $NVM_DIR

RUN wget -qO- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# chromedriver
#RUN cd /opt && wget https://storage.googleapis.com/chrome-for-testing-public/125.0.6422.78/linux64/chromedriver-linux64.zip
#RUN unzip /opt/chromedriver-linux64.zip
#RUN ln -s /opt/chromedriver-linux64/chromedriver /usr/local/bin/chromedriver

# composer
#COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.6

# Expose port 80 to the outside world
EXPOSE 80

# user permissions
RUN groupadd --gid 1000 laravel \
    && useradd --uid 1000 -g laravel \
        -G www-data,root --shell /bin/bash \
        --create-home laravel

USER laravel

WORKDIR /var/www/html
