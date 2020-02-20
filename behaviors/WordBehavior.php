<?php namespace Waka\Compilator\Behaviors;

use Backend\Classes\ControllerBehavior;
use October\Rain\Exception\ApplicationException;
use Waka\Compilator\Models\Document;
use Waka\Compilator\Classes\WordCreator;
use Waka\Compilator\Classes\WordProcessor;
use Flash;
use Lang;
use Redirect;
use Session;

class WordBehavior extends ControllerBehavior
{
    protected $wordBehaviorWidget;

	public function __construct($controller)
    {
        parent::__construct($controller);
        $this->wordBehaviorWidget = $this->createWordBehaviorWidget();
    }

    public function onLoadWordBehaviorPopupForm()
    {
        $dataSource = post('model');
        $dataSourceId = post('modelId');
        //
        $modelClassDecouped = explode('\\', $dataSource );
        $modelClassName = array_pop($modelClassDecouped);
        //
        $options = Document::whereHas('data_source', function ($query) use($modelClassName) {
            $query->where('model', '=', $modelClassName);
        })->lists('name', 'id');
        //
        $this->vars['options'] = $options;
        $this->vars['dataSourceId'] = $dataSourceId;
        return $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_popup.htm');
        //return true;
    }
    public function onLoadWordBehaviorContentForm()
    {
        $dataSource = post('model');
        $dataSourceId = post('modelId');
        //
        $modelClassDecouped = explode('\\', $dataSource );
        $modelClassName = array_pop($modelClassDecouped);
        //
        $options = Document::whereHas('data_source', function ($query) use($modelClassName) {
            $query->where('model', '=', $modelClassName);
        })->lists('name', 'id');
        //
        $this->vars['options'] = $options;
        $this->vars['dataSourceId'] = $dataSourceId;
        return [
            '#popupActionContent' => $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_content.htm')
        ];
    }
    public function onWordBehaviorPopupValidation()
    {
        $id = post('documentId');
        $datasourceid = post('dataSourceId');
        return Redirect::to('/backend/waka/compilator/documents/makeword/?id='.$id.'&source='.$datasourceid);

    }
    public function onLoadWordBehaviorForm()
    {
        $id = post('id');
        $wp = new WordProcessor($id);
        $tags = $wp->checkTags();  
        return Redirect::to('/backend/waka/compilator/documents/makeword/?id='.$id);
    }
    public function makeword(){
        $id = post('id');
        $dataSourceId = post('source');
        $model = Document::find($id);
        //
        $wc = new WordCreator($id);
        return $wc->renderWord($dataSourceId);   
    }
    public function onLoadWordCheck() {
        $id = post('id');
        $wp = new WordProcessor($id);
        return $wp->checkDocument();  
    }

    // public function CheckWord($id){
    //     $returnTag = WordProcessor::checkTags($id);
    //     $model = Document::find($id);
    //     if($model->has_informs('problem')) {
    //         Flash::error('Le document à des erreurs');
    //         return Redirect::refresh();
    //     } else {
    //         foreach($model->blocs as $bloc) {
    //             if($bloc->has_informs('problem')) {
    //                 Flash::error('Le document à des erreurs');
    //                 return Redirect::refresh();
    //             }
    //         }

    //     }
    //     return $returnTag;
    // }
    public function createWordBehaviorWidget() {

        $config = $this->makeConfig('$/waka/compilator/models/document/fields_for_test.yaml');
        $config->alias = 'wordBehaviorformWidget';
        $config->arrayName = 'wordBehavior_array';
        $config->model = new Document();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}