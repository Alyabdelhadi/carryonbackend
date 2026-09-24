@extends('layout.main')
@section('title') Cities @endsection
@section('content')

<section id="basic-input">
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="row" id="table-head">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">

                                <div class="card-body">
                                    <h4 class="card-title">
                                        Cities
                                        <a href="{{ Asset($link.'add') }}" class="btn btn-primary" style="float: right">Add New</a>
                                    </h4>
                                
                                    {{-- Search Bar --}}
                                    <form method="GET" action="{{ url()->current() }}">
                                        <div class="row mb-2">
                                            <div class="col-md-4">
                                                <input type="text" name="search" class="form-control" placeholder="Search by city or country" value="{{ request('search') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-primary">Search</button>
                                                <a href="{{ url()->current() }}" class="btn btn-secondary ml-1">Reset</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Arabic Name</th>
                                                <th>Country</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Options</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($data as $row)
                                            <tr>
                                                <td>{{ $row->name }}</td>
                                                <td dir="rtl">{{ $row->name_ar }}</td>
                                                <td>{{ $row->country->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if(!empty($row->image))
                                                        <a href="{{ asset('upload/cities/' . $row->image) }}">
                                                            <img src="{{ asset('upload/cities/' . $row->image) }}" width="50" alt="Image">
                                                        </a>
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td>
                                                    <a onclick="return confirm('Are you sure?')" href="{{ Asset('cityStatus?id='.$row->id) }}">
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
                                                    <a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light"
                                                       data-toggle="tooltip" data-placement="top" title="Edit"
                                                       href="{{ Asset($link.$row->id.'/edit') }}">
                                                        <i class="feather icon-edit"></i>
                                                    </a>

                                                    <a type="button"
                                                       class="btn btn-icon btn-danger mr-1 mb-1 waves-effect waves-light"
                                                       data-toggle="tooltip" data-placement="top" title="Delete"
                                                       onclick="confirmAlert('{{ Asset($link.'delete/'.$row->id) }}')">
                                                        <i class="feather icon-trash-2"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Info -->
                                <div class="mt-3 ml-2">
                                    <p class="mb-2">
                                        Showing page {{ $data->currentPage() }} of {{ $data->lastPage() }} — Total results: {{ $data->total() }}
                                    </p>
                                
                                    <!-- Pagination Links -->
                                    <div>
                                        {{ $data->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                                    </div>
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