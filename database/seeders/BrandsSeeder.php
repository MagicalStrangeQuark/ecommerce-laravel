<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (\App\Helpers\Utils::getSeederJSON(\Database\Seeders\DatabaseSeeder::FIELDS[\BrandsSeeder::class][\Database\Seeders\DatabaseSeeder::URL]) as $BRAND) {
            $stub = [
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];

            foreach (\Database\Seeders\DatabaseSeeder::FIELDS[\BrandsSeeder::class][\Database\Seeders\DatabaseSeeder::COLUMNS] as $INFO) {
                $stub[$INFO] = $BRAND->{$INFO};
            }

            DB::table('brands')->insert($stub);
        }
    }
}
