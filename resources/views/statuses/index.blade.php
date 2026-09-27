@extends('layout.main')

@section('title') Delivery Statuses @endsection

@section('content')
<x-admin.page-header title="Delivery Statuses" subtitle="The steps shown on the order timeline.">
    <x-admin.add-button module="statuses" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="truck" title="No delivery statuses yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td>@if($row->img) <img src="{{ Asset('upload/statuses/'.$row->img) }}" height="48" alt="Status"> @else <span class="text-muted">—</span> @endif</td>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td class="text-muted" style="max-width:320px">{{ $row->text }}</td>
                    <td><x-admin.status-toggle module="statuses" :url="Asset('deliveryStatusStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="statuses" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
