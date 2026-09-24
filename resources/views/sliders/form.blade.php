<div class="card-content">
    <div class="card-body">
    
    <div class="tab-content">
    
    <div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
    <div class="form-row">
    
    
    <div class="form-group col-md-6">
    <label for="inputEmail6">Image</label>
    <input type="file" name="img" class="form-control">
    </div>

    <div class="form-group col-md-6">
    <label for="img_ar">Image (Arabic)</label>
    <input type="file" name="img_ar" class="form-control">
    <small class="text-muted">Shown instead of the image above when the app language is Arabic.</small>
    @if($data->img_ar)
    <div class="mt-1"><img src="{{ Asset('upload/sliders/'.$data->img_ar) }}" height="60px"> <label class="ml-2"><input type="checkbox" name="remove_img_ar" value="1"> Remove Arabic image</label></div>
    @endif
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