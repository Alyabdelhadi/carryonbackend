<div class="row">
<div class="{{ $data->code === 'stripe' ? 'col-lg-5' : 'col-lg-8' }}">
    <div class="card">
        <div class="card-header"><h4 class="card-title">General</h4></div>
        <div class="card-body">
            <div class="form-group">
                <label>Payment Method</label>
                <input type="text" class="form-control" value="{{ $data->name }}" disabled>
            </div>

            <div class="form-group">
                <label for="enabled">Status</label>
                <select name="enabled" id="enabled" class="form-control" required>
                    <option value="0" @if($data->enabled == 0) selected @endif>Disabled</option>
                    <option value="1" @if($data->enabled == 1) selected @endif>Enabled</option>
                </select>
            </div>

            <div class="form-group mb-0">
                <label for="currency">Currency</label>
                <select name="currency" id="currency" class="form-control" required>
                    <option value="USD" @if(strtoupper($data->currency) == 'USD') selected @endif>USD</option>
                    <option value="EUR" @if(strtoupper($data->currency) == 'EUR') selected @endif>EUR</option>
                </select>
            </div>
        </div>
        @if($data->code !== 'stripe')
        <div class="card-footer d-flex justify-content-end" style="gap:8px">
            <a href="{{ Asset($link) }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
        </div>
        @endif
    </div>
</div>

@if($data->code === 'stripe')
<div class="col-lg-7">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Stripe Configuration</h4>
            <p class="text-muted mb-0 mt-25">Configure the Stripe API credentials used for online payments.</p>
        </div>
        <div class="card-body">
            {{-- Publishable Key --}}
            <div class="form-group">
                <label for="publishable_key">Publishable Key</label>
                <input type="text" id="publishable_key" name="publishable_key" class="form-control" value="{{ $data->publishable_key }}" placeholder="pk_test_... or pk_live_..." autocomplete="off">
                <small class="form-text">This key can be used by the mobile application.</small>
            </div>

            {{-- Secret Key --}}
            <div class="form-group">
                <label for="secret_key">Secret Key</label>
                <input type="password" id="secret_key" name="secret_key" class="form-control" value=""
                    placeholder="@if(!empty($data->secret_key)) Configured - leave blank to keep current key @else sk_test_... or sk_live_... @endif"
                    autocomplete="new-password">
                @if(!empty($data->secret_key))
                    <small class="form-text text-success"><i class="feather icon-check-circle"></i> Secret key is configured. Leave this field blank to keep the existing key.</small>
                @else
                    <small class="form-text text-danger">Secret key is not configured.</small>
                @endif
            </div>

            {{-- Webhook Secret --}}
            <div class="form-group">
                <label for="webhook_secret">Webhook Secret</label>
                <input type="password" id="webhook_secret" name="webhook_secret" class="form-control" value=""
                    placeholder="@if(!empty($data->webhook_secret)) Configured - leave blank to keep current secret @else whsec_... @endif"
                    autocomplete="new-password">
                @if(!empty($data->webhook_secret))
                    <small class="form-text text-success"><i class="feather icon-check-circle"></i> Webhook secret is configured. Leave this field blank to keep the existing secret.</small>
                @else
                    <small class="form-text text-danger">Webhook secret is not configured.</small>
                @endif
            </div>

            {{-- Webhook URL --}}
            <div class="form-group mb-0">
                <label for="stripeWebhookUrl">Webhook URL</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ url('/api/payments/stripe/webhook') }}" id="stripeWebhookUrl" readonly>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary" onclick="copyStripeWebhook()">Copy</button>
                    </div>
                </div>
                <small class="form-text">Add this URL as the webhook endpoint in your Stripe account.</small>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end" style="gap:8px">
            <a href="{{ Asset($link) }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Save</button>
        </div>
    </div>
</div>

<script>
function copyStripeWebhook()
{
    var input = document.getElementById('stripeWebhookUrl');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
}
</script>
@endif
</div>
