<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('match_entries', function (Blueprint $table) {
            $table->id();
            $table->string('match_id');
            $table->string('name');
            $table->integer('cap');
            $table->integer('vcap');
            $table->string('role');
            $table->integer('percent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('match_entries');
    }
};

