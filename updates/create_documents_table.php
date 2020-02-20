<?php namespace Waka\Compilator\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_compilator_documents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug');

            $table->string('path');
            $table->integer('data_source_id')->unsigned()->nullable();

            $table->integer('sort_order')->default(0);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_compilator_documents');
    }
}
