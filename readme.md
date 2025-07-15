### 🐚 Shell Script Usage for Comment Management

For faster operations on large comment sets, the following shell scripts are used instead of the PHP-based AJAX buttons:

📁 **Location**:  
`wp-content/plugins/appetiser-common-assets/shellscripts/`

#### Scripts:
- `backup-comments.sh` – Exports `wp_comments` and `wp_commentmeta` to a `.sql` file using `mysqldump`
- `delete-comments.sh` – Deletes all comments and related metadata directly via `wp db query`
- `restore-comments.sh` – Restores comments from the latest `.sql` backup file

📝 These scripts are preferred when dealing with a large number of comments due to significantly faster performance compared to PHP/AJAX operations.

> ⚠️ Note: This section is a working draft and not the final documentation.
