<div class="card-content">

<div class="card-body">

<div class="tab-content">

<div
    class="tab-pane active"
    id="tabs_0"
    role="tabpanel"
>

<div class="form-row">


    {{-- Payment Method Name --}}
    <div class="form-group col-md-6">

        <label>Payment Method</label>

        <input
            type="text"
            class="form-control"
            value="{{ $data->name }}"
            disabled
        >

    </div>


    {{-- Status --}}
    <div class="form-group col-md-6">

        <label>Status</label>

        <select
            name="enabled"
            class="form-control"
            required
        >

            <option
                value="0"
                @if($data->enabled == 0)
                    selected
                @endif
            >
                Disabled
            </option>

            <option
                value="1"
                @if($data->enabled == 1)
                    selected
                @endif
            >
                Enabled
            </option>

        </select>

    </div>


    {{-- Currency --}}
    <div class="form-group col-md-6">

        <label>Currency</label>

        <select
            name="currency"
            class="form-control"
            required
        >

            <option
                value="USD"
                @if(strtoupper($data->currency) == 'USD')
                    selected
                @endif
            >
                USD
            </option>

            <option
                value="EUR"
                @if(strtoupper($data->currency) == 'EUR')
                    selected
                @endif
            >
                EUR
            </option>

        </select>

    </div>


    @if($data->code === 'stripe')


        <div class="col-md-12">

            <hr>

            <h5 class="mb-2">
                Stripe Configuration
            </h5>

            <p class="text-muted">
                Configure the Stripe API credentials used for online payments.
            </p>

        </div>


        {{-- Publishable Key --}}
        <div class="form-group col-md-12">

            <label>Publishable Key</label>

            <input
                type="text"
                name="publishable_key"
                class="form-control"
                value="{{ $data->publishable_key }}"
                placeholder="pk_test_... or pk_live_..."
                autocomplete="off"
            >

            <small class="text-muted">
                This key can be used by the mobile application.
            </small>

        </div>


        {{-- Secret Key --}}
        <div class="form-group col-md-12">

            <label>Secret Key</label>

            <input
                type="password"
                name="secret_key"
                class="form-control"
                value=""
                placeholder="@if(!empty($data->secret_key)) Configured - leave blank to keep current key @else sk_test_... or sk_live_... @endif"
                autocomplete="new-password"
            >

            @if(!empty($data->secret_key))

                <small class="text-success">
                    <i class="feather icon-check-circle"></i>
                    Secret key is configured.
                    Leave this field blank to keep the existing key.
                </small>

            @else

                <small class="text-danger">
                    Secret key is not configured.
                </small>

            @endif

        </div>


        {{-- Webhook Secret --}}
        <div class="form-group col-md-12">

            <label>Webhook Secret</label>

            <input
                type="password"
                name="webhook_secret"
                class="form-control"
                value=""
                placeholder="@if(!empty($data->webhook_secret)) Configured - leave blank to keep current secret @else whsec_... @endif"
                autocomplete="new-password"
            >

            @if(!empty($data->webhook_secret))

                <small class="text-success">
                    <i class="feather icon-check-circle"></i>
                    Webhook secret is configured.
                    Leave this field blank to keep the existing secret.
                </small>

            @else

                <small class="text-danger">
                    Webhook secret is not configured.
                </small>

            @endif

        </div>


        {{-- Webhook URL --}}
        <div class="form-group col-md-12">

            <label>Webhook URL</label>

            <div class="input-group">

                <input
                    type="text"
                    class="form-control"
                    value="{{ url('/api/payments/stripe/webhook') }}"
                    id="stripeWebhookUrl"
                    readonly
                >

                <div class="input-group-append">

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        onclick="copyStripeWebhook()"
                    >
                        Copy
                    </button>

                </div>

            </div>

            <small class="text-muted">
                Add this URL as the webhook endpoint in your Stripe account.
            </small>

        </div>


    @endif


</div>

</div>

</div>


<button
    type="submit"
    class="btn btn-primary mr-1 mb-1 waves-effect waves-light"
>
    Save
</button>


<a
    href="{{ Asset($link) }}"
    class="btn btn-outline-secondary mr-1 mb-1"
>
    Cancel
</a>


</div>
</div>


@if($data->code === 'stripe')

<script>

function copyStripeWebhook()
{
    var input =
        document.getElementById(
            'stripeWebhookUrl'
        );

    input.select();

    input.setSelectionRange(
        0,
        99999
    );

    document.execCommand('copy');
}

</script>

@endif