@extends('layout.main')

@section('title') Admins @endsection

@section('content')
<x-admin.page-header title="Admins" subtitle="People who can sign in to this dashboard. What they see depends on their group.">
    @can('admin_groups.view')
        <a href="{{ Asset('admin-groups') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-shield"></i> Groups &amp; permissions</a>
    @endcan
    <x-admin.add-button module="admins" :href="Asset('admins/create')" label="New admin" />
</x-admin.page-header>

<div class="card">
    <form method="GET" class="co-toolbar">
        <div class="co-search">
            <i class="feather icon-search"></i>
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, username or email">
        </div>
    </form>

    @if($admins->isEmpty())
        <x-admin.empty icon="users" title="No admins found" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Username</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th>Last sign-in</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="co-avatar mr-1">{{ mb_strtoupper(mb_substr($admin->name ?: $admin->username, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $admin->name }}</strong>
                                @if($admin->id === auth()->id()) <span class="badge badge-dark ml-50">You</span> @endif
                                <div class="text-muted small">{{ $admin->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $admin->username }}</td>
                    <td>
                        @if($admin->group)
                            <span class="badge {{ $admin->group->is_super ? 'badge-dark' : 'badge-info' }}">{{ $admin->group->name }}</span>
                        @else
                            <span class="badge badge-warning">No group</span>
                        @endif
                    </td>
                    <td>
                        <span class="chip {{ $admin->is_active ? 'chip-success' : 'chip-danger' }}"><span class="chip-body"><span class="chip-text">{{ $admin->is_active ? 'Active' : 'Disabled' }}</span></span></span>
                    </td>
                    <td class="text-muted">{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</td>
                    <td class="text-right">
                        <div class="d-inline-flex">
                            @can('admins.edit')
                                <a class="btn btn-icon btn-info" href="{{ Asset('admins/'.$admin->id.'/edit') }}" data-toggle="tooltip" title="Edit"><i class="feather icon-edit-2"></i></a>
                            @endcan
                            @can('admins.delete')
                                @if($admin->id !== auth()->id())
                                    <form method="POST" action="{{ Asset('admins/'.$admin->id) }}" data-co-confirm="Delete {{ $admin->name }}? They will no longer be able to sign in.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-icon btn-danger" data-toggle="tooltip" title="Delete"><i class="feather icon-trash-2"></i></button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $admins->links() }}
    @endif
</div>
@endsection
