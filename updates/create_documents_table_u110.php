<?php namespace Waka\Compilator\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateDocumentsTableU110 extends Migration
{
    public function up()
    {
        Schema::table('waka_compilator_documents', function (Blueprint $table) {
            $table->text('scopes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_compilator_documents', function (Blueprint $table) {
            $table->dropColumn('scopes');
        });
    }
}
