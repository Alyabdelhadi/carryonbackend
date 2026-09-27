<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Account</h4></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Name</label>
                        {!! Form::text('name',null,['id' => 'name','class' => 'form-control','required'])!!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">Phone</label>
                        {!! Form::number('phone',null,['id' => 'phone','class' => 'form-control','required'])!!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        {!! Form::email('email',null,['id' => 'email','class' => 'form-control','required'])!!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="password">Password</label>
                        {!! Form::password('password',['id' => 'password','class' => 'form-control','autocomplete' => 'new-password','placeholder' => isset($data) ? 'Leave empty to keep the current password' : ''] + (isset($data) ? [] : ['required']))!!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="city">City</label>
                        {!! Form::text('city',null,['id' => 'city','class' => 'form-control'])!!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="country">Country</label>
                        {!! Form::text('country',null,['id' => 'country','class' => 'form-control'])!!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Status &amp; photos</h4></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="0" @if($data->status == 0) selected @endif>Inactive</option>
                        <option value="1" @if($data->status == 1) selected @endif>Active</option>
                    </select>
                </div>
                {!! Form::hidden('role', 1) !!}

                <div class="form-group">
                    <label for="selfie">Selfie</label>
                    {!! Form::file('selfie', ['id' => 'selfie', 'class' => 'form-control', 'accept' => 'image/*']) !!}
                </div>

                <div class="form-group mb-0">
                    <label for="identity">Identity</label>
                    {!! Form::file('identity', ['id' => 'identity', 'class' => 'form-control', 'accept' => 'image/*']) !!}
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>
