<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('original_name');
            $table->string('disk');
            $table->string('path');
            $table->string('duration')->nullable();
            $table->string('frame')->nullable();
            $table->string('DownloadedAsMp3Url')->nullable();;
            $table->datetime('converted_for_streaming_at')->nullable();
            $table->string('stream_path')->nullable();
            $table->boolean('processed')->default(false);
            $table->datetime('converted_for_downloading_at')->nullable();
           // $table->integer('tag_id')->unsigned()->nullable();
            $table->timestamps();
        });
        //Schema::table('videos', function($table) {
           //// $table->unsignedBigInteger('tag_id')->unsigned()->nullable();
           // $table->integer('tag_id')->unsigned()->nullable();
           // $table->foreign('tag_id')->references('id')->on('tags');
  //     }//);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
