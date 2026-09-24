@extends('layout.main')
@section('title') Notification Templates @endsection



@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-12">
<div class="card">

<div class="row" id="table-head">
<div class="col-12">
<div class="card">
<div class="card-content">

<div class="card-body"><h4 class="card-title">Notification Templates <a href="{{ url('notifications/' . 'add') }}" class="btn btn-primary" style="float: right">Add New</a></h4> </div>
<div class="table-responsive">
<table class="table mb-0">
<thead >
<tr>
<th>Event</th>
<th>Title</th>
<th>Options</th>
</tr>
</thead>
<tbody>

@foreach ($templates as $template)
<tr>
    <td>{{ $template->event }}</td>
    <td>{{ $template->title }}</td>
    <td>
        <a class="btn btn-icon btn-info mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Edit" href="{{ url('notifications/' .$template->id.'/edit') }}"><i class="feather icon-edit"></i></a>
        <a type="button" class="btn btn-icon btn-danger mr-1 mb-1 waves-effect waves-light" data-toggle="tooltip" data-placement="top" data-original-title="Delete" onclick="confirmAlert('{{ url('notifications/' .'delete/'.$template->id) }}')"><i class="feather icon-trash-2"></i></a>
    </td>
</tr>
@endforeach

</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

@endsection