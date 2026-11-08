<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Nodos - Zona: '.$zone->name) }} 
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
   
    @livewire('node-component', ['zone_id'=>$zone->id])

    <x-slot name="script">
        
        <script type="text/javascript">            
            window.livewire.on('nodeUpdateStore', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });
            window.livewire.on('nodeEditCreate', (message) => {
                $('#exampleModal').modal('show');
                $('.titulo_pagina').html(message)
            });
            function EliminarNode(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminara el nodo "+name+". Esta accion es irreversible",
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
                        window.livewire.on('nodeDelete', () => {
                            swal('Nodo Eliminado', {icon: "success",});
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>