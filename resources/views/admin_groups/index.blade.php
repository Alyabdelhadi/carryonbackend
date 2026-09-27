@extends('layout.main')

@section('title') Groups & Permissions @endsection

@section('content')
<x-admin.page-header title="Groups &amp; permissions" subtitle="Each admin belongs to one group. Tick what the group can view, create, edit and delete on every page.">
    @can('admins.view')
        <a href="{{ Asset('admins') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-user-check"></i> Admins</a>
    @endcan
    <x-admin.add-button module="admin_groups" :href="Asset('admin-groups/create')" label="New group" />
</x-admin.page-header>

@php $total = \App\Models\AdminGroup::totalPermissions(); @endphp
<div class="row">
@foreach($groups as $group)
    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-1">
                    <div class="co-stat-icon {{ $group->is_super ? 'is-ink' : 'is-leaf' }}"><i class="feather icon-{{ $group->is_super ? 'star' : 'shield' }}"></i></div>
                    <div class="d-inline-flex">
                        @if(!$group->is_super)
                            @can('admin_groups.edit')
                                <a class="btn btn-icon btn-info" href="{{ Asset('admin-groups/'.$group->id.'/edit') }}" data-toggle="tooltip" title="Edit permissions"><i class="feather icon-edit-2"></i></a>
                            @endcan
                            @can('admin_groups.delete')
                                <form method="POST" action="{{ Asset('admin-groups/'.$group->id) }}" data-co-confirm="Delete the group {{ $group->name }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-icon btn-danger" data-toggle="tooltip" title="Delete"><i class="feather icon-trash-2"></i></button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </div>
                <h4 class="mb-25" style="font-weight:700">{{ $group->name }}</h4>
                <p class="text-muted mb-2">{{ $group->description ?: 'No description' }}</p>
                @php $count = $group->permissionCount(); @endphp
                <div class="d-flex justify-content-between small text-muted mb-50">
                    <span>{{ $group->is_super ? 'Full access' : $count . ' of ' . $total . ' permissions' }}</span>
                    <span>{{ $group->users_count }} {{ \Illuminate\Support\Str::plural('admin', $group->users_count) }}</span>
                </div>
                <div class="progress" style="height:6px;border-radius:6px;background:var(--co-bg)">
                    <div class="progress-bar" style="width: {{ $total ? round($count / $total * 100) : 0 }}%;background:{{ $group->is_super ? 'var(--co-ink)' : 'var(--co-leaf)' }}"></div>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>
@endsection
