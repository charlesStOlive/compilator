<?php namespace Waka\Compilator\Contents\Compilers;

use Waka\Compilator\Classes\ContentParser;
use Yaml;

class StaticConfig
{

    public static function proceed($contents, $dataSourceModel)
    {
        foreach ($contents as $content) {
            $data = Yaml::parse($content->data);
            $src = $data['src'] ?? false;
            $relation = $data['relation'] ?? false;
            $parser = new ContentParser();
            $parser->setModel($src, $dataSourceModel, $relation);
            $fields = $data['fields'] ?? false;
            //trace_log("----------fields-----------");
            //trace_log($fields);
            $resultat = $parser->parseFields($fields);
           //trace_log("Resultat process Static config");
           //trace_log($resultat);
            return $resultat;
        }
    }
}
