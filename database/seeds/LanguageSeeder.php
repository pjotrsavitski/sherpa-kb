<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        DB::table('languages')->insert([
            [
                'name' => 'English',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Estonian',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Finnish',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Greek',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Italian',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
