<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /** Admin: the switches page. */
    public function edit()
    {
        $page = 'app-settings';
        $definitions = AppSetting::definitions();
        $values = AppSetting::allBools();

        return view('settings.app', compact('definitions', 'values', 'page'));
    }

    /** Admin: save the switches (unchecked boxes are absent from the request). */
    public function update(Request $request)
    {
        foreach (AppSetting::definitions() as $key => $definition) {
            AppSetting::setBool($key, $request->boolean($key));
        }

        return redirect()
            ->route('app-settings.edit')
            ->with('success', 'App settings updated successfully!');
    }

    /** API: what the mobile app needs to know. */
    public function api()
    {
        return response()->json(AppSetting::allBools());
    }
}
