### 🐚 Shell Script Usage for Comment Management

For faster operations on large comment sets, the following shell scripts are used instead of the PHP-based AJAX buttons:

📁 **Location**:  
`wp-content/plugins/appetiser-common-assets/shellscripts/`

#### Scripts:
- `backup-comments.sh` – Exports `wp_comments` and `wp_commentmeta` to a `.sql` file using `mysqldump`
sudo -u www-data bash backup-comments.sh
- `delete-comments.sh` – Deletes all comments and related metadata directly via `wp db query`
sudo -u www-data bash delete-comments.sh
- `restore-comments.sh` – Restores comments from the latest `.sql` backup file
sudo -u www-data bash restore-comments.sh

📝 These scripts are preferred when dealing with a large number of comments due to significantly faster performance compared to PHP/AJAX operations.

> ⚠️ Note: This section is a working draft and not the final documentation.
