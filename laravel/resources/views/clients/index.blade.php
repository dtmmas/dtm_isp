<x-app-layout>
    <x-slot name="titulo_pagina">
        @if (Auth::user()->hasRole('Cobrador'))
            {{ __('Listado de Mis Clientes') }}
        @else
            {{ __('Listado de Clientes') }}
        @endif
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('client-component', ['searchCollector'=> false])

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('clientUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('clientEdit', () => {
                $('#exampleModal').modal('show');
            });
            
            window.livewire.on('cargarDataRecordatorioShow', () => {
                $('#ModalDataRecordatorio').modal('show');
            });

            window.livewire.on('cargarDataAvisoMoraShow', () => {
                $('#ModalAvisoMora').modal('show');
            });

            window.addEventListener('open-recordatorio-modal', () => {
                $('#ModalDataRecordatorio').modal('show');
            });

            window.addEventListener('open-aviso-mora-modal', () => {
                $('#ModalAvisoMora').modal('show');
            });

            window.livewire.on('zoneUserEdit', (message) =>{
                $('#zoneModalLabel').html(message)
                $('#zoneModal').modal('show')
            })
            window.livewire.on('zoneUserUpdate', (message) => {
                $('#zoneModal').modal('hide');
                swal(message, {icon: "success",});
            });

            function EliminarClient(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el cliente "+name+". Esta accion es irreversible.",
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
                        window.livewire.on('clientDelete', () => {
                            swal('Cliente Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
