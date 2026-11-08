<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Importar clientes')}}
    </x-slot>
    <x-slot name="opciones_nav">
    </x-slot>
    @livewire('client-importar-component')
    <x-slot name="script">
        <script type="text/javascript"> 
            $(document).ready(function(){
                $('.btnIniciarProceso').on('click',function(){
                    $('.validado').show().addClass('alert-warning').html('1. Validando Información...');    
                })
            })

            window.livewire.on('ValidateClient', (message) => {
                if(message.rps){
                    $('.btnIniciarProceso').attr('disabled','disabled')
                    $('.validado').show().addClass('alert-success').html(message.msj);
                    $('.cargando').show().addClass('alert-warning').html('2. Cargando información en base de datos...');
                    window.livewire.emit('onImportData','')
                }else{
                    $('.validado').show().addClass('alert-danger').html(message.msj);
                }
            });

            window.livewire.on('ImportarClientImport', (message) => {
                if(message.rps){
                    $('.cargando').show().addClass('alert-success').html(message.msj);
                }else{
                    $('.cargando').show().addClass('alert-danger').html(message.msj);
                }
            });

            window.livewire.on('ImportarClientImport', (message) => {
                if(message.rps){
                    $('.cargando').show().addClass('alert-success').html(message.msj);
                }else{
                    $('.cargando').show().addClass('alert-danger').html(message.msj);
                }
            });
        </script>
    </x-slot>        
</x-app-layout>