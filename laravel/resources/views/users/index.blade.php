<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Usuarios') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('user-component')

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('userUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('userEdit', () => {
                $('#exampleModal').modal('show');
            });

            window.livewire.on('roleUserEdit', (message) =>{
                $('#roleModalLabel').html(message)
                $('#roleModal').modal('show')
            })
            window.livewire.on('roleUserUpdate', (message) => {
                $('#roleModal').modal('hide');
                swal(message, {icon: "success",});
            });

            function EliminarUser(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el usuario "+name+". Esta accion es irreversible",
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
                        window.livewire.on('userDelete', () => {
                            swal('Usuario Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>