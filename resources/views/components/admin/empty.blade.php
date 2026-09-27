@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'message' => null])
<div class="co-empty">
    <i class="feather icon-{{ $icon }}"></i>
    <h4>{{ $title }}</h4>
    @if($message)<p class="mb-0">{{ $message }}</p>@endif
    {{ $slot }}
</div>
