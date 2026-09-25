<div class="card-content">
    <div class="card-body">
    
    <div class="tab-content">
    
    <div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
    <div class="form-row">
    
    
        <div class="form-group col-md-6">
        <label for="inputEmail6">Weight (kg)</label>
        {!! Form::text('value',null,['id' => 'code','class' => 'form-control','required','inputmode' => 'decimal','placeholder' => 'e.g. 0.5, 2, 10'])!!}
        </div>

        <div class="form-group col-md-6">
        <label>Show in the app</label>
        <div class="custom-control custom-checkbox">
        <input type="hidden" name="in_order_form" value="0">
        <input type="checkbox" class="custom-control-input" id="in_order_form" name="in_order_form" value="1" @if(!isset($data->id) || $data->in_order_form) checked @endif>
        <label class="custom-control-label" for="in_order_form">Order form (send / receive a package)</label>
        </div>
        <div class="custom-control custom-checkbox">
        <input type="hidden" name="in_calculator" value="0">
        <input type="checkbox" class="custom-control-input" id="in_calculator" name="in_calculator" value="1" @if(!isset($data->id) || $data->in_calculator) checked @endif>
        <label class="custom-control-label" for="in_calculator">Carbon calculator</label>
        </div>
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
    
    </div>
    </div>
    </div>
    <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Save</button>
    
    
    </div>
    </div>