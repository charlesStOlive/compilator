<?php namespace Waka\Compilator\Contents;

use Backend\Classes\ControllerBehavior;

class ContentStaticConfig extends ControllerBehavior
{
    public $contentStaticConfigFormWidget;
    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->contentStaticConfigFormWidget = $this->createContentStaticConfigFormWidget();
    }

    public function onLoadCreateStaticConfigForm()
    {
        $bloc = $this->controller->getBlocModel();

        $this->contentStaticConfigFormWidget->context = post('context');

        $this->vars['behaviorWidget'] = $this->contentStaticConfigFormWidget;
        $this->vars['orderId'] = post('manage_id');
        $this->vars['update'] = false;

        return [
            '#popupCompilatorContent' => $this->makePartial('$/waka/compilator/contents/form/_content_create_form.htm'),
        ];
    }
    //
    public function onLoadUpdateStaticConfigForm()
    {
        $bloc = $this->controller->getBlocModel();
        //
        $recordId = post('record_id');
        $sk = post('_session_key');

        $this->contentStaticConfigFormWidget = $this->createContentStaticConfigFormWidget($recordId);
        $this->contentStaticConfigFormWidget->context = post('context');

        //
        $this->vars['behaviorWidget'] = $this->contentStaticConfigFormWidget;
        //
        $this->vars['orderId'] = post('manage_id');
        $this->vars['recordId'] = $recordId;
        $this->vars['update'] = true;
        //
        return [
            '#popupCompilatorContent' => $this->makePartial('$/waka/compilator/contents/form/_content_create_form.htm'),
        ];
    }

    protected function createContentStaticConfigFormWidget($recordId = null)
    {
        $config = $this->makeConfig('$/waka/compilator/contents/compilers/staticconfig.yaml');
        $config->alias = 'contentStaticConfigForm';
        $config->arrayName = 'StaticConfigForm';

        if (!$recordId) {
            $config->model = new \Waka\Compilator\Models\Content;
        } else {
            $config->model = \Waka\Compilator\Models\Content::find($recordId);
        }

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        return $widget;
    }

}
