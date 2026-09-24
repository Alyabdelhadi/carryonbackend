<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    // List all email templates
    public function index()
    {
        $templates = EmailTemplate::all();
        return view('emails.index', compact('templates'));
    }

    // Show add template form
    public function add()
    {
        $data = new EmailTemplate(); // Create empty model instance
        $form_url = route('emails.store');
    
        return view('emails.add', compact('data', 'form_url'));
    }

    // Store new template
    public function store(Request $request)
    {
        $request->validate([
            'event' => 'required|unique:email_templates',
            'title' => 'required',
            'body' => 'required',
        ]);

        EmailTemplate::create($request->all());

        return redirect('emails')->with('success', 'Template created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $data = EmailTemplate::findOrFail($id);
        $form_url = route('emails.update', $id);
    
        return view('emails.edit', compact('data', 'form_url'));
    }

    // Update template
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect(env('admin').'/emails')->with('message','Record Updated Successfully.');
    }

    // Delete template
    public function destroy($id)
    {
        EmailTemplate::findOrFail($id)->delete();
        return redirect(env('admin').'/emails')->with('message','Record Deleted Successfully.');
    }
}