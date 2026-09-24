<table class="table mb-0">
<thead >
<tr>
<th>ID</th>
<th>Created On</th>
<th>City From</th>
<th>City To</th>
<th>Sender Name</th>
<th>Sender Phone</th>
<th>Receiver Name</th>
<th>Receiver Phone</th>
<th>Category</th>
<th>Value</th>
<th>Reward</th>
<th>Weight</th>
<th>Created By</th>
<th>Carried By</th>
<th>Status</th>
<th class="text-right">Options</th>
</tr>
</thead>
<tbody>

@foreach($data as $row)
<tr>
<td>{{ $row->id }}</td>
<td>{{ date('d-M-Y',strtotime($row->created_at)) }}

@if($row->order_date)
<br>
<small style="color:red">Needed before: {{ date('d-M-Y',strtotime($row->order_date)) }}</small>

@endif

</td>
<td>{{ $row->s_city }}</td>
<td>{{ $row->r_city }}</td>
<td>{{ $row->s_name }}</td>
<td>{{ $row->s_phone }}</td>
<td>{{ $row->r_name }}</td>
<td>{{ $row->r_phone }}</td>
<td>{{ $row->cate }}</td>
<td>{{ $row->value }}</td>
<td>${{ $row->amount }}</td>
<td>{{ $row->weight }}</td>
<td>{{ $row->user_name }}</td>
<td>{{ $row->carrier_name }}</td>

<td>
@if($row->status == 'Unassigned')

<small style="color:red">Unassigned</small>

@elseif($row->status == 'Assigned')

<small style="color:blue">Assigned</small>

@elseif($row->status == 'Picked')

<small style="color:orange">Picked</small>

@elseif($row->status == 'Delivered')

<small style="color:green">Delivered</small>

@elseif($row->status == 'Cancelled')

<small style="color:red">Cancelled</small>

@elseif($row->status == 'Expired')

<small style="color:red">Expired</small>


@endif
</td>


<td class="text-right">
@include('orders.button')
<br>
</td>
</tr>
@endforeach

</tbody>
</table>