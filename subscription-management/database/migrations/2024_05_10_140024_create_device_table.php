<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('device', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->string('uid');
            $table->integer('appid');
            $table->string('client-token');
            $table->string('os');
            $table->string('language');


            $table->index('uid');
            $table->index('appid');
            $table->index('client-token');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection);
        Schema::dropIfExists('device_collection');
    }
};
