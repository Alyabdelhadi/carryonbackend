@extends('layout.main')

@section('title') App Settings @endsection

@php $canEdit = auth()->user()->can('app_settings.edit'); @endphp

@section('content')
<x-admin.page-header title="App settings" subtitle="Switches the mobile app reads at runtime. Changes apply immediately." />

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('app-settings.update') }}" method="POST">
    @csrf
    <fieldset @disabled(!$canEdit)>
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-shield mr-50"></i> Identity verification</h4></div>
                <div class="card-body">
                    @foreach($definitions as $key => $definition)
                        <div class="form-group">
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input type="checkbox" class="custom-control-input" id="setting-{{ $key }}" name="{{ $key }}" value="1" {{ $values[$key] ? 'checked' : '' }}>
                                <label class="custom-control-label" for="setting-{{ $key }}"><strong>{{ $definition['label'] }}</strong></label>
                            </div>
                            <small class="form-text text-muted">{{ $definition['help'] }}</small>
                        </div>
                    @endforeach

                    @foreach($choiceDefinitions as $key => $definition)
                        <div class="form-group mb-0">
                            <label>{{ $definition['label'] }}</label>
                            @foreach($definition['options'] as $option => $optionLabel)
                                <div class="custom-control custom-radio mb-1">
                                    <input type="radio" class="custom-control-input" id="setting-{{ $key }}-{{ $option }}" name="{{ $key }}" value="{{ $option }}" {{ $choiceValues[$key] === $option ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="setting-{{ $key }}-{{ $option }}">{{ $optionLabel }}</label>
                                </div>
                            @endforeach
                            <small class="form-text text-muted">{{ $definition['help'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-bar-chart-2 mr-50"></i> Home screen statistics</h4></div>
                <div class="card-body">
                    <p class="text-muted">Shown above the banner in the app. Leave a field empty to use the live value from the database (updates every 10 minutes).</p>
                    <div class="form-row">
                        @foreach($statDefinitions as $key => $definition)
                            <div class="form-group col-md-6">
                                <label for="setting-{{ $key }}">{{ $definition['label'] }}</label>
                                <input type="number" min="0" class="form-control" id="setting-{{ $key }}" name="{{ $key }}" value="{{ $statValues[$key] }}" placeholder="Automatic: {{ number_format($statComputed[$definition['field']]) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title"><i class="feather icon-credit-card mr-50"></i> Online payment &amp; wallet</h4></div>
                <div class="card-body">
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
                                <input type="number" min="{{ $definition['min'] }}" max="{{ $definition['max'] }}" step="{{ $definition['step'] }}" class="form-control" id="setting-{{ $key }}" name="{{ $key }}" value="{{ $numberValues[$key] + 0 }}">
                                <small class="form-text text-muted">{{ $definition['help'] }}</small>
                            </div>
                        @endforeach
                        <div class="form-group col-md-12 mb-0">
                            <label for="setting-payout_methods">Payout methods</label>
                            <textarea class="form-control" rows="4" id="setting-payout_methods" name="payout_methods">{{ $payoutMethodsRaw }}</textarea>
                            <small class="form-text text-muted">One per line as <code>code|Label</code>, e.g. <code>omt|OMT</code>. Carriers pick one when requesting a payout; you pay it out by hand from the Payouts page.</small>
                        </div>
                    </div>
                </div>
                @if($canEdit)
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save settings</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </fieldset>
</form>
@endsection
