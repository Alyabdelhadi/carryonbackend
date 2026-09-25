@extends('layout.main')
@section('title') Users @endsection
@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body d-flex justify-content-between align-items-center">
    <h4 class="card-title mb-0">Users</h4>
    <a href="{{ Asset($link.'add') }}" class="btn btn-primary">Add New</a>
</div>

<form method="GET" class="form-inline mb-2 ml-2 d-flex justify-content-center align-items-center">
    {{-- Search --}}
    <input type="text" name="search" value="{{ request('search') }}" class="form-control mr-2 w-50" placeholder="Search by name, email or phone">
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<div class="table-responsive">
<table class="table mb-0">
<thead >
<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Registration Date</th>
<th>Selfie</th>
<th>Identity</th>
<th>Rating</th>
<th>Trips</th>
<th>Packages</th>
<th>Carried Packages</th>
<th>RCode</th>
<th>Status</th>
<th>Verification</th>
<th>Options</th>
</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
<td>{{ $row->id }}</td>
<td>{{ $row->name }}</td>
<td>{{ $row->phone }}</td>
<td>{{ $row->email }}</td>
<td>{{ date('Y-m-d',strtotime($row->created_at)) }} - {{ date('h:i:A',strtotime($row->created_at)) }}</td>
@if(!empty($row->selfie))
<td>
    <a href="{{ asset('upload/selfies/' . $row->selfie) }}" data-lightbox="user-{{ $row->id }}-selfie" data-title="Selfie">
        <img src="{{ asset('upload/selfies/' . $row->selfie) }}" width="50" loading="lazy" alt="Selfie">
    </a>
</td>
@else
<td>No Image</td>
@endif

@php
    $filePath = public_path('upload/identities/' . $row->identity);
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
@endphp

@if(!empty($row->identity))
    <td>
        @if($extension === 'pdf')
            <a href="{{ asset('users/' . $row->id . '/identity') }}" target="_blank">
                <i class="fas fa-file-pdf fa-2x text-danger"></i> View PDF
            </a>
        @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heif', 'heic']))
            <a href="{{ asset('users/' . $row->id . '/identity') }}" data-lightbox="user-{{ $row->id }}-identity" data-title="Identity">
                <img src="{{ asset('users/' . $row->id . '/identity') }}" width="50" loading="lazy" alt="Identity">
            </a>
        @else
            Unknown file type
        @endif
    </td>
@else
    <td>No File</td>
@endif

<td>
    {{ $row->average_rating }}
</td>

<td>
    {{ $row->trips_count }}
</td>

<td>
    {{ $row->packages_count }}
</td>

<td>
    {{ $row->carried_packages_count }}
</td>

<td>{{ $row->rcode }}</td>

<td>
    <a onclick="return confirm('Are you sure?')" href="{{ Asset('userStatus?id='.$row->id) }}">
    @if($row->status == 1)
    
    <div class="chip chip-success mr-1">
    <div class="chip-body">
    <span class="chip-text">Active</span>
    </div>
    </div>
    
    @else
    
    <div class="chip chip-danger mr-1">
    <div class="chip-body">
    <span class="chip-text">Inactive</span>
    </div>
    </div>
    
    @endif
    </a>
</td>

<td>
    <a onclick="return confirm('{{ $row->is_verified ? 'Remove verification? The user will have to verify again in the app.' : 'Mark this user as verified?' }}')" href="{{ Asset('userVerification?id='.$row->id) }}">
    @if($row->is_verified)
    <div class="chip chip-success mr-1"><div class="chip-body"><span class="chip-text">Verified</span></div></div>
    @elseif($row->identity_status == 'pending')
    <div class="chip chip-warning mr-1"><div class="chip-body"><span class="chip-text">Under review</span></div></div>
    @elseif(in_array($row->identity_status, ['declined', 'invalid']))
    <div class="chip chip-danger mr-1"><div class="chip-body"><span class="chip-text">{{ ucfirst($row->identity_status) }}</span></div></div>
    @else
    <div class="chip mr-1"><div class="chip-body"><span class="chip-text">Not verified</span></div></div>
    @endif
    </a>
</td>

<td>

<a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Edit" href="{{ Asset($link.$row->id.'/edit') }}"><i class="feather icon-edit"></i></a>
</td>
</tr>

@endforeach

</tbody>
</table>
</div>

<!-- Pagination Info -->
<div class="mt-4 ml-2">
    <p>
        Showing page {{ $data->currentPage() }} of {{ $data->lastPage() }} — Total results: {{ $data->total() }}
    </p>
</div>

<!-- Pagination Controls Form -->
<form method="GET" id="pagination-controls" class="form-inline flex-nowrap mt-2 ml-2">
    {{-- Rows per page --}}
    <label for="per_page" class="mr-2">Rows per page:</label>
    <select name="per_page" id="per_page" class="form-control mr-3" onchange="document.getElementById('pagination-controls').submit();">
        @foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }}</option>
        @endforeach
    </select>
</form>

<!-- Pagination Links -->
<div class="container mx-2 my-2 w-100 p-0">
    <div class="d-flex">
        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
</div>
<div class="mt-2 mb-4 ml-2">
    {{ $data->appends(request()->input())->links() }}
</div> 

</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

@endsection