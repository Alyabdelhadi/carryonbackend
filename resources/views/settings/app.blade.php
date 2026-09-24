@extends('layout.main')

@section('title') App Settings @endsection

@section('content')
<section id="app-settings">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('app-settings.update') }}" method="POST">
                @csrf

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <h1 style="font-weight: bold;">App Settings</h1>
                            <p class="text-muted">Switches the mobile app reads at runtime. Changes apply immediately.</p>

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @foreach($definitions as $key => $definition)
                                <fieldset class="form-group">
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="setting-{{ $key }}"
                                            name="{{ $key }}"
                                            value="1"
                                            {{ $values[$key] ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="setting-{{ $key }}">
                                            <strong>{{ $definition['label'] }}</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">{{ $definition['help'] }}</small>
                                </fieldset>
                            @endforeach

                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
