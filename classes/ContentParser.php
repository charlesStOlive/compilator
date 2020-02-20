<?php namespace Waka\Compilator\Classes;

use ApplicationException;
use stdClass;
use Waka\Compilator\Classes\Fields\FileC;
use Waka\Compilator\Classes\Fields\MontageC;
use Waka\Compilator\Classes\Fields\SingleMediaC;
use Waka\Compilator\Classes\Fields\ValueC;

class ContentParser
{
    protected $fieldsType;
    public $error;
    private $model;
    private $modelCollection;
    protected $blocOptions;

    public function __construct()
    {
        $this->fieldsType = [
            'value',
            'media',
            'file',
            'montage',
        ];
    }
    public function setModel($blocModel, $model)
    {
        $type = $blocModel['type'] ?? 'error';
        $childs = $blocModel['childs'] ?? false;
        if ($type == 'error') {
            new ApplicationException("Il manque le type de model dans la config");
        }
        if ($type == 'inherit') {
            $this->modelCollection = $model;
        }

        if ($type == 'relation') {
            $relationName = $blocModel['name'];
            if ($childs) {
                $this->modelCollection = $this->convertDotToRelationArray($model, $relationName)->with($childs)->get();
            } else {
                $this->modelCollection = $this->convertDotToRelationArray($model, $relationName)->get();
            }
            // trace_log('relation');
            // trace_log($this->modelCollection->toArray());

        }
        if ($type == 'src') {
            $srcName = $blocModel['name'];
            if ($childs) {
                $this->modelCollection = $srcName::query()->with($childs)->get();

            } else {
                $this->modelCollection = $srcName::get();
            }

        }
    }
    public function setOptions($options)
    {
        $this->blocOptions = $options;
        //trace_log("Set options");
        //trace_log($this->blocOptions);
    }

    public function parseFields($fields)
    {
        //trace_log("fields condif in parseFields");
        //trace_log($fields);
        $datas = [];
        if (!$this->modelCollection) {
            new ApplicationException("Le modèle n'a pas été correctement initialisé ");
        }
        if (!is_countable($this->modelCollection)) {
            $returnObject = new stdClass();
            foreach ($fields as $key => $config) {
                $type = $config['type'] ?? 'value';
                $fieldToReturn;
                switch ($type) {
                    case 'single_media':
                        $fieldToReturn = new SingleMediaC($this->modelCollection, $key, $config, $this->blocOptions);
                        break;
                }
                $objKey = $fieldToReturn->getKey();
                $objValue = $fieldToReturn->getValue();
                $returnObject->{$objKey} = $objValue;
                array_push($datas, $returnObject);
                return $datas;
            }
        }
        foreach ($this->modelCollection as $rowModel) {

            $returnObject = new stdClass();
            // trace_log("c est une collection");
            // trace_log("Model à lire");
            // trace_log($rowModel->toArray());

            foreach ($fields as $key => $config) {
                $type = $config['type'] ?? 'value';
                //trace_log("type de '.$key.' : " . $type);
                if (!in_array($type, $this->fieldsType)) {
                    throw new ApplicationException("le type " . $type . " n' existe pas");
                }
                $fieldToReturn;
                switch ($type) {
                    case 'value':
                        $fieldToReturn = new ValueC($rowModel, $key, $config, $this->blocOptions);
                        break;
                    // case 'media':
                    //     $val = new MediaC($this->model, $key, $config);
                    //     $fieldToReturn = $val->getValue();
                    //     break;
                    case 'file':
                        $fieldToReturn = new FileC($rowModel, $key, $config, $this->blocOptions);
                        break;
                    case 'montage':
                        $fieldToReturn = new MontageC($rowModel, $key, $config, $this->blocOptions);
                        break;
                        // case 'jsonable':
                        //     $jsonable = new JsonableC($rowModel, $key, $config, $this->blocOptions);
                        //     $rows = $jsonable->launchJson()
                        //     break;
                }
                $objKey = $fieldToReturn->getKey();
                $objValue = $fieldToReturn->getValue();
                $returnObject->{$objKey} = $objValue;
            }
            array_push($datas, $returnObject);

        }
        //trace_log("datas return from parser");
        //trace_log($datas);
        return $datas;
    }
    public function convertDotToRelationArray($model, $value)
    {

        //trace_log($this->model->toArray());
        $parts = explode(".", $value);
        $nbParts = count($parts) ?? 1;
        if ($nbParts > 1) {
            if ($nbParts == 2) {
                $returnValue = $model->{$parts[0]}->{$parts[1]}() ?? null;
            }

            if ($nbParts == 3) {
                $returnValue = $model->{$parts[0]}->{$parts[1]}->{$parts[2]}() ?? null;
            }

            if ($nbParts == 4) {
                $returnValue = $model{$parts[0]}->{$parts[1]}->{$parts[2]}->{$parts[3]}() ?? null;
            }

        } else {
            $returnValue = $model->{$value}() ?? null;
        }
        return $returnValue;

    }

}
