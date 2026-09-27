{{-- Built from App\Support\AdminModules; only the pages the admin's group can open. --}}
@php
    $path = trim(request()->path(), '/');
    $isActive = fn ($url) => $url && ($path === trim($url, '/') || str_starts_with($path, trim($url, '/') . '/'));
@endphp
<aside class="co-sidebar" id="co-sidebar">
    <a href="{{ Asset(auth()->user()->homeUrl() ?? 'home') }}" class="co-brand">
        <img src="{{ Asset('assets/admin/logo.png') }}" alt="" width="36" height="36">
        <span class="co-brand-text">CarryOn<small>Admin</small></span>
    </a>

    <nav class="co-nav">
        @foreach(\App\Support\AdminModules::sections() as $section => $modules)
            @php $visible = array_filter($modules, fn ($m, $key) => $m['url'] && auth()->user()->canAdmin($key, 'view'), ARRAY_FILTER_USE_BOTH); @endphp
            @if($visible)
                <div class="co-nav-section">{{ $section }}</div>
                @foreach($visible as $key => $module)
                    @php
                        $children = $module['children'] ?? [];
                        $open = $isActive($module['url']) || collect($children)->contains(fn ($c) => $isActive($c['url']));
                        $badge = \App\Support\AdminBadges::count($module['badge'] ?? null)
                            + collect($children)->sum(fn ($c) => \App\Support\AdminBadges::count($c['badge'] ?? null));
                    @endphp
                    @if($children)
                        <div class="co-nav-group {{ $open ? 'is-open' : '' }}">
                            <button type="button" class="co-nav-link {{ $open ? 'is-active' : '' }}" data-co-toggle-group aria-expanded="{{ $open ? 'true' : 'false' }}">
                                <i class="feather icon-{{ $module['icon'] }}"></i>
                                <span>{{ $module['label'] }}</span>
                                @if($badge)<span class="co-nav-badge">{{ $badge }}</span>@endif
                                <i class="feather icon-chevron-down co-nav-caret"></i>
                            </button>
                            <div class="co-nav-children">
                                @foreach($children as $child)
                                    @php $childBadge = \App\Support\AdminBadges::count($child['badge'] ?? null); @endphp
                                    <a href="{{ Asset($child['url']) }}" class="co-nav-child {{ $path === trim($child['url'], '/') ? 'is-active' : '' }}">
                                        <span>{{ $child['label'] }}</span>
                                        @if($childBadge)<span class="co-nav-badge">{{ $childBadge }}</span>@endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ Asset($module['url']) }}" class="co-nav-link {{ $isActive($module['url']) ? 'is-active' : '' }}">
                            <i class="feather icon-{{ $module['icon'] }}"></i>
                            <span>{{ $module['label'] }}</span>
                            @if($badge)<span class="co-nav-badge">{{ $badge }}</span>@endif
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
</aside>
