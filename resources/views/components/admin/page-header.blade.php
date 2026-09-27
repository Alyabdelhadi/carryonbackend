@props(['title', 'subtitle' => null])
{{-- Page title with its actions on the right: <x-admin.page-header title="Services"> buttons </x-admin.page-header> --}}
<div class="co-page-head">
    <div>
        <h2>{{ $title }}</h2>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @if(trim($slot) !== '')
        <div class="co-page-actions">{{ $slot }}</div>
    @endif
</div>
