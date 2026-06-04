FROM php:8.5.2-apache-bookworm

COPY public/ /var/www/html/

EXPOSE 80