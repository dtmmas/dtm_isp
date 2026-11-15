<div>
    <div class="card">
        <div class="card-body wire:ignore.self">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item"><a href="#navpills-1" class="nav-link {{(($tab=='empresa')?'active show':'')}}" data-toggle="tab" aria-expanded="false">Informacion Empresa</a>
                </li>
                </li>
            </ul>
            <div class="tab-content br-n pn">
                <div id="navpills-1" class="tab-pane {{(($tab=='empresa')?'active show':'')}}">
                    @if (session('success_restore'))
                        <div class="alert alert-success">
                            {{ session('success_restore') }}
                        </div>
                    @endif

                    {!! Form::open(['id' => 'FormEmpresa', 'method' => 'POST', 'files' => true]) !!}
                        <div class="form-row">
                           
                            <div class="form-group col-md-12">
                                {!! Form::label('nombre_empresa', 'Nombre de la empresa'); !!}
                                {!! Form::text('nombre_empresa', null, ['wire:model.defer'=> 'nombre_empresa',  'class' => 'form-control']); !!}
                                @error('nombre_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('direccion_empresa', 'Direccion'); !!}
                                {!! Form::text('direccion_empresa', null, ['wire:model.defer'=> 'direccion_empresa',  'class' => 'form-control', 'placeholder'=>'']); !!}
                                @error('direccion_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('telefono_empresa', 'Telefono'); !!}
                                {!! Form::text('telefono_empresa', null, ['wire:model.defer'=> 'telefono_empresa', 'class' => 'form-control ', 'placeholder'=>'']); !!}
                                @error('telefono_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('nit_empresa', 'NIT'); !!}
                                {!! Form::text('nit_empresa', null, ['wire:model.defer'=> 'nit_empresa',  'class' => 'form-control numerico', 'placeholder'=>'']); !!}
                                @error('nit_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('slogan_empresa', 'Slogan'); !!}
                                {!! Form::text('slogan_empresa', null, ['wire:model.defer'=> 'slogan_empresa',  'class' => 'form-control', 'placeholder'=>'']); !!}
                                @error('slogan_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-md-12">
                                {!! Form::label('logo_empresa', 'Cargar Logo'); !!}
                                {!! Form::file('logo_empresa',  ['wire:model'=> 'logo_empresa', 'class' => 'form-control', 'placeholder'=>'Cargar Logo', 'accept'=>'image/*']); !!}
                                @error('logo_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                                <span wire:loading wire:target="logo_empresa" class="text-danger" > Cargando...</span>
                            </div>
                            
                            <div class="form-group col-md-12 text-center">
                                <button type="button" wire:loading.attr="disabled"  wire:target="logo_empresa,guardarEmpresa"  wire:click="guardarEmpresa()" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    {!! Form::close() !!}

                    <hr>

                    <div class="card border mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Respaldo completo del sistema</h4>
                            <p class="text-muted mb-3">
                                Genera un archivo ZIP con la base de datos en SQL, configuracion principal, codigo fuente y archivos necesarios para restaurar el sistema en otro equipo o servidor.
                            </p>
                            <ul class="mb-3">
                                <li>Incluye respaldo SQL completo.</li>
                                <li>Incluye archivo <code>.env</code> y configuraciones clave.</li>
                                <li>Incluye archivos publicos, assets y almacenamiento util.</li>
                            </ul>

                            <div id="backup-progress-wrapper" class="mb-3 d-none">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted" id="backup-progress-label">Preparando respaldo...</small>
                                    <small class="text-muted" id="backup-progress-percent">0%</small>
                                </div>
                                <div class="progress mt-2" style="height: 18px;">
                                    <div id="backup-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="backup-feedback" class="alert d-none" role="alert"></div>

                            <button type="button" id="backup-download-button" class="btn btn-success" data-download-url="{{ route('settings.backup.download') }}">
                                Descargar respaldo completo
                            </button>
                        </div>
                    </div>

                    <div class="card border border-warning mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Restaurar respaldo del sistema</h4>
                            <p class="text-muted mb-3">
                                Sube un ZIP generado por este sistema para restaurar la base de datos y los archivos incluidos en el respaldo. Esta accion reemplaza la informacion actual.
                            </p>

                            @if ($errors->has('restore_archive') || $errors->has('confirm_restore'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('restore_archive') ?: $errors->first('confirm_restore') }}
                                </div>
                            @endif

                            <div id="restore-progress-wrapper" class="mb-3 d-none">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted" id="restore-progress-label">Esperando archivo...</small>
                                    <small class="text-muted" id="restore-progress-percent">0%</small>
                                </div>
                                <div class="progress mt-2" style="height: 18px;">
                                    <div id="restore-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="restore-feedback" class="alert d-none" role="alert"></div>

                            <form id="restore-backup-form" action="{{ route('settings.backup.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="restore_archive">Archivo ZIP del respaldo</label>
                                    <input type="file" name="restore_archive" id="restore_archive" class="form-control" accept=".zip" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_restore">Escribe <code>RESTAURAR</code> para confirmar</label>
                                    <input type="text" name="confirm_restore" id="confirm_restore" class="form-control" placeholder="RESTAURAR" required>
                                </div>
                                <button type="submit" id="restore-backup-button" class="btn btn-warning">
                                    Restaurar respaldo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            function setProgressState(config) {
                config.wrapper.classList.remove('d-none');
                config.bar.style.width = config.percent + '%';
                config.bar.setAttribute('aria-valuenow', config.percent);
                config.label.textContent = config.labelText;
                config.percentNode.textContent = config.percent + '%';
            }

            function setFeedback(node, type, message) {
                node.className = 'alert alert-' + type;
                node.textContent = message;
                node.classList.remove('d-none');
            }

            function clearFeedback(node) {
                node.className = 'alert d-none';
                node.textContent = '';
            }

            function parseJsonSafe(text) {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    return {};
                }
            }

            function extractResponseMessage(payload, rawText, fallbackMessage) {
                if (payload && payload.message) {
                    return payload.message;
                }

                if (rawText) {
                    var trimmedText = rawText.trim();

                    if (trimmedText && trimmedText.charAt(0) !== '<') {
                        return trimmedText;
                    }
                }

                return fallbackMessage;
            }

            document.addEventListener('DOMContentLoaded', function () {
                var backupButton = document.getElementById('backup-download-button');
                var backupWrapper = document.getElementById('backup-progress-wrapper');
                var backupBar = document.getElementById('backup-progress-bar');
                var backupLabel = document.getElementById('backup-progress-label');
                var backupPercent = document.getElementById('backup-progress-percent');
                var backupFeedback = document.getElementById('backup-feedback');

                if (backupButton) {
                    backupButton.addEventListener('click', function () {
                        var backupUrl = backupButton.getAttribute('data-download-url');
                        var progressValue = 8;
                        var timer = null;

                        clearFeedback(backupFeedback);
                        backupButton.disabled = true;
                        backupBar.classList.remove('bg-danger');
                        backupBar.classList.add('bg-success');
                        setProgressState({
                            wrapper: backupWrapper,
                            bar: backupBar,
                            label: backupLabel,
                            percentNode: backupPercent,
                            percent: progressValue,
                            labelText: 'Preparando respaldo...'
                        });

                        timer = window.setInterval(function () {
                            if (progressValue < 90) {
                                progressValue += progressValue < 50 ? 8 : 4;
                            }

                            setProgressState({
                                wrapper: backupWrapper,
                                bar: backupBar,
                                label: backupLabel,
                                percentNode: backupPercent,
                                percent: Math.min(progressValue, 90),
                                labelText: progressValue < 65 ? 'Empaquetando archivos...' : 'Finalizando descarga...'
                            });
                        }, 450);

                        fetch(backupUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                            .then(function (response) {
                                if (!response.ok) {
                                    return response.text().then(function (text) {
                                        var payload = parseJsonSafe(text);
                                        throw new Error(payload.message || text || 'No se pudo generar el respaldo.');
                                    });
                                }

                                return response.json();
                            })
                            .then(function (payload) {
                                window.clearInterval(timer);
                                setProgressState({
                                    wrapper: backupWrapper,
                                    bar: backupBar,
                                    label: backupLabel,
                                    percentNode: backupPercent,
                                    percent: 100,
                                    labelText: 'Respaldo listo. Iniciando descarga...'
                                });
                                setFeedback(backupFeedback, 'success', payload.message || 'Respaldo listo para descargar.');
                                window.setTimeout(function () {
                                    window.location.href = payload.download_url || backupUrl;
                                    backupButton.disabled = false;
                                }, 500);
                            })
                            .catch(function (error) {
                                window.clearInterval(timer);
                                backupButton.disabled = false;
                                setProgressState({
                                    wrapper: backupWrapper,
                                    bar: backupBar,
                                    label: backupLabel,
                                    percentNode: backupPercent,
                                    percent: 100,
                                    labelText: 'No se pudo completar el respaldo.'
                                });
                                backupBar.classList.remove('bg-success');
                                backupBar.classList.add('bg-danger');
                                setFeedback(backupFeedback, 'danger', error.message || 'No se pudo generar el respaldo.');
                            });
                    });
                }

                var restoreForm = document.getElementById('restore-backup-form');
                var restoreButton = document.getElementById('restore-backup-button');
                var restoreWrapper = document.getElementById('restore-progress-wrapper');
                var restoreBar = document.getElementById('restore-progress-bar');
                var restoreLabel = document.getElementById('restore-progress-label');
                var restorePercent = document.getElementById('restore-progress-percent');
                var restoreFeedback = document.getElementById('restore-feedback');

                if (restoreForm) {
                    restoreForm.addEventListener('submit', function (event) {
                        event.preventDefault();

                        if (!window.confirm('Se restaurara la base de datos y los archivos del sistema. Deseas continuar?')) {
                            return;
                        }

                        var formData = new FormData(restoreForm);
                        var restoreRequest = new XMLHttpRequest();
                        var processingPercent = 70;
                        var processingTimer = null;

                        clearFeedback(restoreFeedback);
                        restoreButton.disabled = true;
                        restoreBar.classList.remove('bg-danger', 'bg-success');
                        restoreBar.classList.add('bg-warning');
                        setProgressState({
                            wrapper: restoreWrapper,
                            bar: restoreBar,
                            label: restoreLabel,
                            percentNode: restorePercent,
                            percent: 0,
                            labelText: 'Preparando restauracion...'
                        });

                        restoreRequest.open('POST', restoreForm.getAttribute('action'), true);
                        restoreRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        restoreRequest.setRequestHeader('Accept', 'application/json');

                        restoreRequest.upload.addEventListener('progress', function (progressEvent) {
                            if (!progressEvent.lengthComputable) {
                                return;
                            }

                            var uploadPercent = Math.round((progressEvent.loaded / progressEvent.total) * 70);
                            processingPercent = Math.max(uploadPercent, 10);

                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: processingPercent,
                                labelText: 'Subiendo respaldo al servidor...'
                            });
                        });

                        restoreRequest.upload.addEventListener('load', function () {
                            processingTimer = window.setInterval(function () {
                                if (processingPercent < 95) {
                                    processingPercent += 3;
                                }

                                setProgressState({
                                    wrapper: restoreWrapper,
                                    bar: restoreBar,
                                    label: restoreLabel,
                                    percentNode: restorePercent,
                                    percent: Math.min(processingPercent, 95),
                                    labelText: 'Aplicando restauracion...'
                                });
                            }, 500);
                        });

                        restoreRequest.onload = function () {
                            var payload = parseJsonSafe(restoreRequest.responseText);
                            window.clearInterval(processingTimer);
                            restoreButton.disabled = false;

                            if (restoreRequest.status >= 200 && restoreRequest.status < 300) {
                                restoreBar.classList.remove('bg-warning');
                                restoreBar.classList.add('bg-success');
                                setProgressState({
                                    wrapper: restoreWrapper,
                                    bar: restoreBar,
                                    label: restoreLabel,
                                    percentNode: restorePercent,
                                    percent: 100,
                                    labelText: 'Restauracion completada.'
                                });
                                setFeedback(restoreFeedback, 'success', payload.message || 'Respaldo restaurado correctamente.');
                                window.setTimeout(function () {
                                    window.location.reload();
                                }, 1200);
                                return;
                            }

                            restoreBar.classList.remove('bg-warning');
                            restoreBar.classList.add('bg-danger');
                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: 100,
                                labelText: 'No se pudo completar la restauracion.'
                            });
                            setFeedback(
                                restoreFeedback,
                                'danger',
                                extractResponseMessage(payload, restoreRequest.responseText, 'No se pudo restaurar el respaldo.')
                            );
                        };

                        restoreRequest.onerror = function () {
                            window.clearInterval(processingTimer);
                            restoreButton.disabled = false;
                            restoreBar.classList.remove('bg-warning');
                            restoreBar.classList.add('bg-danger');
                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: 100,
                                labelText: 'La conexion fallo durante la restauracion.'
                            });
                            setFeedback(restoreFeedback, 'danger', 'La conexion fallo durante la restauracion.');
                        };

                        restoreRequest.send(formData);
                    });
                }
            });
        })();
    </script>
@endonce
