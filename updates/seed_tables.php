<?php namespace Waka\Compilator\Updates;

//use Excel;
use Seeder;

//use System\Models\File;
//use Waka\Compilator\Models\BlocType;

// use Waka\Crsm\Classes\CountryImport;

class SeedTables extends Seeder
{
    public function run()
    {
        $this->call('Waka\Crsm\Updates\Seeders\SeedCompilator');

    }
}
