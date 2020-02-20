<?php namespace Waka\Compilator\Classes\fields;

class JsonableC extends BaseC
{
    protected $model;
    protected $key;
    protected $config;
    protected $type;
    protected $error;
    protected $options;
    protected $jsonable;
    protected $jsonkey;

    public function __construct($model, $key, $config, $options)
    {
        $this->model = $model;
        $this->key = $key;
        $this->config = $config;
        $this->type = "value";
        $this->error = $config['label_error'] ?? "Inc";
        $this->value = $config['value'];
        $this->options = $options;
        $this->fields = $config['fields'];

        $this->fieldsType = [
            'value',
            'media',
            'file',
            'montage',
            'jsonable',
        ];
    }

    public function getKey()
    {
        return null;

    }

    public function getOption($label)
    {
        if ($this->options) {
            return $this->options[$label] ?? null;
        }

    }

    public function launchJson()
    {
        //Convertion des dote.value en model liée et valeur.
        $this->model = $this->convertDotToModel($this->value);
        $this->value = $this->convertDotGetValue($this->value);

        $jsonData = $this->model[$this->value];

        $returnObject = null;

        foreach ($this->fields as $key => $config) {

            $type = $config['type'] ?? 'value';
            //trace_log("type de '.$key.' : " . $type);
            if (!in_array($type, $this->fieldsType)) {
                throw new ApplicationException("le type " . $type . " n' existe pas");
            }
            switch ($type) {
                case 'value':
                    trace_log("Analuse de " . $key);
                    trace_log($jsonData);
                    trace_log($config);
                    trace_log($config);
                    //$fieldToReturn = new ValueC($jsonData, $key, $config, $this->blocOptions);
                    break;
                case 'file':
                    $fieldToReturn = new FileC($jsonData, $key, $config, $this->blocOptions);
                    break;
                    // case 'montage':
                    //     $fieldToReturn = new MontageC($jsonData, $key, $config, $this->blocOptions);
                    //     break;
                    // case 'jsonable':
                    //     $fieldToReturn = new JsonableC($jsonData, $key, $config, $this->blocOptions);
                    //     break;
            }
            // $objKey = $fieldToReturn->getKey();
            // $objValue = $fieldToReturn->getValue();
            // $returnObject->{$objKey} = $objValue;

        }
        return $returnObject;
    }

}
