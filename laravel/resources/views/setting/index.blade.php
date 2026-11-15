<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Configuración del Sistema') }}
    </x-slot>
    <x-slot name="opciones_nav">

    </x-slot>
    @livewire('setting-component')

    <x-slot name="script">
        <script type="text/javascript"> 
            window.livewire.on('infoGuardada', (message) => {
                swal(message, {icon: "success",});
            });      
        </script>
    </x-slot>
</x-app-layout>