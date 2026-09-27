@php
    $admin = auth()->user();
    $initials = collect(explode(' ', trim($admin->name ?: $admin->username)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<header class="co-topbar">
    <button type="button" class="co-icon-btn co-menu-btn" data-co-sidebar-open aria-label="Menu">
        <i class="feather icon-menu"></i>
    </button>
    <h1 class="co-topbar-title">@yield('title')</h1>

    <div class="co-topbar-actions">
        <div class="dropdown">
            <button type="button" class="co-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="co-avatar">{{ $initials ?: 'A' }}</span>
                <span class="co-user-meta">
                    <strong>{{ $admin->name ?: $admin->username }}</strong>
                    <small>{{ $admin->group->name ?? 'No group' }}</small>
                </span>
                <i class="feather icon-chevron-down"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right co-dropdown">
                <a class="dropdown-item" href="{{ Asset('setting') }}"><i class="feather icon-settings"></i> Account settings</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="{{ Asset('logout') }}"><i class="feather icon-log-out"></i> Log out</a>
            </div>
        </div>
    </div>
</header>
