@props(['module', 'href', 'label' => 'Add New'])
{{-- Shown only with the group's "create" tick for $module. --}}
@can($module . '.create')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-primary co-btn-icon-text']) }}>
        <i class="feather icon-plus"></i> {{ $label }}
    </a>
@endcan
