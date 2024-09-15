<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\App;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('perfil_id')->nullable();
            $table->foreign('perfil_id')->references('id')->on('perfils');
            $table->string('nombre', 150);
            $table->string('email', 150);
            $table->string('telefono', 15)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 250);
            $table->boolean('principal')->default(false);
            $table->rememberToken();
            $table->actionusers();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert(
            array(
                ['perfil_id' => App::$PERFIL_ADMINISTRADOR, 'nombre' => 'Daniel Valverde', 'email' => 'info@webaltoque.com', 'password' => Hash::make('qwerty')]
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
        Schema::dropIfExists('users');
    }
}
