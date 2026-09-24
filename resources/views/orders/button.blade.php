<a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="View Details" href="{{ Asset('parcel_order_view?id='.$row->id) }}" target="_blank"><i class="fa fa-eye"></i></a>
<a class="btn btn-icon btn-warning mr-1 mb-1 waves-effect waves-light" 
   data-toggle="tooltip" 
   data-placement="top" 
   data-original-title="Edit"
   href="{{ route('parcel.edit', $row->id) }}" 
   target="_blank">
   <i class="fa fa-edit"></i>
</a>
<a href="{{ route('parcel.notify', $row->id) }}" 
   class="btn btn-icon btn-success mr-1 mb-1 waves-effect waves-light" 
   data-toggle="tooltip" 
   data-placement="top" 
   data-original-title="Notify">
   <i class="feather icon-bell"></i>
</a>
<a type="button" class="btn btn-icon btn-danger mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Delete" onclick="confirmAlert('{{ Asset('parcel_order/delete/'.$row->id) }}')"><i class="feather icon-trash-2"></i></a>
