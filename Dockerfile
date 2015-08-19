FROM maxexcloo/nginx-php:latest
MAINTAINER Patrick Poulain <petitchevalroux@free.fr>
RUN mkdir -p /data/http/public && \
perl -pi -e 's~root /data/http~root /data/http/public~g' /etc/nginx/host.d/default.conf
ADD . /data/http
ADD ./docker/nginx/default-host.conf /etc/nginx/host.d/default.conf
RUN mkdir -p /data/config
ADD ./docker/php-fpm/php-env.conf /data/config/php-env.conf
