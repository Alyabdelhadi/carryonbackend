@extends('layout.main')

@section('title') Add App Text @endsection

@section('content')
<x-admin.page-header title="Add App Text" subtitle="Every label and message the app shows. Changes reach the app the next time it loads its texts.">
</x-admin.page-header>

<div class="card">
{!! Form::open(['url' => [Asset('texts')],'files' => true]) !!}

@include('texts.form')

</form>
</div>
@endsection
