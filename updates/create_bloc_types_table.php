<?php namespace Waka\Compilator\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateBlocTypesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_compilator_bloc_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->string('type');
            $table->text('config');
            $table->text('datasource_accepted')->nullable();
            $table->boolean('use_icon')->default(0);
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_compilator_bloc_types');
    }
}
