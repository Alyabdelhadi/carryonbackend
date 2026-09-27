@extends('layout.main')

@section('title') Second Sliders @endsection

@section('content')
<x-admin.page-header title="Second Sliders" subtitle="The second banner row on the app home screen.">
    <x-admin.add-button module="sliders2" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="image" title="No sliders yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Image</th>
                    <th>Arabic Image</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td>@if($row->img) <img src="{{ Asset('upload/sliders/'.$row->img) }}" height="48" style="max-width:160px" alt="Slider"> @else <span class="text-muted">—</span> @endif</td>
                    <td>@if($row->img_ar) <img src="{{ Asset('upload/sliders/'.$row->img_ar) }}" height="48" style="max-width:160px" alt="Arabic slider"> @else <span class="text-muted">—</span> @endif</td>
                    <td><x-admin.status-toggle module="sliders2" :url="Asset('slider2Status?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="sliders2" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
