<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Buscar de Cliente') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('client-component', ['searchCollector'=> true])

    <x-slot name="script">
    </x-slot>
</x-app-layout>