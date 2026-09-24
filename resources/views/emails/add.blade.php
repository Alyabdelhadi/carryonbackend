@extends('layout.main')

@section('title') Add New Email Template @endsection

@section('content')

<section id="basic-input">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add New Email Template</h4>
                </div>

                {!! Form::model($data, ['url' => route('emails.store'), 'files' => true]) !!}

                @include('emails.form')

                </form>
            </div>
        </div>
    </div>
</section>

@endsection