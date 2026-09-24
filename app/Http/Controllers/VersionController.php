<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VersionController extends Controller
{
    /**
     * Show the form to edit App Versions.
     */
    public function edit()
    {
        try {
            // Single row, throws if missing
            $version = Version::mustOne();
        } catch (ModelNotFoundException $e) {
            abort(404, 'Version row not found.');
        }

        // Set page var if you want to highlight sidebar menu
        $page = 'versions';

        return view('versions.index', compact('version', 'page'));
    }

    /**
     * Handle form submission to update App Versions.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'ios'     => ['required', 'string', 'max:50'],
            'android' => ['required', 'string', 'max:50'],
        ]);

        Version::updateValues($data);

        return redirect()
            ->route('edit')
            ->with('success', 'App versions updated successfully!');
    }
}