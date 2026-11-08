<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use ZipArchive;

class BackupArchiveService
{
    public function create(): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extension ZIP no esta disponible en este servidor.');
        }

        $timestamp = now()->format('Ymd_His');
        $backupDirectory = storage_path('app/backups');
        $workspaceRoot = dirname(base_path());
        $tempDirectory = $backupDirectory.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$timestamp;
        $zipPath = $backupDirectory.DIRECTORY_SEPARATOR.'dtm_isp_backup_'.$timestamp.'.zip';
        $sqlPath = $tempDirectory.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'dtm_isp.sql';
        $manifestPath = $tempDirectory.DIRECTORY_SEPARATOR.'README_BACKUP.txt';

        File::ensureDirectoryExists(dirname($sqlPath));
        File::ensureDirectoryExists($backupDirectory);

        $this->writeDatabaseDump($sqlPath);
        File::put($manifestPath, $this->buildRestoreGuide($timestamp));

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo ZIP del respaldo.');
        }

        $zip->addFile($sqlPath, 'database/dtm_isp.sql');
        $zip->addFile($manifestPath, 'README_BACKUP.txt');

        foreach ($this->laravelFiles(base_path()) as $item) {
            $this->addPathToZip($zip, $item['source'], $item['target']);
        }

        foreach ($this->workspaceFiles($workspaceRoot) as $item) {
            $this->addPathToZip($zip, $item['source'], $item['target']);
        }

        $zip->close();
        File::deleteDirectory($tempDirectory);

        return [
            'path' => $zipPath,
            'filename' => basename($zipPath),
        ];
    }

    protected function writeDatabaseDump(string $sqlPath): void
    {
        $pdo = DB::connection()->getPdo();
        $databaseName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $columnKey = 'Tables_in_'.$databaseName;

        $header = [
            '-- DTM ISP database backup',
            '-- Generated at: '.now()->format('Y-m-d H:i:s'),
            '-- Database: '.$databaseName,
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        File::put($sqlPath, implode(PHP_EOL, $header));

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->$columnKey ?? array_values((array) $tableRow)[0] ?? null;
            if (! $tableName) {
                continue;
            }

            $createTableRow = DB::selectOne('SHOW CREATE TABLE `'.$tableName.'`');
            $createTableSql = $createTableRow->{'Create Table'} ?? array_values((array) $createTableRow)[1] ?? null;
            $columns = Schema::getColumnListing($tableName);

            File::append($sqlPath, PHP_EOL.'-- Table: '.$tableName.PHP_EOL);
            File::append($sqlPath, 'DROP TABLE IF EXISTS `'.$tableName.'`;'.PHP_EOL);
            File::append($sqlPath, $createTableSql.';'.PHP_EOL.PHP_EOL);

            if (empty($columns)) {
                continue;
            }

            $columnList = '`'.implode('`, `', $columns).'`';
            $batch = [];

            foreach (DB::table($tableName)->cursor() as $row) {
                $values = [];

                foreach ($columns as $column) {
                    $value = $row->$column ?? null;
                    $values[] = $this->sqlValue($pdo, $value);
                }

                $batch[] = '('.implode(', ', $values).')';

                if (count($batch) >= 100) {
                    File::append(
                        $sqlPath,
                        'INSERT INTO `'.$tableName.'` ('.$columnList.') VALUES'.PHP_EOL
                        .implode(','.PHP_EOL, $batch).';'.PHP_EOL.PHP_EOL
                    );
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                File::append(
                    $sqlPath,
                    'INSERT INTO `'.$tableName.'` ('.$columnList.') VALUES'.PHP_EOL
                    .implode(','.PHP_EOL, $batch).';'.PHP_EOL.PHP_EOL
                );
            }
        }

        File::append($sqlPath, 'SET FOREIGN_KEY_CHECKS=1;'.PHP_EOL);
    }

    protected function sqlValue($pdo, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    protected function addPathToZip(ZipArchive $zip, string $source, string $target): void
    {
        if (! File::exists($source)) {
            return;
        }

        if (File::isFile($source)) {
            $zip->addFile($source, str_replace('\\', '/', $target));
            return;
        }

        $zip->addEmptyDir(str_replace('\\', '/', $target));

        foreach (File::allFiles($source) as $file) {
            $relativePath = ltrim(str_replace($source, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile(
                $file->getPathname(),
                str_replace('\\', '/', $target.DIRECTORY_SEPARATOR.$relativePath)
            );
        }
    }

    protected function laravelFiles(string $laravelRoot): array
    {
        return [
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'.env', 'target' => 'laravel/.env'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'app', 'target' => 'laravel/app'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'app.php', 'target' => 'laravel/bootstrap/app.php'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'config', 'target' => 'laravel/config'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'database', 'target' => 'laravel/database'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'resources', 'target' => 'laravel/resources'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'routes', 'target' => 'laravel/routes'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app', 'target' => 'laravel/storage/app'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'artisan', 'target' => 'laravel/artisan'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'composer.json', 'target' => 'laravel/composer.json'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'composer.lock', 'target' => 'laravel/composer.lock'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'package.json', 'target' => 'laravel/package.json'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'phpunit.xml', 'target' => 'laravel/phpunit.xml'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'server.php', 'target' => 'laravel/server.php'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'tailwind.config.js', 'target' => 'laravel/tailwind.config.js'],
            ['source' => $laravelRoot.DIRECTORY_SEPARATOR.'webpack.mix.js', 'target' => 'laravel/webpack.mix.js'],
        ];
    }

    protected function workspaceFiles(string $workspaceRoot): array
    {
        return [
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'index.php', 'target' => 'public-root/index.php'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'.htaccess', 'target' => 'public-root/.htaccess'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'bootstrap', 'target' => 'public-root/bootstrap'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'css', 'target' => 'public-root/css'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'js', 'target' => 'public-root/js'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'plugins', 'target' => 'public-root/plugins'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'resources', 'target' => 'public-root/resources'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'scss', 'target' => 'public-root/scss'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'mix-manifest.json', 'target' => 'public-root/mix-manifest.json'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'router.php', 'target' => 'public-root/router.php'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'robots.txt', 'target' => 'public-root/robots.txt'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'favicon.ico', 'target' => 'public-root/favicon.ico'],
            ['source' => $workspaceRoot.DIRECTORY_SEPARATOR.'web.config', 'target' => 'public-root/web.config'],
        ];
    }

    protected function buildRestoreGuide(string $timestamp): string
    {
        return implode(PHP_EOL, [
            'DTM ISP - Respaldo completo',
            'Generado: '.$timestamp,
            '',
            'Contenido:',
            '- database/dtm_isp.sql: respaldo SQL completo de la base de datos',
            '- laravel/: codigo fuente, configuracion, migraciones y almacenamiento util',
            '- public-root/: archivos publicos y assets necesarios del proyecto',
            '',
            'Restauracion sugerida:',
            '1. Extraer el ZIP en una carpeta nueva.',
            '2. Restaurar database/dtm_isp.sql en MySQL o MariaDB.',
            '3. Ajustar laravel/.env con las credenciales del entorno destino.',
            '4. Publicar la carpeta public-root junto con la carpeta laravel segun la estructura actual del sistema.',
            '5. Ejecutar php artisan optimize:clear y validar acceso al sistema.',
            '',
        ]);
    }
}
