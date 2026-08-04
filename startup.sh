#!/bin/bash
sed -i 's|try_files \$uri/ /index.php?\$query_string;|try_files \$uri \$uri/ /index.php?\$query_string;|g' /etc/nginx/sites-available/default
nginx -s reload
