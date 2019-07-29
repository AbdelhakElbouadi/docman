<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //sender, recipient, docId, version, readstatus,datetime, message 
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender')->unsigned();
            $table->bigInteger('recipient')->unsigned();
            $table->bigInteger('doc_id')->unsigned();
            $table->string('version');
            $table->boolean('is_read')->default(false);
            $table->dateTime('datetime');
            $table->text('message');
            $table->timestamps();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('sender')->on('users')->references('id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('recipient')->on('users')->references('id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('doc_id')->on('documents')->references('id')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
