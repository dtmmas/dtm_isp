<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class RestoreArchiveService
{
    public function restore(UploadedFile $archive): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extension ZIP no esta disponible en este servidor.');
        }

        $timestamp = now()->format('Ymd_His');
        $workspaceRoot = dirname(base_path());
        $restoreRoot = storage_path('app/restore-workspaces'.DIRECTORY_SEPARATOR.$timestamp);
        $archivePath = $restoreRoot.DIRECTORY_SEPARATOR.'archive.zip';
        $extractPath = $restoreRoot.DIRECTORY_SEPARATOR.'extracted';

        File::ensureDirectoryExists($restoreRoot);
        $archive->move($restoreRoot, 'archive.zip');

        try {
            File::ensureDirectoryExists($extractPath);

            $zip = new ZipArchive();
            if ($zip->open($archivePath) !== true) {
                throw new RuntimeException('No se pudo abrir el ZIP del respaldo.');
            }

            if (! $zip->extractTo($extractPath)) {
                $zip->close();
                throw new RuntimeException('No se pudo extraer el ZIP del respaldo.');
            }

            $zip->close();

            $this->validateStructure($extractPath);
            $this->restoreDatabase($extractPath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'dtm_isp.sql');
            $this->restoreLaravelFiles($extractPath, base_path());
            $this->restoreWorkspaceFiles($extractPath, $workspaceRoot);

            Artisan::call('optimize:clear');
        } finally {
            File::deleteDirectory($restoreRoot);
        }

        return [
            'filename' => $archive->getClientOriginalName(),
        ];
    }

    protected function validateStructure(string $extractPath): void
    {
        $requiredPaths = [
            $extractPath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'dtm_isp.sql',
            $extractPath.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'app',
            $extractPath.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'config',
            $extractPath.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'routes',
            $extractPath.DIRECTORY_SEPARATOR.'public-root',
        ];

        foreach ($requiredPaths as $requiredPath) {
            if (! File::exists($requiredPath)) {
                throw new RuntimeException('El respaldo no tiene la estructura esperada para restaurarse.');
            }
        }
    }

    protected function restoreDatabase(string $sqlPath): void
    {
        $sql = File::get($sqlPath);
        $statements = $this->splitSqlStatements($sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            DB::unprepared($statement);
        }
    }

    protected function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($index = 0; $index < $length; $index++) {
            $character = $sql[$index];
            $nextCharacter = $index + 1 < $length ? $sql[$index + 1] : '';
            $previousCharacter = $index > 0 ? $sql[$index - 1] : '';

            if ($inLineComment) {
                if ($character === "\n") {
                    $inLineComment = false;
                }

                continue;
            }

            if ($inBlockComment) {
                if ($character === '*' && $nextCharacter === '/') {
                    $inBlockComment = false;
                    $index++;
                }

                continue;
            }

            if (! $inSingleQuote && ! $inDoubleQuote) {
                if ($character === '-' && $nextCharacter === '-') {
                    $inLineComment = true;
                    $index++;
                    continue;
                }

                if ($character === '#') {
                    $inLineComment = true;
                    continue;
                }

                if ($character === '/' && $nextCharacter === '*') {
                    $inBlockComment = true;
                    $index++;
                    continue;
                }
            }

            if ($character === "'" && ! $inDoubleQuote && $previousCharacter !== '\\') {
                $inSingleQuote = ! $inSingleQuote;
            } elseif ($character === '"' && ! $inSingleQuote && $previousCharacter !== '\\') {
                $inDoubleQuote = ! $inDoubleQuote;
            }

            if ($character === ';' && ! $inSingleQuote && ! $inDoubleQuote) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $character;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    protected function restoreLaravelFiles(string $extractPath, string $laravelRoot): void
    {
        foreach ($this->laravelMappings() as $mapping) {
            $source = $extractPath.DIRECTORY_SEPARATOR.$mapping['source'];
            $target = $laravelRoot.DIRECTORY_SEPARATOR.$mapping['target'];
            $this->replacePath($source, $target);
        }
    }

    protected function restoreWorkspaceFiles(string $extractPath, string $workspaceRoot): void
    {
        foreach ($this->workspaceMappings() as $mapping) {
            $source = $extractPath.DIRECTORY_SEPARATOR.$mapping['source'];
            $target = $workspaceRoot.DIRECTORY_SEPARATOR.$mapping['target'];
            $this->replacePath($source, $target);
        }
    }

    protected function replacePath(string $source, string $target): void
    {
        if (! File::exists($source)) {
            return;
        }

        if (File::isFile($source)) {
            File::ensureDirectoryExists(dirname($target));
            File::copy($source, $target);
            return;
        }

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        } elseif (File::exists($target)) {
            File::delete($target);
        }

        File::ensureDirectoryExists(dirname($target));
        File::copyDirectory($source, $target);
    }

    protected function laravelMappings(): array
    {
        return [
            ['source' => 'laravel/.env', 'target' => '.env'],
            ['source' => 'laravel/app', 'target' => 'app'],
            ['source' => 'laravel/bootstrap/app.php', 'target' => 'bootstrap/app.php'],
            ['source' => 'laravel/config', 'target' => 'config'],
            ['source' => 'laravel/database', 'target' => 'database'],
            ['source' => 'laravel/resources', 'target' => 'resources'],
            ['source' => 'laravel/routes', 'target' => 'routes'],
            ['source' => 'laravel/artisan', 'target' => 'artisan'],
            ['source' => 'laravel/composer.json', 'target' => 'composer.json'],
            ['source' => 'laravel/composer.lock', 'target' => 'composer.lock'],
            ['source' => 'laravel/package.json', 'target' => 'package.json'],
            ['source' => 'laravel/phpunit.xml', 'target' => 'phpunit.xml'],
            ['source' => 'laravel/server.php', 'target' => 'server.php'],
            ['source' => 'laravel/storage/app/public/logo', 'target' => 'storage/app/public/logo'],
            ['source' => 'laravel/storage/app/public/payment', 'target' => 'storage/app/public/payment'],
            ['source' => 'laravel/tailwind.config.js', 'target' => 'tailwind.config.js'],
            ['source' => 'laravel/webpack.mix.js', 'target' => 'webpack.mix.js'],
        ];
    }

    protected function workspaceMappings(): array
    {
        return [
            ['source' => 'public-root/index.php', 'target' => 'index.php'],
            ['source' => 'public-root/.htaccess', 'target' => '.htaccess'],
            ['source' => 'public-root/bootstrap', 'target' => 'bootstrap'],
            ['source' => 'public-root/css', 'target' => 'css'],
            ['source' => 'public-root/js', 'target' => 'js'],
            ['source' => 'public-root/plugins', 'target' => 'plugins'],
            ['source' => 'public-root/resources', 'target' => 'resources'],
            ['source' => 'public-root/scss', 'target' => 'scss'],
            ['source' => 'public-root/mix-manifest.json', 'target' => 'mix-manifest.json'],
            ['source' => 'public-root/router.php', 'target' => 'router.php'],
            ['source' => 'public-root/robots.txt', 'target' => 'robots.txt'],
            ['source' => 'public-root/favicon.ico', 'target' => 'favicon.ico'],
            ['source' => 'public-root/web.config', 'target' => 'web.config'],
        ];
    }
}
