@extends('layout.main')

@section('title') Push Notifications @endsection

@section('content')

<section id="basic-input">
<div class="row">
<div class="col-md-8">
<div class="card">
<div class="card-header">
<h4 class="card-title">Push Notifications</h4>
</div>

{!! Form::open(['url' => [Asset('send')],'files' => true,'method' => 'POST']) !!}

<div class="card-content">
<div class="card-body">

<div class="form-row">
<div class="form-group col-md-12">
<label for="inputEmail6">Title</label>
{!! Form::text('title',null,['id' => 'code','class' => 'form-control','required'])!!}
</div>

<div class="form-group col-md-12">
<label for="inputEmail6">Description</label>
<textarea name="text" class="form-control" required="required"></textarea>
</div>
</div>


<button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Send</button>


</div>
</div>

</form>
</div>
</div>
</div>
</section>

@endsection