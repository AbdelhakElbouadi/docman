<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Department;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        App\Department::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        factory(App\Department::class, 5)->create();
    }
}
