<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Cobradores') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('collector.collector-component')

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('collectorUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('collectorEdit', () => {
                $('#exampleModal').modal('show');
            });

            window.livewire.on('zoneUserEdit', (message) =>{
                $('#zoneModalLabel').html(message)
                $('#zoneModal').modal('show')
            })
            window.livewire.on('zoneUserUpdate', (message) => {
                $('#zoneModal').modal('hide');
                swal(message, {icon: "success",});
            });

            function EliminarCollector(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el cobrador "+name+". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Eliminar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeleteRow',id)
                        window.livewire.on('collectorDelete', () => {
                            swal('Cobrador Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>