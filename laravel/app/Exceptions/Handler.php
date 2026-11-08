<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // #region debug-point A:global-exception-report
            try {
                $request = request();
                $debugServerUrl = 'http://127.0.0.1:7777/event';
                $debugSessionId = 'system-wide-500';
                $debugRunId = 'pre-fix';
                $debugEnvPath = base_path('.dbg/system-wide-500.env');

                if (is_file($debugEnvPath)) {
                    $debugEnvContent = @file_get_contents($debugEnvPath) ?: '';
                    foreach (preg_split("/\r\n|\n|\r/", $debugEnvContent) as $debugEnvLine) {
                        if (strpos($debugEnvLine, 'DEBUG_SERVER_URL=') === 0) {
                            $debugServerUrl = trim(substr($debugEnvLine, strlen('DEBUG_SERVER_URL=')));
                        }

                        if (strpos($debugEnvLine, 'DEBUG_SESSION_ID=') === 0) {
                            $debugSessionId = trim(substr($debugEnvLine, strlen('DEBUG_SESSION_ID=')));
                        }

                        if (strpos($debugEnvLine, 'DEBUG_RUN_ID=') === 0) {
                            $debugRunId = trim(substr($debugEnvLine, strlen('DEBUG_RUN_ID=')));
                        }
                    }
                }

                $debugPayload = [
                    'sessionId' => $debugSessionId,
                    'runId' => $debugRunId ?: 'pre-fix',
                    'hypothesisId' => $e instanceof \Illuminate\Database\QueryException ? 'C' : ($request->input('fingerprint.name') ? 'A' : 'D'),
                    'location' => get_class($e),
                    'msg' => '[DEBUG] Unhandled exception captured',
                    'ts' => round(microtime(true) * 1000),
                    'data' => [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'path' => optional($request)->path(),
                        'method' => optional($request)->method(),
                        'route' => optional(optional($request)->route())->getName(),
                        'is_livewire' => optional($request)->has('fingerprint'),
                        'livewire_component' => optional($request)->input('fingerprint.name'),
                        'livewire_method' => optional($request)->input('updates.0.payload.method'),
                        'user_id' => optional(optional($request)->user())->id,
                    ],
                ];

                @file_get_contents($debugServerUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => json_encode($debugPayload),
                        'timeout' => 1,
                    ],
                ]));
            } catch (Throwable $debugInstrumentationException) {
                // Intentionally swallow instrumentation failures.
            }
            // #endregion
        });
    }
}
