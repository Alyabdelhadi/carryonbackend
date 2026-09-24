<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\ParcelCate;

class TemplateService
{
    public static function parse($template, $parcelOrder, array $extra = [])
    {
        $user = AppUser::find($parcelOrder->user_id);
        $carrier = $parcelOrder->carrier_id ? AppUser::find($parcelOrder->carrier_id) : null;
        $parcelType = ParcelCate::find($parcelOrder->cate_id);

        $replacements = [
            ':userfullname'     => $user ? $user->name : 'User',
            ':userfirstname'    => $user ? explode(' ', $user->name)[0] : 'User',
            ':carrierfullname'  => $carrier ? $carrier->name : 'Carrier',
            ':carrierfirstname' => $carrier ? explode(' ', $carrier->name)[0] : 'Carrier',
            ':fromcity'         => $parcelOrder->s_city,
            ':tocity'           => $parcelOrder->r_city,
            ':packagetype'      => $parcelType ? $parcelType->name : 'Unknown Type',
            ':packagenumber'    => $parcelOrder->id,
            ':amount'           => self::money($parcelOrder->payment_amount, $parcelOrder->payment_currency),
            ':earning'          => self::money($parcelOrder->carrier_earning, $parcelOrder->payment_currency),
        ];

        return strtr($template, array_merge($replacements, $extra));
    }
    
    public static function parseWithUserId($template, $userId, array $extra = [])
    {
        $user = AppUser::find($userId);
    
        $replacements = [
            ':userfullname'     => $user ? $user->name : 'User',
            ':userfirstname'    => $user ? explode(' ', $user->name)[0] : 'User',
            ':carrierfullname'  => $user ? $user->name : 'Carrier',
            ':carrierfirstname' => $user ? explode(' ', $user->name)[0] : 'Carrier',
        ];
    
        return strtr($template, array_merge($replacements, $extra));
    }

    /** "25.00 USD" for the :amount / :earning placeholders. */
    public static function money($amount, $currency): string
    {
        return number_format((float) $amount, 2) . ' ' . strtoupper($currency ?: 'USD');
    }
}