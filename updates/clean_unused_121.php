<?php namespace Waka\Compilator\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CleanUnused121 extends Migration
{
    public function up()
    {
        Schema::dropIfExists('waka_compilator_bloc_types');
        Schema::dropIfExists('waka_compilator_blocs');
    }

    public function down()
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
        Schema::create('waka_compilator_blocs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code');
            $table->string('name')->nullable();
            $table->text('bloc_form')->nullable();
            $table->string('ready')->default(0);
            $table->integer('document_id')->unsigned()->nullable();
            $table->integer('bloc_type_id')->unsigned();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

}
