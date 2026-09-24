<div class="modal fade text-left" id="assign{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
<div class="modal-content">
<div class="modal-header bg-primary">
<h4 class="modal-title" id="myModalLabel33">Assign Delivery Partner</h4>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<form action="{{ Asset('parcelAssign') }}" method="post" enctype="multipart/form-data">
<div class="modal-body">

{{ csrf_field() }}

<input type="hidden" name="id" value="{{ $row->id }}">

<div class="row">
<div class="form-group col-md-8">
<label for="inputEmail6">Select Delivery Partner</label>
<select name="dboy" class="form-control" required="required">
<option value="">Select</option>
@foreach($dboy as $d)
<option value="{{ $d->id }}">{{ $d->name }}</option>
@endforeach
</select>
</div>

<div class="form-group col-md-4">
<label for="inputEmail6">&nbsp;</label>
<button type="submit" class="btn btn-primary" style="margin-top: 20px">Assign</button>
</div>

</div>
<div class="modal-footer">
</div>
</form>
</div>
</div>
</div>
</form>
</div>