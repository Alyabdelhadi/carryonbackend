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

                            <hr>
                            <h4 style="font-weight: bold;">Home screen statistics</h4>
                            <p class="text-muted">Shown above the banner in the app. Leave a field empty to use the live value from the database (updates every 10 minutes).</p>
                            <div class="form-row">
                                @foreach($statDefinitions as $key => $definition)
                                    <div class="form-group col-md-6">
                                        <label for="setting-{{ $key }}">{{ $definition['label'] }}</label>
                                        <input
                                            type="number"
                                            min="0"
                                            class="form-control"
                                            id="setting-{{ $key }}"
                                            name="{{ $key }}"
                                            value="{{ $statValues[$key] }}"
                                            placeholder="Automatic: {{ number_format($statComputed[$definition['field']]) }}"
                                        >
                                    </div>
                                @endforeach
                            </div>

                            <hr>
                            <h4 style="font-weight: bold;">Online payment &amp; wallet</h4>
                            <p class="text-muted">
                                Applies to orders paid by card through Stripe
                                (<a href="{{ Asset('payment-methods') }}">Payment Methods</a>:
                                currently <strong>{{ $stripeEnabled ? 'enabled' : 'disabled' }}</strong>).
                                Cash orders are not affected.
                            </p>
                            <div class="form-row">
                                @foreach($numberDefinitions as $key => $definition)
                                    <div class="form-group col-md-6">
                                        <label for="setting-{{ $key }}">{{ $definition['label'] }}</label>
                                        <input
                                            type="number"
                                            min="{{ $definition['min'] }}"
                                            max="{{ $definition['max'] }}"
                                            step="{{ $definition['step'] }}"
                                            class="form-control"
                                            id="setting-{{ $key }}"
                                            name="{{ $key }}"
                                            value="{{ $numberValues[$key] + 0 }}"
                                        >
                                        <small class="form-text text-muted">{{ $definition['help'] }}</small>
                                    </div>
                                @endforeach
                                <div class="form-group col-md-12">
                                    <label for="setting-payout_methods">Payout methods</label>
                                    <textarea class="form-control" rows="4" id="setting-payout_methods" name="payout_methods">{{ $payoutMethodsRaw }}</textarea>
                                    <small class="form-text text-muted">One per line as <code>code|Label</code>, e.g. <code>omt|OMT</code>. Carriers pick one when requesting a payout; you pay it out by hand from the Payouts page.</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
