<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Cobrador: '.$user->name_complete) }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @include('yield.resume-user')
</x-app-layout>