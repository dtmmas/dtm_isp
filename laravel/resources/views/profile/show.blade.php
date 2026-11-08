<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Mi Perfil ') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @include('yield.resume-user')
    <hr>

    <div class="mt-10 sm:mt-0">
        <h3>Cambiar Contraseña</h3><br>
        @livewire('profile.update-password-form')
    </div>

    <x-slot name="script">
    <script type="text/javascript">            
        window.livewire.on('saved', () => {
            swal('Cambio exitoso', {icon: "success",});
        });

    </script>
    </x-slot>
</x-app-layout>
