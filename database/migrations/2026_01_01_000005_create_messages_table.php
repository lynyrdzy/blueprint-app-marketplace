<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('sender_id');
            
            $table->text('content');
            
            $table->string('attachment_path')->nullable();
            
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('order_id');
            $table->index('sender_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
