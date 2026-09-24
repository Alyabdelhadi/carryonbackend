<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    // List all notification templates
    public function index()
    {
        $templates = NotificationTemplate::all();
        return view('notifications.index', compact('templates'));
    }

    // Show add template form
    public function add()
    {
        $data = new NotificationTemplate(); // Create empty model instance
        $form_url = route('notifications.store');
    
        return view('notifications.add', compact('data', 'form_url'));
    }

    // Store new template
    public function store(Request $request)
    {
        $request->validate([
            'event' => 'required|unique:notification_templates',
            'title' => 'required',
            'body' => 'required',
        ]);

        NotificationTemplate::create($request->all());

        return redirect('notifications')->with('success', 'Template created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $data = NotificationTemplate::findOrFail($id);
        $form_url = route('notifications.update', $id);
    
        return view('notifications.edit', compact('data', 'form_url'));
    }

    // Update template
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect(env('admin').'/notifications')->with('message','Record Updated Successfully.');
    }

    // Delete template
    public function destroy($id)
    {
        NotificationTemplate::findOrFail($id)->delete();
        return redirect(env('admin').'/notifications')->with('message','Record Deleted Successfully.');
    }
}