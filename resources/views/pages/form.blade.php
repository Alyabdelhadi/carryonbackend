<div class="card-content">
    <div class="card-body">
    
    <div class="tab-content">
    
    <div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
    <div class="form-row">
    
    
    <div class="form-group col-md-12">
    <label for="inputEmail6">Title</label>
    {!! Form::text('title',null,['id' => 'title','class' => 'form-control','required'])!!}
    </div>

    <div class="form-group col-md-12">
    <label for="inputEmail6">Content</label>
    <textarea id="content" name="content" class="form-control" required>{{ $data->content }}</textarea>
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
    <div class="d-flex justify-content-end border-top pt-2 mt-50">
    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
    </div>
    
    
    </div>
    </div>