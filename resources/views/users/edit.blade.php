@extends('layout.main')

@section('title') Edit User @endsection

@php
    $identityExt = strtolower(pathinfo((string) $data->identity, PATHINFO_EXTENSION));
    $identityUrl = Asset('users/'.$data->id.'/identity');
    $deleteText = 'Delete ' . $data->name . '? This permanently removes the user and all their packages, trips, addresses, ratings and wallet history.';
    if ($data->is_verified) {
        $verify = ['chip-success', 'Verified'];
    } elseif ($data->identity_status === 'pending') {
        $verify = ['chip-warning', 'Under review'];
    } elseif (in_array($data->identity_status, ['declined', 'invalid'], true)) {
        $verify = ['chip-danger', ucfirst($data->identity_status)];
    } else {
        $verify = ['', 'Not verified'];
    }
@endphp

@section('content')

<x-admin.page-header :title="'Edit ' . ($data->name ?? 'user')" subtitle="Update the app account's details, status and photos.">
    <a href="{{ Asset('users') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
    @can('users.delete')
        <button type="button" class="btn btn-danger co-btn-icon-text" onclick="confirmAlert('{{ Asset('users/delete/'.$data->id) }}', {{ json_encode($deleteText) }})"><i class="feather icon-trash-2"></i> Delete user</button>
    @endcan
</x-admin.page-header>

{!! Form::model($data, ['url' => [$form_url],'files' => true,'method' => 'PATCH']) !!}

@include('users.form')

</form>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Photos</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-2 mb-sm-0">
                        <label class="d-block">Selfie</label>
                        @if(!empty($data->selfie))
                            <a href="{{ Asset('upload/selfies/'.$data->selfie) }}" data-lightbox="user-{{ $data->id }}-photos" data-title="Selfie">
                                <img src="{{ Asset('upload/selfies/'.$data->selfie) }}" alt="Selfie" class="w-100" style="max-height:280px;object-fit:cover;border-radius:12px;border:1px solid var(--co-border)">
                            </a>
                        @else
                            <x-admin.empty icon="camera" title="No selfie" />
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="d-block">Identity document</label>
                        @if(!empty($data->identity))
                            @if($identityExt === 'pdf')
                                <a href="{{ $identityUrl }}" target="_blank" class="btn btn-light co-btn-icon-text"><i class="feather icon-file-text text-danger"></i> Open PDF</a>
                            @else
                                <a href="{{ $identityUrl }}" data-lightbox="user-{{ $data->id }}-photos" data-title="Identity document">
                                    <img src="{{ $identityUrl }}" alt="Identity document" class="w-100" style="max-height:280px;object-fit:cover;border-radius:12px;border:1px solid var(--co-border)">
                                </a>
                            @endif
                        @else
                            <x-admin.empty icon="credit-card" title="No identity document" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Identity verification</h4></div>
            <div class="card-body">
                <span class="chip {{ $verify[0] }}"><span class="chip-body"><span class="chip-text">{{ $verify[1] }}</span></span></span>
                @if($data->identity_verified_at)
                    <div class="small text-muted mt-50">Verified {{ \Carbon\Carbon::parse($data->identity_verified_at)->format('Y-m-d H:i') }}</div>
                @endif

                @can('users.edit')
                    <div class="d-flex flex-wrap mt-2" style="gap:8px">
                        @if($data->identity_status === 'pending')
                            <a class="btn btn-success" href="{{ Asset('userVerification?id='.$data->id.'&action=approve') }}" data-co-go="Approve this user's identity? Check that the selfie matches the ID first." data-co-go-label="Approve">Approve</a>
                            <a class="btn btn-outline-danger" href="{{ Asset('userVerification?id='.$data->id.'&action=reject') }}" data-co-go="Reject? The user will be asked to upload new photos." data-co-go-label="Reject">Reject</a>
                        @elseif($data->is_verified)
                            <a class="btn btn-outline-danger" href="{{ Asset('userVerification?id='.$data->id.'&action=revoke') }}" data-co-go="Remove verification? The user will have to verify again in the app." data-co-go-label="Remove">Remove verification</a>
                        @else
                            <a class="btn btn-success" href="{{ Asset('userVerification?id='.$data->id.'&action=approve') }}" data-co-go="Mark this user as verified?" data-co-go-label="Mark verified">Mark as verified</a>
                        @endif
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>

@endsection
