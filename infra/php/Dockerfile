FROM centos:7

# Install dependencies
RUN yum -y update
RUN yum -y install epel-release
RUN yum -y install git make automake
RUN yum -y install http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
RUN yum -y install --enablerepo=remi-php73 \
    php \
    php-devel \
    php-gd \
    php-mbstring \
    php-intl \
    php-pecl-zip \
    php-gmp \
    php-openssl \
    php-opcache \
    php-pecl-runkit7 \
    php-pecl-redis \
    php-pecl-igbinary \
    php-pecl-imagick \
    php-xml

## Install swoole from source
WORKDIR /tmp
RUN git clone https://github.com/swoole/swoole-src.git
RUN cd swoole-src && \
    git checkout master && \
    phpize && \
    ./configure --enable-openssl && \
    make && \
    make install

# Add Swoole extensions into php.ini
RUN echo extension=swoole.so >> /etc/php.ini

COPY composer.phar /usr/bin/composer

# Run PHP
WORKDIR /var/src
CMD composer -n install &>/dev/null && php /var/src/bootstrap.php
