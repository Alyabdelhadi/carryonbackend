@extends('layout.main')
@section('title') {{ $title ?? 'Users' }} @endsection
@section('content')

<x-admin.page-header :title="$title ?? 'Users'" subtitle="Senders and carriers using the CarryOn app.">
    <x-admin.add-button module="users" :href="Asset('users/add')" label="Add user" />
</x-admin.page-header>

<div class="card">
    <form method="GET" class="co-toolbar">
        <div class="co-search">
            <i class="feather icon-search"></i>
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, email or phone">
        </div>
        <select name="per_page" class="form-control" style="width:auto" onchange="this.form.submit()" aria-label="Rows per page">
            @foreach([10, 25, 50, 100] as $size)
                <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }} rows</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search'))
            <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
        @endif
    </form>

    @if($data->isEmpty())
        <x-admin.empty icon="users" title="No users found" />
    @else
    <div class="table-responsive">
    <table class="table">
    <thead>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Phone</th>
        <th>Registered</th>
        <th>ID doc</th>
        <th>Activity</th>
        <th>Rating</th>
        <th>Status</th>
        <th>Verification</th>
        <th class="text-right">Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
    <tr>
        <td class="text-muted">#{{ $row->id }}</td>
        <td>
            <div class="d-flex align-items-center" style="gap:10px;min-width:220px">
                @if(!empty($row->selfie))
                    <a href="{{ asset('upload/selfies/' . $row->selfie) }}" data-lightbox="user-{{ $row->id }}-selfie" data-title="Selfie">
                        <img src="{{ asset('upload/selfies/' . $row->selfie) }}" width="40" height="40" loading="lazy" alt="Selfie" style="border-radius:50%">
                    </a>
                @else
                    <span class="co-avatar">{{ mb_strtoupper(mb_substr($row->name, 0, 1)) }}</span>
                @endif
                <div>
                    <strong>{{ $row->name }}</strong>
                    <div class="text-muted small">{{ $row->email }}</div>
                    @if($row->rcode)<div class="text-muted small">Code {{ $row->rcode }}</div>@endif
                </div>
            </div>
        </td>
        <td class="text-nowrap">{{ $row->phone }}</td>
        <td class="text-nowrap">{{ date('Y-m-d',strtotime($row->created_at)) }}<div class="text-muted small">{{ date('h:i A',strtotime($row->created_at)) }}</div></td>
        @php $extension = strtolower(pathinfo((string) $row->identity, PATHINFO_EXTENSION)); @endphp
        <td>
            @if(!empty($row->identity))
                @if($extension === 'pdf')
                    <a href="{{ asset('users/' . $row->id . '/identity') }}" target="_blank" class="text-nowrap">
                        <i class="feather icon-file-text text-danger"></i> PDF
                    </a>
                @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heif', 'heic']))
                    <a href="{{ asset('users/' . $row->id . '/identity') }}" data-lightbox="user-{{ $row->id }}-identity" data-title="Identity">
                        <img src="{{ asset('users/' . $row->id . '/identity') }}" width="44" height="44" loading="lazy" alt="Identity">
                    </a>
                @else
                    <span class="text-muted">Unknown file</span>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-nowrap small">
            <div><strong>{{ $row->packages_count }}</strong> sent · <strong>{{ $row->carried_packages_count }}</strong> carried</div>
            <div class="text-muted">{{ $row->trips_count }} {{ \Illuminate\Support\Str::plural('trip', $row->trips_count) }}</div>
        </td>
        <td class="text-nowrap">@if($row->average_rating)<i class="feather icon-star" style="color:var(--co-warn)"></i> {{ $row->average_rating }}@else<span class="text-muted">—</span>@endif</td>
        <td>
            <x-admin.status-toggle module="users" :url="Asset('userStatus?id='.$row->id)" :active="$row->status == 1" />
        </td>
        <td>
            @if($row->identity_status == 'pending')
                <span class="chip chip-warning"><span class="chip-body"><span class="chip-text">Under review</span></span></span>
                @can('users.edit')
                <div class="mt-50 text-nowrap">
                    <a class="btn btn-sm btn-success" href="{{ Asset('userVerification?id='.$row->id.'&action=approve') }}" data-co-go="Approve this user's identity? Check that the selfie matches the ID first." data-co-go-label="Approve">Approve</a>
                    <a class="btn btn-sm btn-outline-danger" href="{{ Asset('userVerification?id='.$row->id.'&action=reject') }}" data-co-go="Reject? The user will be asked to upload new photos." data-co-go-label="Reject">Reject</a>
                </div>
                @endcan
            @else
                @php
                    $verifyChip = $row->is_verified
                        ? ['chip-success', 'Verified']
                        : (in_array($row->identity_status, ['declined', 'invalid']) ? ['chip-danger', ucfirst($row->identity_status)] : ['', 'Not verified']);
                @endphp
                @can('users.edit')
                    <a href="{{ Asset('userVerification?id='.$row->id.'&action='.($row->is_verified ? 'revoke' : 'approve')) }}"
                       data-co-go="{{ $row->is_verified ? 'Remove verification? The user will have to verify again in the app.' : 'Mark this user as verified?' }}">
                        <span class="chip {{ $verifyChip[0] }}"><span class="chip-body"><span class="chip-text">{{ $verifyChip[1] }}</span></span></span>
                    </a>
                @else
                    <span class="chip {{ $verifyChip[0] }}"><span class="chip-body"><span class="chip-text">{{ $verifyChip[1] }}</span></span></span>
                @endcan
            @endif
        </td>
        <td class="text-right">
            <x-admin.row-actions module="users" :edit="Asset('users/'.$row->id.'/edit')" :delete="Asset('users/delete/'.$row->id)"
                :delete-text="'Delete ' . $row->name . '? This permanently removes the user and all their packages, trips, addresses, ratings and wallet history.'" />
        </td>
    </tr>
    @endforeach
    </tbody>
    </table>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1" style="gap:12px">
        <span class="text-muted small">Page {{ $data->currentPage() }} of {{ $data->lastPage() }} · {{ number_format($data->total()) }} users</span>
        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection
