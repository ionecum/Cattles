<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('secret_number')->nullable();
            $table->tinyInteger('nb_attempts')->nullable();
            $table->float('evaluation')->nullable();
            $table->boolean('outcome')->nullable();
            $table->boolean('elapsed_time')->nullable();
            $table->float('rank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
