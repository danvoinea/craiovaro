#!/bin/sh
php artisan test
./vendor/bin/pint
./vendor/bin/phpstan analyse --memory-limit=512M
