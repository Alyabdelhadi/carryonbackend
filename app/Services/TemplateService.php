<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\ParcelCate;

class TemplateService
{
    public static function parse($template, $parcelOrder)
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
        ];

        return strtr($template, $replacements);
    }
    
    public static function parseWithUserId($template, $userId)
    {
        $user = AppUser::find($userId);
    
        $replacements = [
            ':userfullname'     => $user ? $user->name : 'User',
            ':userfirstname'    => $user ? explode(' ', $user->name)[0] : 'User',
        ];
    
        return strtr($template, $replacements);
    }
}