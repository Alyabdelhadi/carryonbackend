<div class="card-content">
    <div class="card-body">

        <div class="tab-content">
            <div class="tab-pane active" id="tabs_0" aria-labelledby="home-tab" role="tabpanel">
                
                <p>You can use the following variables: <b>:userfirstname</b> <b>:userfullname</b> <b>:carrierfirstname</b> <b>:carrierfullname</b> <b>:fromcity</b> <b>:tocity</b> <b>:packagetype</b> <b>:packagenumber</b></p>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="event">Event</label>
                        {!! Form::text('event', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        <label for="title">Title</label>
                        {!! Form::text('title', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="body">Body</label>
                        {!! Form::textarea('body', null, ['class' => 'form-control', 'rows' => 5, 'required']) !!}
                    </div>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Save</button>

    </div>
</div>