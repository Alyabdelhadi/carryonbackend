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
<label for="inputEmail6">Phone</label>
{!! Form::number('phone',null,['id' => 'code','class' => 'form-control','required'])!!}
</div>
</div>


<div class="form-row">
<div class="form-group col-md-6">
<label for="inputEmail6">Email</label>
{!! Form::email('email',null,['id' => 'email','class' => 'form-control','required'])!!}
</div>
<div class="form-group col-md-6">
<label for="inputEmail6">Password</label>
{!! Form::password('password',['id' => 'password','class' => 'form-control','autocomplete' => 'new-password','placeholder' => isset($data) ? 'Leave empty to keep the current password' : ''] + (isset($data) ? [] : ['required']))!!}
</div>

</div>


<div class="form-row">
<div class="form-group col-md-6">
<label for="inputEmail6">City</label>
{!! Form::text('city',null,['id' => 'code','class' => 'form-control'])!!}
</div>
<div class="form-group col-md-6">
<label for="inputEmail6">Country</label>
{!! Form::text('country',null,['id' => 'code','class' => 'form-control'])!!}
</div>
</div>

<div class="form-row">
<div class="form-group col-md-6">
<label for="basicInput">Status</label>
<select name="status" class="form-control">
<option value="0" @if($data->status == 0) selected @endif>Inactive</option>
<option value="1" @if($data->status == 1) selected @endif>Active</option>
</select>
</div>
{!! Form::hidden('role', 1) !!}
</div>

 <div class="form-row">
    <div class="form-group col-md-6">
        <label for="inputEmail6">Selfie</label>
        {!! Form::file('selfie', ['id' => 'code', 'class' => 'form-control', 'accept' => 'image/*']) !!}
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="identity">Identity</label>
        {!! Form::file('identity', ['id' => 'code', 'class' => 'form-control', 'accept' => 'image/*']) !!}
    </div>
</div>

</div>
</div>
<button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Save</button>


</div>
</div>