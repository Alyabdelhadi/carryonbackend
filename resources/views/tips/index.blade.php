@extends('layout.main')

@section('title') Tips @endsection

@section('content')
<x-admin.page-header title="Tips" subtitle="Reward amounts senders can pick for carriers.">
    <x-admin.add-button module="tips" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="award" title="No tips yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Reward</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td><strong>{{ (float) $row->value == 0 && is_numeric($row->value) ? 'Free' : $row->value }}</strong></td>
                    <td><x-admin.status-toggle module="tips" :url="Asset('tipStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="tips" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
