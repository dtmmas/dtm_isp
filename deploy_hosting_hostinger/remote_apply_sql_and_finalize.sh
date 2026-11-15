set -e

base="/home/u497317539/domains/sistemasdtm.com/public_html/DTM_sistema"
laravel="$base/laravel"
sql_file="$HOME/deploy_hosting_hostinger/sql/2026_08_24_hosting_updates.sql"

db=$(grep '^DB_DATABASE=' "$laravel/.env" | cut -d= -f2-)
user=$(grep '^DB_USERNAME=' "$laravel/.env" | cut -d= -f2-)
pass=$(grep '^DB_PASSWORD=' "$laravel/.env" | cut -d= -f2-)
host=$(grep '^DB_HOST=' "$laravel/.env" | cut -d= -f2-)
port=$(grep '^DB_PORT=' "$laravel/.env" | cut -d= -f2-)

if [ -z "$port" ]; then
    port=3306
fi

mysql -h "$host" -P "$port" -u "$user" -p"$pass" "$db" < "$sql_file"

cd "$laravel"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan route:list --name=clients.map | sed -n '1,40p'
echo "SQL applied and cache cleared"
