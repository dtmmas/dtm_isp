set -e

ts=$(date +%Y%m%d_%H%M%S)
base="/home/u497317539/domains/sistemasdtm.com/public_html/DTM_sistema"
laravel="$base/laravel"
backup_dir="$HOME/deploy_backups"

mkdir -p "$backup_dir"

tar -czf "$backup_dir/dtm_files_$ts.tar.gz" -C "/home/u497317539/domains/sistemasdtm.com/public_html" "DTM_sistema"

db=$(grep '^DB_DATABASE=' "$laravel/.env" | cut -d= -f2-)
user=$(grep '^DB_USERNAME=' "$laravel/.env" | cut -d= -f2-)
pass=$(grep '^DB_PASSWORD=' "$laravel/.env" | cut -d= -f2-)
host=$(grep '^DB_HOST=' "$laravel/.env" | cut -d= -f2-)
port=$(grep '^DB_PORT=' "$laravel/.env" | cut -d= -f2-)

if [ -z "$port" ]; then
    port=3306
fi

if command -v mysqldump >/dev/null 2>&1; then
    mysqldump -h "$host" -P "$port" -u "$user" -p"$pass" "$db" > "$backup_dir/dtm_db_$ts.sql"
else
    echo "mysqldump not found; skipped database backup"
fi

ls -lh "$backup_dir" | tail -n 4
