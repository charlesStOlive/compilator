<?php namespace Waka\Compilator\Contents;

use Backend\Classes\ControllerBehavior;

class ContentTextes extends ControllerBehavior
{
    public $contentTextesFormWidget;
    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->contentTextesFormWidget = $this->createContentTextesFormWidget();
    }

    public function onLoadCreateTextesForm()
    {
        $bloc = $this->controller->getBlocModel();

        $this->contentTextesFormWidget->context = post('context');

        $this->vars['behaviorWidget'] = $this->contentTextesFormWidget;
        $this->vars['orderId'] = post('manage_id');
        $this->vars['update'] = false;

        return [
            '#popupCompilatorContent' => $this->makePartial('$/waka/compilator/contents/form/_content_create_form.htm'),
        ];
    }
    //
    public function onLoadUpdateTextesForm()
    {
        $bloc = $this->controller->getBlocModel();
        //
        $recordId = post('record_id');
        $sk = post('_session_key');

        $this->contentTextesFormWidget = $this->createContentTextesFormWidget($recordId);
        $this->contentTextesFormWidget->context = post('context');

        //
        $this->vars['behaviorWidget'] = $this->contentTextesFormWidget;
        //
        $this->vars['orderId'] = post('manage_id');
        $this->vars['recordId'] = $recordId;
        $this->vars['update'] = true;
        //
        return [
            '#popupCompilatorContent' => $this->makePartial('$/waka/compilator/contents/form/_content_create_form.htm'),
        ];
    }

    protected function createContentTextesFormWidget($recordId = null)
    {
        $config = $this->makeConfig('$/waka/compilator/contents/compilers/textes.yaml');
        $config->alias = 'contentTextesForm';
        $config->arrayName = 'textesForm';

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
