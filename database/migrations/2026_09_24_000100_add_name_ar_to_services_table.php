<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Arabic display name for the home services. The app shows `name_ar` when
 * its language is Arabic and falls back to `name` when it is empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });

        foreach ([
            'Send a package' => 'إرسال طرد',
            'Receive a package' => 'استلام طرد',
            'Carry a package' => 'حمل طرد',
        ] as $en => $ar) {
            DB::table('services')->where('name', $en)->whereNull('name_ar')->update(['name_ar' => $ar]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
