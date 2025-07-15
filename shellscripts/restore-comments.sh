#!/bin/bash

# Set path to WordPress root
WP_PATH="/var/www/html"

# Move to WordPress directory
cd "$WP_PATH" || { echo "❌ Failed to change directory to $WP_PATH"; exit 1; }

# Extract DB credentials and table prefix
DB_NAME=$(grep DB_NAME wp-config.php | cut -d \' -f 4)
DB_USER=$(grep DB_USER wp-config.php | cut -d \' -f 4)
DB_PASSWORD=$(grep DB_PASSWORD wp-config.php | cut -d \' -f 4)
TABLE_PREFIX=$(grep "^\$table_prefix" wp-config.php | cut -d \' -f 2)

# Find latest SQL file
LATEST_SQL=$(ls -t wp-content/uploads/appcli_comments_backup_*.sql 2>/dev/null | head -n 1)

if [ -z "$LATEST_SQL" ]; then
  echo "❌ No comment backup file found in wp-content/uploads"
  exit 1
fi

echo "♻️  Restoring from $LATEST_SQL ..."

# Drop the existing tables before restore (optional but recommended)
mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
  DROP TABLE IF EXISTS \`${TABLE_PREFIX}comments\`, \`${TABLE_PREFIX}commentmeta\`;
"

# Restore the dump
mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$LATEST_SQL"

# Confirm
if [ $? -eq 0 ]; then
  echo "✅ Restore completed successfully from $LATEST_SQL"
else
  echo "❌ Restore failed"
  exit 1
fi