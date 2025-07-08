<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TechnicianSeeder extends Seeder
{
    public function run()
    {
        DB::table('technicians')->insert([
            [
                'name'       => 'man',
                'position'   => 'mechanic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'icen',
                'position'   => 'mechanic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'drei',
                'position'   => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
