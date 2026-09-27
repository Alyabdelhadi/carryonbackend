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
        $choiceDefinitions = AppSetting::choiceDefinitions();
        $choiceValues = AppSetting::allChoices();
        $statDefinitions = AppSetting::statDefinitions();
        $statValues = [];
        foreach ($statDefinitions as $key => $definition) {
            $statValues[$key] = AppSetting::getInt($key);
        }
        $statComputed = \App\Services\HomeStatsService::computed();
        $numberDefinitions = AppSetting::numberDefinitions();
        $numberValues = AppSetting::allNumbers();
        $payoutMethodsRaw = optional(AppSetting::find(AppSetting::PAYOUT_METHODS))->value
            ?? implode("\n", array_map(fn ($m) => $m['code'] . '|' . $m['name'], AppSetting::payoutMethods()));
        $stripeEnabled = \App\Services\StripePaymentService::isEnabled();

        return view('settings.app', compact(
            'definitions', 'values', 'choiceDefinitions', 'choiceValues', 'page', 'statDefinitions', 'statValues', 'statComputed',
            'numberDefinitions', 'numberValues', 'payoutMethodsRaw', 'stripeEnabled'
        ));
    }

    /** Admin: save the switches (unchecked boxes are absent from the request). */
    public function update(Request $request)
    {
        foreach (AppSetting::definitions() as $key => $definition) {
            AppSetting::setBool($key, $request->boolean($key));
        }
        foreach (AppSetting::choiceDefinitions() as $key => $definition) {
            AppSetting::setChoice($key, $request->input($key));
        }
        foreach (AppSetting::statDefinitions() as $key => $definition) {
            AppSetting::setNullableInt($key, $request->input($key));
        }
        foreach (AppSetting::numberDefinitions() as $key => $definition) {
            AppSetting::setNumber($key, $request->input($key));
        }
        AppSetting::updateOrCreate(
            ['key' => AppSetting::PAYOUT_METHODS],
            ['value' => trim((string) $request->input(AppSetting::PAYOUT_METHODS))]
        );

        return redirect()
            ->route('app-settings.edit')
            ->with('success', 'App settings updated successfully!');
    }

    /** API: what the mobile app needs to know. */
    public function api()
    {
        $method = AppSetting::getChoice(AppSetting::IDENTITY_METHOD);
        return response()->json(array_merge(
            AppSetting::allBools(),
            [
                'identity_method' => $method,
                // app builds from before identity_method: live page vs photo upload
                'shufti_live' => $method === AppSetting::IDENTITY_SHUFTI,
                'payments' => WalletApiController::rules(),
            ]
        ));
    }

    /** API: the home-screen counters. */
    public function stats()
    {
        return response()->json(\App\Services\HomeStatsService::stats());
    }
}
