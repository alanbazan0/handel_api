#!/bin/bash
set -e
# Cargar variables desde .env.deploy (nunca hardcodear credenciales)
source .env.deploy

composer install --no-dev --optimize-autoloader --no-security-blocking

zip -r vendor.zip vendor/

lftp -c "set ftp:ssl-allow no; \
  open -u \$FTP_USER,\$FTP_PASS ftp://\$FTP_SERVER; \
  mirror -R --exclude vendor/ --exclude .git/ --exclude node_modules/ --exclude tests/ --exclude storage/logs/ . ."

curl -f -X POST "$APP_URL/post_deploy.php" -d "token=$MIGRATE_TOKEN"

rm -f vendor.zip
echo "Deploy de handel_api completado."
