<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Reporte de Planes por Zona') }}
    </x-slot>
    <x-slot name="opciones_nav">

    </x-slot>
    @livewire('report-plan-zone-component')
</x-app-layout>
