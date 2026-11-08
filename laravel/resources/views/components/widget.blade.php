@props(['title','color','total'])
@php
    $icons = [
        'primary' => 'fas fa-chart-line',
        'info' => 'fas fa-credit-card',
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-clock',
        'danger' => 'fas fa-exclamation-triangle',
    ];

    $icon = $icons[$color] ?? 'fas fa-chart-pie';
@endphp

<div class="white-box analytics-info widget-color-{{ $color }}">
    <div class="widget-icon" aria-hidden="true">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="widget-copy">
        <span class="widget-label">{{ $title }}</span>
        <strong class="widget-total">{{ $total }}</strong>
    </div>
</div>
