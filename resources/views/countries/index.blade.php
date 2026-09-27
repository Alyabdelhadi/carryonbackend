@extends('layout.main')

@section('title') Countries @endsection

@section('content')
<x-admin.page-header title="Countries" subtitle="Countries users can pick for addresses, trips and phone codes.">
    <x-admin.add-button module="countries" :href="Asset($link.'add')" />
</x-admin.page-header>

<div class="card">
    @if(count($data) === 0)
        <x-admin.empty icon="globe" title="No countries yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Arabic Name</th>
                    <th>Code</th>
                    <th>Phone Code</th>
                    <th>Flag</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td dir="rtl">{{ $row->name_ar }}</td>
                    <td><span class="badge badge-secondary">{{ $row->code }}</span></td>
                    <td>{{ $row->phone_code }}</td>
                    <td style="font-size:20px">{{ $row->flag }}</td>
                    <td><x-admin.status-toggle module="countries" :url="Asset('countryStatus?id='.$row->id)" :active="$row->status == 1" /></td>
                    <td class="text-right">
                        <x-admin.row-actions module="countries" :edit="Asset($link.$row->id.'/edit')" :delete="Asset($link.'delete/'.$row->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
