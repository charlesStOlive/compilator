<?php namespace Waka\Compilator\Contents\Compilers;

use stdClass;

class Textes
{

    public function proceed($contents, $dataSourceModel)
    {
        $datas = [];
        foreach ($contents as $content) {
            foreach ($content->data as $sub) {
                $obj = new stdClass();
                $obj->value = $sub['value'];
                array_push($datas, $obj);
                if ($sub['jump']) {
                    $jump = new stdClass();
                    $jump->value = ' ';
                    array_push($datas, $jump);
                }
            }
        }
        return $datas;
    }
}
