<div class="card-content">
    <div class="card-body">
    
    <div class="tab-content">
    
    <div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
    <div class="form-row">
    
    
        <div class="form-group col-md-6">
        <label for="inputEmail6">Reward amount</label>
        {!! Form::text('value',null,['id' => 'code','class' => 'form-control','required','inputmode' => 'decimal','placeholder' => 'e.g. 10 (0 = Free)'])!!}
        <small class="text-muted">Shown as a reward chip on the app's order form. 0 shows as "Free"; the app always adds "Other".</small>
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