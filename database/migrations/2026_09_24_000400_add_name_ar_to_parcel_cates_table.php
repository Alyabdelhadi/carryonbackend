<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Arabic name for package categories (Document, Gift, …). */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('parcel_cates', 'name_ar')) {
            DB::statement('ALTER TABLE `parcel_cates` ADD `name_ar` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `name`');
        }
        foreach ([
            'Document' => 'مستند', 'Gift' => 'هدية', 'Electronic' => 'إلكترونيات', 'Electronics' => 'إلكترونيات',
            'Parcel' => 'طرد', 'Fashion' => 'أزياء', 'Baby' => 'مستلزمات أطفال', 'Decor' => 'ديكور',
            'Food' => 'طعام', 'Medicine' => 'أدوية', 'Clothes' => 'ملابس', 'Other' => 'أخرى',
        ] as $en => $ar) {
            DB::table('parcel_cates')->where('name', $en)->whereNull('name_ar')->update(['name_ar' => $ar]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('parcel_cates', 'name_ar')) {
            DB::statement('ALTER TABLE `parcel_cates` DROP COLUMN `name_ar`');
        }
    }
};
