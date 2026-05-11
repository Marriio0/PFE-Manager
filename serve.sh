#!/bin/bash
cd "$(dirname "$0")"
php -d upload_max_filesize=25M \
    -d post_max_size=30M \
    -d memory_limit=256M \
    -d max_execution_time=120 \
    artisan serve --host=0.0.0.0 --port=8000
