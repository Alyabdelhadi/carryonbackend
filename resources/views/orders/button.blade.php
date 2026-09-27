<x-admin.row-actions module="orders" :edit="route('parcel.edit', $row->id)" :delete="Asset('parcel_order/delete/'.$row->id)">
    @can('orders.view')
        <a class="btn btn-icon btn-light" href="{{ Asset('parcel_order_view?id='.$row->id) }}" target="_blank" data-toggle="tooltip" title="View details"><i class="feather icon-eye"></i></a>
    @endcan
    @can('orders.edit')
        <a class="btn btn-icon btn-success" href="{{ route('parcel.notify', $row->id) }}" data-co-go="Send the carriers a push about this order?" data-co-go-label="Notify" data-toggle="tooltip" title="Notify"><i class="feather icon-bell"></i></a>
    @endcan
</x-admin.row-actions>
