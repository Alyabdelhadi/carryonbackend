@props(['module', 'url', 'active', 'on' => 'Active', 'off' => 'Inactive', 'confirm' => 'Change the status?'])
{{-- Status chip; clickable (with a confirmation) only with the "edit" tick. --}}
@php $chip = '<span class="chip ' . ($active ? 'chip-success' : 'chip-danger') . '"><span class="chip-body"><span class="chip-text">' . e($active ? $on : $off) . '</span></span></span>'; @endphp
@can($module . '.edit')
    <a href="{{ $url }}" data-co-go="{{ $confirm }}" data-toggle="tooltip" title="Click to change">{!! $chip !!}</a>
@else
    {!! $chip !!}
@endcan
