<?php namespace Waka\Compilator\Classes\fields;

class MontageC extends BaseC
{
    public function __construct($model, $key, $config, $options)
    {
        parent::__construct($model, $key, $config, $options);
        $this->type = "image";
    }

    public function getValue()
    {
        $width = $this->getOption('width') ?? "160";
        $height = $this->getOption('height') ?? "160";
        $widthpx = $this->convertMmToPx($width);
        $heightpx = $this->convertMmToPx($height);
        $ratio = $this->getOption('ratio') ?? true;
        //trace_log("image path  = " . $this->model->{$this->key}->getPath());
        return [
            'path' => $this->model->getCloudiBaseUrl($this->key, 'jpg-' . $widthpx . '-' . $heightpx),
            'width' => $width . "mm",
            'height' => $height . "mm",
            'ratio' => $ratio,
        ];
    }
    public function convertMmToPx($value)
    {
        // en 96 dpi
        $value = $value * 4;
        return intval($value);
    }
}
