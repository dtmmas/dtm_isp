<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Zonas - Cobrador: '.$collector->name_complete) }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('collector.zone-component', ['collector' => $collector])

    <x-slot name="script">
        <script type="text/javascript">
            window.livewire.on('zoneUpdate', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            function EliminarZone(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se quitara la zona "+name+". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Quitar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeleteRow',id)
                        window.livewire.on('zoneDelete', () => {
                            swal('Se quito la zona correctamente', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>