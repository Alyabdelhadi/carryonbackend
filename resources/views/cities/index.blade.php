@extends('layout.main')

@section('title') Cities @endsection

@section('content')
<x-admin.page-header title="Cities" subtitle="Cities for trips and addresses, with their Arabic names.">
    <x-admin.add-button module="cities" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    <form method="GET" action="{{ url()->current() }}" class="co-toolbar">
        <div class="co-search">
            <i class="feather icon-search"></i>
            <input type="search" name="search" class="form-control" placeholder="Search by city or country" value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search'))
            <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
        @endif
    </form>
    @if($data->total() === 0)
        <x-admin.empty icon="map-pin" title="No cities found" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Arabic Name</th>
                    <th>Country</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td dir="rtl">{{ $row->name_ar }}</td>
                    <td>{{ $row->country->name ?? 'N/A' }}</td>
                    <td>@if(!empty($row->image)) <a href="{{ asset('upload/cities/' . $row->image) }}"><img src="{{ asset('upload/cities/' . $row->image) }}" height="48" alt="Image"></a> @else <span class="text-muted">No Image</span> @endif</td>
                    <td><x-admin.status-toggle module="cities" :url="Asset('cityStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="cities" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-wrap align-items-center justify-content-between px-2 py-1" style="gap:12px">
        <span class="text-muted small">Page {{ $data->currentPage() }} of {{ $data->lastPage() }} · {{ $data->total() }} cities</span>
        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
