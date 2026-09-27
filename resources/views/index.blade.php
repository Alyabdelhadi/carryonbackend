<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in · CarryOn Admin</title>
<link rel="icon" type="image/png" href="{{ Asset('assets/admin/logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ Asset('app-assets/vendors/css/vendors.min.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/css/bootstrap.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/css/bootstrap-extended.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/css/components.css') }}">
<link rel="stylesheet" href="{{ Asset('assets/admin/carryon.css') }}?v={{ @filemtime(base_path('assets/admin/carryon.css')) }}">
</head>
<body class="co-body">
<div class="co-auth">
    <section class="co-auth-art">
        <div class="d-flex align-items-center" style="gap:12px">
            <img src="{{ Asset('assets/admin/logo.png') }}" alt="" width="44" height="44" style="border-radius:12px">
            <span class="co-brand-text">CarryOn<small>Admin</small></span>
        </div>
        <div>
            <h2>Parcels that travel <span>with people</span>.</h2>
            <p>Manage orders, carriers, trips, payouts and everything the CarryOn app shows, in one place.</p>
        </div>
        <div class="co-auth-stats">
            <div><strong>P2P</strong><span>Delivery network</span></div>
            <div><strong>CO₂</strong><span>Saved on every trip</span></div>
        </div>
    </section>

    <section class="co-auth-form">
        <div class="co-auth-card">
            <h1>Welcome back</h1>
            <p class="text-muted mb-3">Sign in to the CarryOn dashboard.</p>

            @if(Session::has('error'))
                <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif
            @if(Session::has('message'))
                <div class="alert alert-success">{{ Session::get('message') }}</div>
            @endif

            <form action="{{ $form_url }}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="user-name">Username</label>
                    <input type="text" class="form-control" id="user-name" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="user-password">Password</label>
                    <input type="password" class="form-control" id="user-password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">Sign in</button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
