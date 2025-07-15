#!/bin/bash

# Set the path to your WordPress root directory
WP_PATH="/var/www/html" 

# Change to WordPress root
cd "$WP_PATH" || { echo "❌ Failed to change directory to $WP_PATH"; exit 1; }

# Extract the table prefix from wp-config.php
PREFIX=$(php -r "include 'wp-config.php'; echo isset(\$table_prefix) ? \$table_prefix : 'wp_';")

# Confirm prefix was found
if [ -z "$PREFIX" ]; then
  echo "❌ Could not detect table prefix."
  exit 1
fi

echo "🧹 Deleting all comments from ${PREFIX}comments and ${PREFIX}commentmeta..."

# Run deletion queries
wp db query "DELETE FROM ${PREFIX}comments;"
wp db query "DELETE FROM ${PREFIX}commentmeta;"

echo "✅ All comments and related metadata deleted."