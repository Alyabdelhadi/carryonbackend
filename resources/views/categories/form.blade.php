<div class="card-content">
<div class="card-body">

<div class="tab-content">

<div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
<div class="form-row">

<div class="form-group col-md-6">
<label for="inputEmail6">Name</label>
{!! Form::text('name',null,['id' => 'code','class' => 'form-control','required'])!!}
</div>

<div class="form-group col-md-6">
<label for="name_ar">Name (Arabic)</label>
{!! Form::text('name_ar',null,['id' => 'name_ar','class' => 'form-control','dir' => 'rtl'])!!}
</div>

<div class="form-group col-md-6">
<label for="inputEmail6">Image</label>
<input type="file" name="img" class="form-control">
</div>

<div class="col-xl-6">
<fieldset class="form-group">
<label for="basicInput">Status</label>
<select name="status" class="form-control">
<option value="0" @if($data->status == 0) selected @endif>Inactive</option>
<option value="1" @if($data->status == 1) selected @endif>Active</option>
</select>
</fieldset>
</div>

<div class="form-group col-md-6">
<label for="inputEmail6">Sort No</label>
<input type="number" name="sort_no" class="form-control" value="{{ $data->sort_no }}">
</div>

<div class="form-group col-md-12">
<label for="inputEmail6">Category Description</label>
<textarea name="text" class="form-control">{{ $data->text }}</textarea>
</div>

</div>
</div>
</div>
<button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Save</button>


</div>
</div>