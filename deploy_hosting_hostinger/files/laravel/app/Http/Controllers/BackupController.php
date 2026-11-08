<?php

namespace App\Http\Controllers;

use App\Services\BackupArchiveService;
use App\Services\RestoreArchiveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Throwable;

class BackupController extends Controller
{
    public function download(Request $request, BackupArchiveService $backupArchiveService)
    {
        abort_unless(Auth::check() && Auth::user()->can('setting.index') && Auth::user()->hasRole('Admin'), 403);

        try {
            $backup = $backupArchiveService->create();
        } catch (Throwable $throwable) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'No se pudo generar el respaldo: '.$throwable->getMessage(),
                ], 500);
            }

            abort(500, 'No se pudo generar el respaldo: '.$throwable->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Respaldo listo para descargar.',
                'filename' => $backup['filename'],
                'download_url' => route('settings.backup.download'),
            ]);
        }

        return response()->download($backup['path'], $backup['filename']);
    }

    public function restore(Request $request, RestoreArchiveService $restoreArchiveService)
    {
        abort_unless(Auth::check() && Auth::user()->can('setting.index') && Auth::user()->hasRole('Admin'), 403);

        $confirmRestore = $request->input('confirm_restore');

        if ($confirmRestore !== 'RESTAURAR') {
            $message = 'Debes escribir RESTAURAR para confirmar el proceso.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors([
                'confirm_restore' => $message,
            ])->withInput();
        }

        try {
            $restoreArchive = $this->uploadedRestoreArchive();
        } catch (Throwable $throwable) {
            $message = $throwable->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors([
                'restore_archive' => $message,
            ])->withInput();
        }

        if (! $restoreArchive) {
            $message = 'Debes seleccionar un archivo ZIP de respaldo.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors([
                'restore_archive' => $message,
            ])->withInput();
        }

        if (strtolower($restoreArchive->getClientOriginalExtension()) !== 'zip') {
            $message = 'El archivo de restauracion debe ser un ZIP valido.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors([
                'restore_archive' => $message,
            ])->withInput();
        }

        try {
            $result = $restoreArchiveService->restore($restoreArchive);
        } catch (Throwable $throwable) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'No se pudo restaurar el respaldo: '.$throwable->getMessage(),
                ], 500);
            }

            return back()->withErrors([
                'restore_archive' => 'No se pudo restaurar el respaldo: '.$throwable->getMessage(),
            ])->withInput();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Respaldo restaurado correctamente desde '.$result['filename'].'.',
            ]);
        }

        return redirect()
            ->route('setting.index')
            ->with('success_restore', 'Respaldo restaurado correctamente desde '.$result['filename'].'.');
    }

    protected function uploadedRestoreArchive(): ?UploadedFile
    {
        $uploadedFile = $_FILES['restore_archive'] ?? null;

        if (! is_array($uploadedFile) || empty($uploadedFile['tmp_name']) || empty($uploadedFile['name'])) {
            return null;
        }

        $uploadError = (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->uploadErrorMessage($uploadError));
        }

        $symfonyFile = new SymfonyUploadedFile(
            $uploadedFile['tmp_name'],
            $uploadedFile['name'],
            $uploadedFile['type'] ?? 'application/zip',
            $uploadError,
            true
        );

        return UploadedFile::createFromBase($symfonyFile, true);
    }

    protected function uploadErrorMessage(int $uploadError): string
    {
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            return 'El archivo supera el tamano maximo permitido por el servidor.';
        }

        if ($uploadError === UPLOAD_ERR_PARTIAL) {
            return 'La carga del archivo quedo incompleta. Intenta nuevamente.';
        }

        if ($uploadError === UPLOAD_ERR_NO_TMP_DIR) {
            return 'El servidor no tiene carpeta temporal disponible para subir el archivo.';
        }

        if ($uploadError === UPLOAD_ERR_CANT_WRITE) {
            return 'El servidor no pudo escribir el archivo temporal de restauracion.';
        }

        if ($uploadError === UPLOAD_ERR_EXTENSION) {
            return 'Una extension del servidor detuvo la carga del archivo.';
        }

        return 'La carga del archivo fallo. Intenta nuevamente con el respaldo ZIP.';
    }
}
