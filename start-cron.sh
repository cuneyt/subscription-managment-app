#!/bin/bash

# Cron servisini başlat
service cron start

# Laravel migrasyonları çalıştır ve sunucuyu başlat
php artisan serve --host=0.0.0.0 --port=8181

# Logları sürekli gözlemle
tail -f /var/log/cron.log