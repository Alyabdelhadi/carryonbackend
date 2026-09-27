@extends('layout.main')

@section('title') Email Templates @endsection

@section('content')
<x-admin.page-header title="Email Templates" subtitle="Emails sent to app users on each event.">
    <x-admin.add-button module="emails" :href="url('emails/add')" label="New template" />
</x-admin.page-header>

<div class="card">
    @if($templates->isEmpty())
        <x-admin.empty icon="mail" title="No templates yet" />
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
                        <x-admin.row-actions module="emails"
                            :edit="url('emails/' . $template->id . '/edit')"
                            :delete="url('emails/delete/' . $template->id)" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
