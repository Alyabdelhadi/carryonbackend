@php
    $statusClass = [
        'Unassigned' => 'badge-warning',
        'Assigned' => 'badge-info',
        'Picked' => 'badge-info',
        'Transit' => 'badge-info',
        'Delivered' => 'badge-success',
        'Cancelled' => 'badge-danger',
        'Expired' => 'badge-secondary',
    ];
@endphp
<table class="table">
<thead>
<tr>
    <th>ID</th>
    <th>Created On</th>
    <th>Route</th>
    <th>Sender</th>
    <th>Receiver</th>
    <th>Category</th>
    <th>Value</th>
    <th>Reward</th>
    <th>Weight</th>
    <th>Created By</th>
    <th>Carried By</th>
    <th>Status</th>
    <th class="text-right">Actions</th>
</tr>
</thead>
<tbody>
@foreach($data as $row)
<tr>
    <td class="text-muted">#{{ $row->id }}</td>
    <td class="text-nowrap">
        {{ date('d-M-Y',strtotime($row->created_at)) }}
        @if($row->order_date)
            <div class="small text-danger">Needed before: {{ date('d-M-Y',strtotime($row->order_date)) }}</div>
        @endif
    </td>
    <td class="text-nowrap">
        <strong>{{ $row->s_city }}</strong>
        <i class="feather icon-arrow-right text-muted mx-25"></i>
        <strong>{{ $row->r_city }}</strong>
    </td>
    <td>{{ $row->s_name }}<div class="text-muted small text-nowrap">{{ $row->s_phone }}</div></td>
    <td>{{ $row->r_name }}<div class="text-muted small text-nowrap">{{ $row->r_phone }}</div></td>
    <td>{{ $row->cate }}</td>
    <td>{{ $row->value }}</td>
    <td class="text-nowrap"><strong>${{ $row->amount }}</strong><div class="small text-muted">{{ $row->paymentLabel() }}</div></td>
    <td>{{ $row->weight }}</td>
    <td>{{ $row->user_name }}</td>
    <td>{{ $row->carrier_name ?: '—' }}</td>
    <td>
        @if($row->status)
            <span class="badge {{ $statusClass[$row->status] ?? 'badge-secondary' }}">{{ $row->status }}</span>
        @endif
    </td>
    <td class="text-right">
        @include('orders.button')
    </td>
</tr>
@endforeach
</tbody>
</table>
