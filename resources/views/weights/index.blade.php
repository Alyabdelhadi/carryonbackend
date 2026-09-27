@extends('layout.main')

@section('title') Weights @endsection

@section('content')
<x-admin.page-header title="Weights" subtitle="Package weights offered in the order form and the calculator.">
    <x-admin.add-button module="weights" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="bar-chart-2" title="No weights yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sort No</th>
                    <th>Weight (kg)</th>
                    <th>Order form</th>
                    <th>Calculator</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-muted">{{ $row->sort_no }}</td>
                    <td><strong>{{ $row->value }} kg</strong></td>
                    <td>@if($row->in_order_form) <span class="badge badge-success">Yes</span> @else <span class="text-muted">—</span> @endif</td>
                    <td>@if($row->in_calculator) <span class="badge badge-success">Yes</span> @else <span class="text-muted">—</span> @endif</td>
                    <td><x-admin.status-toggle module="weights" :url="Asset('weightStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="weights" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
