<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notion_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('page_id')->unique();
            $table->string('header_block_id')->unique();
            $table->string('endpoint_block_id')->unique();
            $table->string('parameters_block_id')->unique();
            $table->string('body_block_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notion_blocks');
    }
};
