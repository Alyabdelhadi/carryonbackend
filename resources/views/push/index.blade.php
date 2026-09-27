@extends('layout.main')

@section('title') Push Notifications @endsection

@section('content')
<x-admin.page-header title="Push notification" subtitle="Sends a message to every app user subscribed to the CarryOn topic." />

<div class="row">
    <div class="col-lg-8">
        @can('push.create')
            {!! Form::open(['url' => [Asset('send')],'files' => true,'method' => 'POST']) !!}
            <div class="card">
                <div class="card-header"><h4 class="card-title">New message</h4></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="code">Title</label>
                        {!! Form::text('title',null,['id' => 'code','class' => 'form-control','required'])!!}
                    </div>
                    <div class="form-group mb-0">
                        <label for="push-text">Message</label>
                        <textarea id="push-text" name="text" class="form-control" rows="4" required="required"></textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-send"></i> Send to everyone</button>
                </div>
            </div>
            </form>
        @else
            <div class="card">
                <x-admin.empty icon="lock" title="Sending is not allowed" message="Your group can view this page but cannot send push notifications." />
            </div>
        @endcan
    </div>
</div>
@endsection
