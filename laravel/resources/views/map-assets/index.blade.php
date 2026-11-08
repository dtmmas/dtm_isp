<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Activos de red') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('map-asset-component')

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('mapAssetSaved', (message) => {
                swal(message, {icon: "success"});
                $('#mapAssetModal').modal('hide');
            });

            window.livewire.on('mapAssetEdit', (message) => {
                $('#mapAssetModal').modal('show');
                $('.titulo_pagina').html(message);
            });

            window.livewire.on('mapAssetDeleted', () => {
                swal('Activo eliminado', {icon: "success"});
            });

            function EliminarMapAsset(id, name) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el activo " + name + ". Esta accion es irreversible",
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
                        window.livewire.emit('onDeleteMapAssetRow', id);
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
