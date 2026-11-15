<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Listado de Planes') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            
        </ol>
    </x-slot>
    @livewire('collector-product-component', ['collector'=>Auth::user()])
</x-app-layout>