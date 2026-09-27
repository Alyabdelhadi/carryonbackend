@extends('layout.main')

@section('title') Notification Templates @endsection

@section('content')
<x-admin.page-header title="Notification Templates" subtitle="Push messages the app sends on each event.">
    <x-admin.add-button module="notifications" :href="url('notifications/add')" label="New template" />
</x-admin.page-header>

<div class="card">
    @if($templates->isEmpty())
        <x-admin.empty icon="bell" title="No templates yet" />
    @else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Title</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($templates as $template)
                <tr>
                    <td><code>{{ $template->event }}</code></td>
                    <td><strong>{{ $template->title }}</strong></td>
                    <td class="text-right">
                        <x-admin.row-actions module="notifications"
                            :edit="url('notifications/' . $template->id . '/edit')"
                            :delete="url('notifications/delete/' . $template->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
