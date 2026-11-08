<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Coberturas') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('map-coverage-component')

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('mapCoverageSaved', (message) => {
                swal(message, {icon: "success"});
                $('#mapCoverageModal').modal('hide');
            });

            window.livewire.on('mapCoverageEdit', (message) => {
                $('#mapCoverageModal').modal('show');
                $('.titulo_pagina').html(message);
            });

            window.livewire.on('mapCoverageDeleted', () => {
                swal('Cobertura eliminada', {icon: "success"});
            });

            function EliminarMapCoverage(id, name) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara la cobertura " + name + ". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Eliminar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                }).then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeleteMapCoverageRow', id);
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
