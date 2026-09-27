@extends('layout.main')

@section('title') Services @endsection

@section('content')
<x-admin.page-header title="Services" subtitle="The services shown on the app home screen.">
    <x-admin.add-button module="services" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="layers" title="No services yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Image</th>
                    <th>Service Name</th>
                    <th>Arabic Name</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td>@if($row->img) <img src="{{ Asset('upload/services/'.$row->img) }}" height="48" alt="Service"> @else <span class="text-muted">—</span> @endif</td>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td dir="rtl">{{ $row->name_ar }}</td>
                    <td><x-admin.status-toggle module="services" :url="Asset('serviceStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="services" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
