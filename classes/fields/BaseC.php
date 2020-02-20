<?php namespace Waka\Compilator\Classes\fields;

use October\Rain\Support\Collection;

class BaseC
{
    protected $model;
    protected $value;
    protected $savedValue;
    protected $key;
    protected $config;
    protected $type;
    protected $error;
    protected $options;
    protected $jsonable;
    protected $jsonkey;
    protected $jsonvalue;
    protected $nested;

    public function __construct($model, $key, $config, $options)
    {
        $this->model = $model;
        $this->key = $key;
        $this->config = $config;
        $this->type = "value";
        $this->error = $config['label_error'] ?? "Inc";
        $this->value = $config['value'];
        $this->savedValue = $config['value'];
        $this->options = $options;
        $this->jsonkey = $config['jsonkey'] ?? false;
        $this->jsonvalue = $config['jsonvalue'] ?? false;
        $this->jsonable = $config['jsonable'] ?? false;
        $this->nested = $config['nested'] ?? false;

        $this->prepareVars();
    }

    public function getKey()
    {
        return $this->type . "." . $this->key;

    }

    public function getOption($label)
    {
        if ($this->options) {
            return $this->options[$label] ?? null;
        }
    }

    public function prepareVars()
    {
        //Convertion des dote.value en model liée et valeur.
        $this->model = $this->convertDotToModel($this->value);
        $this->value = $this->convertDotGetValue($this->value);

        // if ($this->config['value_from_options'] ?? false) {
        //     $valueFromOption = $this->getOption($this->value);
        //     if ($valueFromOption) {
        //         throw new \ApplicationException('Il manque la valeur dans les options ' . $this->key . ' : ' . $this->value);
        //     }
        //     $this->value = $valueFromOption;
        // }
    }

    public function getValue()
    {
        $this->ifJsonable();
        if ($this->config['clean_md'] ?? false) {
            $this->value = $this->cleanMD($this->value);
        }
        return $this->value;
    }

    public function ifJsonable()
    {
        if (!$this->jsonable) {
            $this->value = $this->model[$this->value];
            if ($this->nested && !$this->value) {
                $nestedCol = $this->model->getParents()->sortByDesc('nest_depth');
                $this->value = $nestedCol->where($key, $this->savedValue)->first();
            }
        } else {
            $jsonCol = new Collection($this->model[$this->value]);
            $this->value = $jsonCol->where('code', $this->jsonkey)->first()[$this->jsonvalue];
            if ($this->nested && !$this->value) {
                $parents = $this->model->getParents()->sortByDesc('nest_depth')->toArray();
                foreach ($parents as $parent) {
                    $jsonCol = new Collection($parent[$this->savedValue]);
                    $value = $jsonCol->where('code', $this->jsonkey)->first()[$this->jsonvalue];
                    trace_log($value);
                    if ($value) {
                        $this->value = $value;
                        break;
                    }
                }
            }
        }
    }

    public function setParentValue()
    {
        if ($this->config['nested'] ?? false) {
            // trace_log("nested");
            // trace_log($this->value);
            if (!$this->model[$this->value]) {
                $this->value = $this->model->returnParentValue($this->value);
            }
        }
    }

    public function convertDotToModel($value)
    {
        //trace_log($this->model->toArray());
        $parts = explode(".", $value);
        $nbParts = count($parts) ?? 1;
        if ($nbParts > 1) {
            if ($nbParts == 2) {
                $returnValue = $this->model[$parts[0]] ?? null;
            }

            if ($nbParts == 3) {
                $returnValue = $this->model[$parts[0]][$parts[1]] ?? null;
            }

            if ($nbParts == 4) {
                $returnValue = $this->model[$parts[0]][$parts[1]][$parts[2]] ?? null;
            }
        } else {
            $returnValue = $this->model ?? null;
        }
        return $returnValue;
    }

    public function convertDotGetValue($value)
    {
        //trace_log($this->model->toArray());
        $parts = explode(".", $value);
        $nbParts = count($parts) ?? 1;
        if ($nbParts > 1) {
            if ($nbParts == 2) {
                $returnValue = $parts[1] ?? null;
            }

            if ($nbParts == 3) {
                $returnValue = $parts[2] ?? null;
            }

            if ($nbParts == 4) {
                $returnValue = $parts[3] ?? null;
            }
        } else {
            $returnValue = $value ?? null;
        }
        return $returnValue;
    }

    public function cleanMD($value)
    {
        $value = str_replace("**", "", $value);
        $value = str_replace("* ", "- ", $value);
        $value = str_replace("*", "", $value);
        $value = str_replace("  ", "", $value);
        return $value;
    }

}
