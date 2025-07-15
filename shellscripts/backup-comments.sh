#!/bin/bash

# Set path to WordPress root (edit this if needed)
WP_PATH="/var/www/html"

# Move to WP root directory
cd "$WP_PATH" || { echo "❌ Failed to change directory to $WP_PATH"; exit 1; }

# Extract DB credentials from wp-config.php
DB_NAME=$(grep DB_NAME wp-config.php | cut -d \' -f 4)
DB_USER=$(grep DB_USER wp-config.php | cut -d \' -f 4)
DB_PASSWORD=$(grep DB_PASSWORD wp-config.php | cut -d \' -f 4)

# Extract table prefix
TABLE_PREFIX=$(grep "^\$table_prefix" wp-config.php | cut -d \' -f 2)

# Set output file
DATE_STR=$(date +%m%d%Y)
BACKUP_FILE="wp-content/uploads/appcli_comments_backup_${DATE_STR}.sql"

# Run mysqldump for the comments and commentmeta tables
echo "📦 Backing up ${TABLE_PREFIX}comments and ${TABLE_PREFIX}commentmeta to $BACKUP_FILE ..."
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" \
  "${TABLE_PREFIX}comments" "${TABLE_PREFIX}commentmeta" > "$BACKUP_FILE"

# Check if successful
if [ $? -eq 0 ]; then
  echo "✅ Backup complete: $BACKUP_FILE"
else
  echo "❌ Backup failed"
  exit 1
fi