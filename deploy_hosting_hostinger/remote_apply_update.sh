set -e

base="/home/u497317539/domains/sistemasdtm.com/public_html/DTM_sistema"
laravel="$base/laravel"
incoming="$HOME/deploy_hosting_hostinger"
files="$incoming/files"

if [ ! -d "$files" ]; then
    echo "Upload folder not found: $files"
    exit 1
fi

cp -rf "$files"/. "$base"/

mkdir -p "$laravel/storage/app/public/client-products"
mkdir -p "$laravel/storage/app/backups/tmp"
mkdir -p "$laravel/storage/app/restore-workspaces"
mkdir -p "$laravel/storage/app/exchange-rates"

if [ ! -e "$base/storage" ]; then
    ln -s "$laravel/storage/app/public" "$base/storage"
fi

cd "$laravel"
if php artisan migrate --force; then
    echo "Migrations applied"
else
    echo "Warning: migrate --force failed; continuing with cache clear because this hosting has legacy migration issues"
fi
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan route:list --name=clients.map | sed -n '1,40p'
echo "Deployment finished"
