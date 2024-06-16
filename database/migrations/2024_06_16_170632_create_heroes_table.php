<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Constants\Tables;
use App\Models\Hero;

class CreateHeroesTable extends Migration
{
    public function up()
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('health');
            $table->unsignedBigInteger('arena_id');
            $table->timestamps();

            $table->foreign('arena_id')->references('id')->on('arenas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes');
    }}
