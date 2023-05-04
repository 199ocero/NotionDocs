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
        Schema::create('notion_apis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notion_database_id');
            $table->string('page_id')->unique();
            $table->string('tile');
            $table->string('description');
            $table->string('method');
            $table->string('endpoint');
            $table->json('params')->nullable();
            $table->json('body')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
            $table->foreign('notion_database_id')->references('id')->on('notion_databases')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notion_apis');
    }
};
