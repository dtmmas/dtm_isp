<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Rutas de fibra') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('map-fiber-route-component')

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('mapFiberSaved', (message) => {
                swal(message, {icon: "success"});
                $('#mapFiberModal').modal('hide');
            });

            window.livewire.on('mapFiberEdit', (message) => {
                $('#mapFiberModal').modal('show');
                $('.titulo_pagina').html(message);
            });

            window.livewire.on('mapFiberDeleted', () => {
                swal('Ruta eliminada', {icon: "success"});
            });

            function EliminarMapFiber(id, name) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara la ruta " + name + ". Esta accion es irreversible",
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
                        window.livewire.emit('onDeleteMapFiberRow', id);
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
