<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docversions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doc_id')->unsigned();
            $table->string('path');
            $table->string('version');
            $table->timestamps();
        });

        Schema::table('docversions', function(Blueprint $table){
            $table->foreign('doc_id')->references('id')->on('documents')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docversions');
    }
}
