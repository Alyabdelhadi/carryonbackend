@extends('layout.main')

@php
    $editing = $group->exists;
    $granted = old('permissions', $group->permissions ?? []);
    $has = fn ($module, $action) => in_array($action, (array) ($granted[$module] ?? []), true);
    $me = auth()->user();
@endphp

@section('title') {{ $editing ? 'Edit group' : 'New group' }} @endsection

@section('content')
<x-admin.page-header :title="$editing ? 'Edit ' . $group->name : 'New group'" subtitle="Tick what admins in this group can do on each page. Any tick also gives View.">
    <a href="{{ Asset('admin-groups') }}" class="btn btn-light co-btn-icon-text"><i class="feather icon-arrow-left"></i> Back</a>
</x-admin.page-header>

<form method="POST" action="{{ $editing ? Asset('admin-groups/'.$group->id) : Asset('admin-groups') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="name">Group name</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $group->name) }}" placeholder="e.g. Support, Finance, Content" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-8">
                    <label for="description">Description</label>
                    <input id="description" name="description" class="form-control" value="{{ old('description', $group->description) }}" placeholder="What this group is for">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="co-toolbar justify-content-between">
            <h4 class="card-title">Permissions</h4>
            <div class="d-flex" style="gap:8px">
                <button type="button" class="btn btn-sm btn-light" data-co-perm-all="view">View all</button>
                <button type="button" class="btn btn-sm btn-light" data-co-perm-all="*">Tick everything</button>
                <button type="button" class="btn btn-sm btn-light" data-co-perm-all="">Clear</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table co-perm-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        @foreach(\App\Support\AdminModules::ACTION_LABELS as $label)
                            <th style="width:110px">{{ $label }}</th>
                        @endforeach
                        <th style="width:90px">All</th>
                    </tr>
                </thead>
                <tbody>
                @foreach(\App\Support\AdminModules::sections() as $section => $modules)
                    <tr class="co-perm-section"><td colspan="6">{{ $section }}</td></tr>
                    @foreach($modules as $key => $module)
                        <tr data-co-perm-row>
                            <td><i class="feather icon-{{ $module['icon'] }} mr-50 text-muted"></i> <strong>{{ $module['label'] }}</strong></td>
                            @foreach(\App\Support\AdminModules::ACTIONS as $action)
                                <td>
                                    @if(in_array($action, $module['actions'], true))
                                        @php $allowed = $me->isSuperAdmin() || $me->canAdmin($key, $action); @endphp
                                        <input type="checkbox" class="co-perm-check" name="permissions[{{ $key }}][]" value="{{ $action }}"
                                            data-action="{{ $action }}" @checked($has($key, $action)) @disabled(!$allowed)
                                            @if(!$allowed) title="You do not have this permission yourself" @endif>
                                    @else
                                        <span class="co-perm-na" title="Not available on this page">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td><input type="checkbox" class="co-perm-check" data-co-perm-rowall aria-label="All for {{ $module['label'] }}"></td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary co-btn-icon-text"><i class="feather icon-check"></i> {{ $editing ? 'Save group' : 'Create group' }}</button>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
(function () {
    var rows = document.querySelectorAll('[data-co-perm-row]');
    function boxes(row) { return row.querySelectorAll('input[data-action]:not(:disabled)'); }
    function sync(row) {
        var all = boxes(row), on = Array.prototype.filter.call(all, function (b) { return b.checked; });
        var rowAll = row.querySelector('[data-co-perm-rowall]');
        rowAll.checked = all.length > 0 && on.length === all.length;
        rowAll.indeterminate = on.length > 0 && on.length < all.length;
    }
    rows.forEach(function (row) {
        row.addEventListener('change', function (e) {
            var box = e.target;
            if (box.matches('[data-co-perm-rowall]')) {
                boxes(row).forEach(function (b) { b.checked = box.checked; });
            } else if (box.checked && box.dataset.action !== 'view') {
                // create / edit / delete need the page to open
                var view = row.querySelector('input[data-action="view"]:not(:disabled)');
                if (view) view.checked = true;
            } else if (!box.checked && box.dataset.action === 'view') {
                boxes(row).forEach(function (b) { b.checked = false; });
            }
            sync(row);
        });
        sync(row);
    });
    document.querySelectorAll('[data-co-perm-all]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mode = btn.dataset.coPermAll;
            rows.forEach(function (row) {
                boxes(row).forEach(function (b) { b.checked = mode === '*' || (mode !== '' && b.dataset.action === mode); });
                sync(row);
            });
        });
    });
})();
</script>
@endsection
