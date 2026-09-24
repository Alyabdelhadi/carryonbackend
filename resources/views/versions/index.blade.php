@extends('layout.main')

@section('title') App Versions @endsection

@section('content')
<section id="app-versions">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('update') }}" method="POST">
                @csrf
                {{-- Using POST, no @method('PUT') needed --}}

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <h1 style="font-weight: bold;">App Versions</h1>

                            {{-- Success Message --}}
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            {{-- Validation Errors --}}
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-xl-6">
                                    <fieldset class="form-group">
                                        <label for="versionIos">iOS Version</label>
                                        <input
                                            type="text"
                                            id="versionIos"
                                            name="ios"
                                            class="form-control"
                                            value="{{ old('ios', $version->ios ?? '') }}"
                                            required
                                            placeholder="e.g. 1.2.3"
                                        >
                                    </fieldset>
                                </div>

                                <div class="col-xl-6">
                                    <fieldset class="form-group">
                                        <label for="versionAndroid">Android Version</label>
                                        <input
                                            type="text"
                                            id="versionAndroid"
                                            name="android"
                                            class="form-control"
                                            value="{{ old('android', $version->android ?? '') }}"
                                            required
                                            placeholder="e.g. 2.3.4"
                                        >
                                    </fieldset>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-2">
                                Save Versions
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>
@endsection