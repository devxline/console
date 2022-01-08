<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->comment('#');
                $table->string('name', 255)->comment('имя');
                $table->string('surname', 255)->comment('фамилия');
                $table->smallInteger('age')->comment('возраст');
                $table->string('email', 255)->comment('e-mail');
                $table->integer('location_id')->unsigned()->index()->nullable()->comment('страна');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->timestamp('created_at')->useCurrent()->comment('создано');
                $table->timestamp('updated_at')->useCurrent()->comment('изменено');
                // избавляемся от возможного дубляжа
                $table->unique(['email'], 'email_unique');
            });
            DB::statement("ALTER TABLE `customers` COMMENT 'клиенты'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
