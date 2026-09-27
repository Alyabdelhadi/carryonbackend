@extends('layout.main')

@section('title') Parcel Categories @endsection

@section('content')
<x-admin.page-header title="Parcel Categories" subtitle="What senders can ship: documents, electronics, clothes…">
    <x-admin.add-button module="categories" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="tag" title="No categories yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Arabic Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td>@if($row->img) <img src="{{ Asset('upload/categories/'.$row->img) }}" height="48" alt="Category"> @else <span class="text-muted">—</span> @endif</td>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td dir="rtl">{{ $row->name_ar }}</td>
                    <td class="text-muted" style="max-width:320px">{{ $row->text }}</td>
                    <td><x-admin.status-toggle module="categories" :url="Asset('parcelCateStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="categories" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
