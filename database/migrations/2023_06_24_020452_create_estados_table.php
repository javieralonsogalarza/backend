<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEstadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre', 50);
            $table->string('color', 15)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('estados')->insert(
            array(
                ['nombre' => 'PENDIENTE'],
                ['nombre' => 'FINALIZADO'],
                ['nombre' => 'CANCELADO']
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estados');
    }
}
