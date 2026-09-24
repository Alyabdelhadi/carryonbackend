<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Arabic display data for the app's Arabic mode:
 *  - countries.name_ar, filled from database/seeders/data/country_names_ar.php
 *  - cities.name_ar, filled on demand by `php artisan places:translate-ar`
 *  - sliders.img_ar / slider2s.img_ar, an optional Arabic banner image
 * Indexes on the English names make the per-order lookups cheap.
 *
 * Every step checks first so the migration can be re-run after a partial
 * failure (MySQL DDL is not transactional). `countries` is a latin1 table,
 * so its Arabic column is declared utf8mb4 explicitly.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addColumn('countries', 'name_ar', '`name`');
        $this->addColumn('cities', 'name_ar', '`name`');
        $this->addColumn('sliders', 'img_ar', '`img`');
        $this->addColumn('slider2s', 'img_ar', '`img`');
        $this->addIndex('countries', 'countries_name_index');
        $this->addIndex('cities', 'cities_name_index');

        $names = require database_path('seeders/data/country_names_ar.php');
        foreach ($names as $code => $ar) {
            DB::table('countries')->where('code', $code)->whereNull('name_ar')->update(['name_ar' => $ar]);
        }
    }

    public function down(): void
    {
        foreach (['countries' => 'countries_name_index', 'cities' => 'cities_name_index'] as $table => $index) {
            if ($this->hasIndex($table, $index)) {
                DB::statement("ALTER TABLE `$table` DROP INDEX `$index`");
            }
        }
        foreach (['countries' => 'name_ar', 'cities' => 'name_ar', 'sliders' => 'img_ar', 'slider2s' => 'img_ar'] as $table => $column) {
            if (Schema::hasColumn($table, $column)) {
                DB::statement("ALTER TABLE `$table` DROP COLUMN `$column`");
            }
        }
    }

    private function addColumn(string $table, string $column, string $after): void
    {
        $definition = "`$column` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL";
        if (Schema::hasColumn($table, $column)) {
            DB::statement("ALTER TABLE `$table` MODIFY $definition");
        } else {
            DB::statement("ALTER TABLE `$table` ADD $definition AFTER $after");
        }
    }

    private function addIndex(string $table, string $index): void
    {
        if (!$this->hasIndex($table, $index)) {
            // Prefix index: `cities` is utf8mb4 + COMPACT rows, so a full
            // 255-char key would exceed MySQL's 767-byte limit.
            DB::statement("ALTER TABLE `$table` ADD INDEX `$index` (`name`(100))");
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return count(DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index])) > 0;
    }
};
