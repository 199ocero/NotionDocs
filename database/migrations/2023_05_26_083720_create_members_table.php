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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('invited_by_id');
            $table->unsignedBigInteger('invited_id');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('invitation_response_at')->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('invited_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_id')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
};
