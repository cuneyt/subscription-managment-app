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
        Schema::connection($this->connection)->create('subscription', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('appid');
            $table->string('uid');
            $table->boolean('substatus');
            $table->string('expired_date');

            $table->index('expired_date');
            $table->index(['expired_date', 'substatus']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection);
        Schema::dropIfExists('subscription');
    }
};
