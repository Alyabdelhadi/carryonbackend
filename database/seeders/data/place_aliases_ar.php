<?php

// Arabic names for place strings Google Places stores on orders that are
// not rows of `cities` (governorates, districts, neighbourhoods). Keys are
// compared case-insensitively. Used by App\Services\PlaceNameService as a
// fallback after the `cities` / `countries` lookup.
return [
    'mount lebanon' => 'جبل لبنان', 'mount lebanon governorate' => 'جبل لبنان', 'north governorate' => 'الشمال',
    'north lebanon' => 'الشمال', 'south governorate' => 'الجنوب', 'south lebanon' => 'الجنوب',
    'beqaa' => 'البقاع', 'bekaa' => 'البقاع', 'beqaa governorate' => 'البقاع', 'baalbek-hermel' => 'بعلبك الهرمل',
    'nabatieh governorate' => 'النبطية', 'akkar' => 'عكار', 'akkar governorate' => 'عكار', 'keserwan' => 'كسروان',
    'metn' => 'المتن', 'matn' => 'المتن', 'chouf' => 'الشوف', 'aley' => 'عاليه', 'hamra' => 'الحمرا',
    'achrafieh' => 'الأشرفية', 'dbayeh' => 'ضبية', 'antelias' => 'أنطلياس', 'jal el dib' => 'جل الديب',
    'dubai' => 'دبي', 'deira' => 'ديرة', 'bur dubai' => 'بر دبي', 'jumeirah' => 'جميرا', 'al barsha' => 'البرشاء',
    'business bay' => 'الخليج التجاري', 'downtown dubai' => 'وسط مدينة دبي', 'dubai marina' => 'مرسى دبي',
    'al quoz' => 'القوز', 'al nahda' => 'النهضة', 'mirdif' => 'مردف', 'al karama' => 'الكرامة',
    'abu dhabi' => 'أبوظبي', 'al reem island' => 'جزيرة الريم', 'khalifa city' => 'مدينة خليفة',
    'rumaithiya' => 'الرميثية', 'salmiya' => 'السالمية', 'hawally' => 'حولي', 'hawalli' => 'حولي',
    'farwaniya' => 'الفروانية', 'jabriya' => 'الجابرية', 'mahboula' => 'المهبولة', 'fahaheel' => 'الفحيحيل',
    'al rayyan' => 'الريان', 'al wakrah' => 'الوكرة', 'lusail' => 'لوسيل', 'the pearl' => 'اللؤلؤة',
    'al khobar' => 'الخبر', 'al olaya' => 'العليا', 'al malaz' => 'الملز',
    'greater london' => 'لندن الكبرى', 'île-de-france' => 'إيل دو فرانس', 'ile-de-france' => 'إيل دو فرانس',
    'north rhine-westphalia' => 'شمال الراين-وستفاليا', 'ontario' => 'أونتاريو', 'quebec' => 'كيبيك',
    'québec' => 'كيبيك', 'new york' => 'نيويورك', 'california' => 'كاليفورنيا', 'texas' => 'تكساس',
    'florida' => 'فلوريدا', 'michigan' => 'ميشيغان', 'istanbul province' => 'إسطنبول',
];
