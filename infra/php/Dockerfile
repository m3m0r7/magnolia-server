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
RUN echo hogehoge
RUN git clone https://github.com/swoole/swoole-src.git
RUN cd swoole-src && \
    git checkout refs/tags/v4.4.2 && \
    phpize && \
    ./configure --enable-openssl && \
    make && \
    make install

# Add Swoole extensions into php.ini
RUN echo extension=swoole.so >> /etc/php.ini

# Add composer
WORKDIR /tmp
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/bin/composer

# Run PHP
WORKDIR /var/src
CMD composer -n install &>/dev/null && php /var/src/bootstrap.php
