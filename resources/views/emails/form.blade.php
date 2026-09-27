<div class="card">
    <div class="card-header"><h4 class="card-title">Template</h4></div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="feather icon-info mr-50"></i> Variables you can use: <b>:userfirstname</b> <b>:userfullname</b>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="event">Event</label>
                {!! Form::text('event', null, ['class' => 'form-control', 'id' => 'event', 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                <label for="title">Title</label>
                {!! Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'required']) !!}
            </div>
            <div class="form-group col-md-12 mb-0">
                <label for="body">Body</label>
                {!! Form::textarea('body', null, ['class' => 'form-control', 'id' => 'body', 'rows' => 6, 'required']) !!}
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
    </div>
</div>
