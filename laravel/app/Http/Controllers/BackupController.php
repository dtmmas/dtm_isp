<?php

namespace App\Http\Controllers;

use App\Services\BackupArchiveService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BackupController extends Controller
{
    public function download(BackupArchiveService $backupArchiveService)
    {
        abort_unless(Auth::check() && Auth::user()->can('setting.index') && Auth::user()->hasRole('Admin'), 403);

        try {
            $backup = $backupArchiveService->create();
        } catch (Throwable $throwable) {
            abort(500, 'No se pudo generar el respaldo: '.$throwable->getMessage());
        }

        return response()->download($backup['path'], $backup['filename'])->deleteFileAfterSend(true);
    }
}
