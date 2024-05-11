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
        Schema::connection($this->connection)->create('mobapp', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('appid');
            $table->string('apptitle');
            $table->string('uname');
            $table->string('pass');

            $table->index('appid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection);
        Schema::dropIfExists('mobapp');
    }
};
