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
<small class="text-muted">Leave empty to show the English name in the app. Bulk fill: php artisan places:translate-ar</small>
</div>
</div>

<div class="form-row">
<div class="form-group col-md-6">
<label for="inputEmail6">Country</label>
{!! Form::select('country_id', $countries, null, ['class' => 'form-control', 'placeholder' => 'Select Country', 'required']) !!}
</div>
</div>

 
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="inputEmail6">Image</label>
        {!! Form::file('image', ['id' => 'code', 'class' => 'form-control', 'accept' => 'image/*']) !!}
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
</div>

</div>
</div>
        
<div class="d-flex justify-content-end border-top pt-2 mt-50">
    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
    </div>

</div>
</div>