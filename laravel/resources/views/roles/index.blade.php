<x-app-layout>
    <x-slot name="titulo_pagina">
        <span class="titulo_pagina">{{ __('Listado de Roles') }}</span>
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('role-component')

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('roleUpdateStore', (message) => {
                $('.titulo_pagina').html('Listado de Roles')
                swal(message, {icon: "success",});
            });
            window.livewire.on('verTablaForm', (message) => {
                $('.titulo_pagina').html(message)
            });
            function EliminarRole(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el rol "+name+". Esta accion es irreversible",
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
                        window.livewire.on('roleDelete', () => {
                            swal('Rol Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>