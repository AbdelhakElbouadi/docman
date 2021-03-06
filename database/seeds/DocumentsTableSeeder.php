<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Document;

class DocumentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        App\Document::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        factory(App\Document::class, 10)->create();
    }
}
