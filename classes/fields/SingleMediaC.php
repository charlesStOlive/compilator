<?php namespace Waka\Compilator\Classes\fields;

class SingleMediaC extends BaseC
{
    use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function __construct($model, $key, $config, $options)
    {
        parent::__construct($model, $key, $config, $options);
        $this->type = "image";
    }

    public function getValue()
    {
        $value = null;
        if ($this->config['value_from_options'] ?? false) {
            $valueFromOption = $this->getOption($this->value);
            $this->value = $valueFromOption;
        }
        //trace_log("valeur depuis l'option : " . $this->value);
        $url = $this->decryptKeyedImage($this->value, $this->model);
        //trace_log('url : ' . $url);

        $width = $this->getOption('width') ?? "160";
        $height = $this->getOption('height') ?? "160";
        // $widthpx = $this->convertMmToPx($width);
        // $heightpx = $this->convertMmToPx($height);
        $ratio = $this->getOption('ratio') ?? true;

        return [
            'path' => $url,
            'width' => $width . "mm",
            'height' => $height . "mm",
            'ratio' => $ratio,
        ];
    }
}
