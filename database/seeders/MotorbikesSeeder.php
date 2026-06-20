<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MotorbikesSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FujairahMotorbikesSeeder::class,
            DubaiMotorbikesSeeder::class,
            UmmAlQuwainMotorbikesSeeder::class,
        ]);
    }
}
