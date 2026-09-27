@extends('layout.main')

@section('title') Edit Package #{{ $order->id }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .select2-container--default .select2-selection--single { min-height: 42px; border: 1px solid var(--co-border-strong); border-radius: var(--co-radius-sm); }
  .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: 13px; }
  .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
  .select2-dropdown { border-color: var(--co-border-strong); border-radius: var(--co-radius-sm); }
</style>
@endsection

@section('content')

<x-admin.page-header :title="'Edit Package #' . $order->id" subtitle="Change the order details, status, carrier or payment.">
  <a href="{{ Asset('parcel_order_view?id='.$order->id) }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-eye"></i> View</a>
  <a href="{{ Asset('parcel_order') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="row">
  <div class="col-xl-8">
      <form method="POST" action="{{ route('parcel.update', $order->id) }}">
        @csrf
        @method('PUT')

        <div class="card">
        <div class="card-header"><h4 class="card-title"><i class="feather icon-user text-muted mr-50"></i> Owner &amp; category</h4></div>
        <div class="card-body">
        <div class="form-row">

          <!-- User Dropdown -->
          <div class="form-group col-md-6">
            <label for="user_id">User</label>
            <select class="form-control select2" id="user_id" name="user_id" required>
              <option value="">Select a user</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>
                  {{ $user->name }} (ID: {{ $user->id }})
                </option>
              @endforeach
            </select>
          </div>

          <!-- Category Dropdown -->
          <div class="form-group col-md-6">
            <label for="cate_id">Category</label>
            <select class="form-control select2" id="cate_id" name="cate_id" required>
              <option value="">Select a category</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('cate_id', $order->cate_id) == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
        </div>
        </div>

        <div class="card">
        <div class="card-header"><h4 class="card-title"><i class="feather icon-send text-muted mr-50"></i> Sender</h4></div>
        <div class="card-body">
        <div class="form-row">

          <div class="form-group col-md-6">
            <label for="s_name">Name</label>
            <input type="text" class="form-control" id="s_name" name="s_name" value="{{ old('s_name', $order->s_name) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_phone">Phone</label>
            <input type="text" class="form-control" id="s_phone" name="s_phone" value="{{ old('s_phone', $order->s_phone) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_address_name">Address Name</label>
            <input type="text" class="form-control" name="s_address[name]" value="{{ old('s_address.name', $order->s_addressname) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_city">City</label>
            <input type="text" class="form-control" name="s_address[city]" value="{{ old('s_address.city', $order->s_city) }}">
          </div>

          <div class="form-group col-md-6">
            <label for="s_country">Country</label>
            <input type="text" class="form-control" name="s_address[country]" value="{{ old('s_address.country', $order->s_country) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_lat">Latitude</label>
            <input type="text" class="form-control" name="s_address[lat]" value="{{ old('s_address.lat', $order->s_lat) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_lng">Longitude</label>
            <input type="text" class="form-control" name="s_address[lng]" value="{{ old('s_address.lng', $order->s_lng) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_street">Street</label>
            <input type="text" class="form-control" name="s_address[street]" value="{{ old('s_address.street', $order->s_street) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="s_building">Building</label>
            <input type="text" class="form-control" name="s_address[building]" value="{{ old('s_address.building', $order->s_building) }}">
          </div>

          <div class="form-group col-md-6">
            <label for="s_apartment">Apartment</label>
            <input type="text" class="form-control" name="s_address[apartment]" value="{{ old('s_address.apartment', $order->s_apartment) }}">
          </div>

        </div>
        </div>
        </div>

        <div class="card">
        <div class="card-header"><h4 class="card-title"><i class="feather icon-inbox text-muted mr-50"></i> Receiver</h4></div>
        <div class="card-body">
        <div class="form-row">

          <div class="form-group col-md-6">
            <label for="r_name">Name</label>
            <input type="text" class="form-control" id="r_name" name="r_name" value="{{ old('r_name', $order->r_name) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_phone">Phone</label>
            <input type="text" class="form-control" id="r_phone" name="r_phone" value="{{ old('r_phone', $order->r_phone) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_address_name">Address Name</label>
            <input type="text" class="form-control" name="r_address[name]" value="{{ old('r_address.name', $order->r_addressname) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_city">City</label>
            <input type="text" class="form-control" name="r_address[city]" value="{{ old('r_address.city', $order->r_city) }}">
          </div>

          <div class="form-group col-md-6">
            <label for="r_country">Country</label>
            <input type="text" class="form-control" name="r_address[country]" value="{{ old('r_address.country', $order->r_country) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_lat">Latitude</label>
            <input type="text" class="form-control" name="r_address[lat]" value="{{ old('r_address.lat', $order->r_lat) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_lng">Longitude</label>
            <input type="text" class="form-control" name="r_address[lng]" value="{{ old('r_address.lng', $order->r_lng) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_street">Street</label>
            <input type="text" class="form-control" name="r_address[street]" value="{{ old('r_address.street', $order->r_street) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="r_building">Building</label>
            <input type="text" class="form-control" name="r_address[building]" value="{{ old('r_address.building', $order->r_building) }}">
          </div>

          <div class="form-group col-md-6">
            <label for="r_apartment">Apartment</label>
            <input type="text" class="form-control" name="r_address[apartment]" value="{{ old('r_address.apartment', $order->r_apartment) }}">
          </div>


        </div>
        </div>
        </div>

        <div class="card">
        <div class="card-header"><h4 class="card-title"><i class="feather icon-package text-muted mr-50"></i> Status, carrier &amp; package</h4></div>
        <div class="card-body">
        <div class="form-row">
            <!-- Status -->
            <div class="form-group col-md-6">
              <label for="status">Status</label>
              <select class="form-control" id="status" name="status" required>
                @foreach(['Unassigned', 'Assigned', 'Picked', 'Delivered', 'Cancelled', 'Expired'] as $status)
                  <option value="{{ $status }}" {{ old('status', $order->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
              </select>
            </div>
            
            <!-- Carrier -->
            <div class="form-group col-md-6" id="carrier-wrapper">
              <label for="carrier_id">Carrier</label>
              <select class="form-control select2" id="carrier_id" name="carrier_id">
                <option value="">Select Carrier</option>
                @foreach($carriers as $carrier)
                  <option value="{{ $carrier->id }}" {{ old('carrier_id', $order->carrier_id) == $carrier->id ? 'selected' : '' }}>
                    {{ $carrier->name }} (ID: {{ $carrier->id }})
                  </option>
                @endforeach
              </select>
              <small id="carrier-required-msg" class="text-danger d-none">Carrier is required for this status.</small>
            </div>

          <div class="form-group col-md-6">
            <label for="amount">Value</label>
            <input type="text" class="form-control" name="value" value="{{ old('value', $order->value) }}" required>
          </div>
          
          <div class="form-group col-md-6">
            <label for="amount">Reward</label>
            <input type="text" class="form-control" name="amount" value="{{ old('amount', $order->amount) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="weight">Weight</label>
            <input type="text" class="form-control" name="weight" value="{{ old('weight', $order->weight) }}" required>
          </div>

          <div class="form-group col-md-6">
            <label for="date_to">Order Date</label>
            <input type="date" class="form-control" name="date_to" value="{{ old('date_to', $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : '') }}">
          </div>

          <div class="form-group col-md-12">
            <label for="notes">Notes</label>
            <textarea class="form-control" name="notes">{{ old('notes', $order->notes) }}</textarea>
          </div>

          <div class="form-group col-md-12">
            <label for="description">Description</label>
            <textarea class="form-control" name="description">{{ old('description', $order->description) }}</textarea>
          </div>

        </div>

        </div>
        <div class="card-footer d-flex justify-content-end">
          <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> Update Package</button>
        </div>
        </div>
      </form>
  </div>

  <div class="col-xl-4">
    <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="feather icon-credit-card text-muted mr-50"></i> Payment</h4></div>
      <div class="card-body">
      <div class="row">
        <div class="col-6 mb-1"><small class="text-muted">Method</small><br><strong>{{ $order->isOnlinePayment() ? 'Card (Stripe)' : 'Cash on delivery' }}</strong></div>
        <div class="col-6 mb-1"><small class="text-muted">Status</small><br><strong>{{ $order->paymentLabel() }}</strong></div>
        @if($order->isOnlinePayment())
          <div class="col-6 mb-1"><small class="text-muted">Charged</small><br><strong>{{ number_format($order->payment_amount, 2) }} {{ $order->payment_currency }}</strong></div>
          <div class="col-6 mb-1"><small class="text-muted">Commission / carrier</small><br><strong>{{ number_format($order->commission_amount, 2) }} / {{ number_format($order->carrier_earning, 2) }}</strong></div>
          <div class="col-md-12 mt-2">
            <small class="text-muted">
              @if($order->payment_reference) Stripe: {{ $order->payment_reference }} @endif
              @if($order->paid_at) · paid {{ \Carbon\Carbon::parse($order->paid_at)->format('Y-m-d H:i') }} @endif
              @if($order->payment_deadline_at) · pay before {{ \Carbon\Carbon::parse($order->payment_deadline_at)->format('Y-m-d H:i') }} @endif
              @if($order->refund_reference) · refund {{ $order->refund_reference }} on {{ \Carbon\Carbon::parse($order->refunded_at)->format('Y-m-d H:i') }} @endif
            </small>
          </div>
          @if(in_array($order->payment_status, ['paid', 'refund_pending']))
            <div class="col-md-12 mt-3">
              @can('orders.edit')
              <form method="POST" action="{{ route('parcel.refund', $order->id) }}" data-co-confirm="Refund {{ number_format($order->payment_amount, 2) }} {{ $order->payment_currency }} to the sender through Stripe?">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Refund sender</button>
                @if($order->status === 'Delivered')
                  <small class="text-muted ml-2">The carrier's wallet was already credited; adjust it from the Wallets page if needed.</small>
                @endif
              </form>
              @endcan
            </div>
          @endif
        @endif
      </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function () {
    $('.select2').select2({
      width: '100%',
      placeholder: 'Select an option',
      allowClear: true
    });

    // Show/hide carrier requirement
    function toggleCarrierRequirement() {
      const status = $('#status').val();
      const carrierWrapper = $('#carrier-wrapper');
      const carrierInput = $('#carrier_id');
      const carrierMsg = $('#carrier-required-msg');

      if (status !== 'Unassigned' && status !== 'Cancelled' && status !== 'Expired') {
        carrierWrapper.show();
        carrierInput.prop('required', true);
        if (!carrierInput.val()) {
          carrierMsg.removeClass('d-none');
        }
      } else {
        carrierInput.prop('required', false);
        carrierMsg.addClass('d-none');
      }
    }

    // Initial call
    toggleCarrierRequirement();

    // On status change
    $('#status').on('change', function () {
      toggleCarrierRequirement();
    });
  });
</script>
@endsection
