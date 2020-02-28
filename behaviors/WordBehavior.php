<?php namespace Waka\Compilator\Behaviors;

use Backend\Classes\ControllerBehavior;
use Redirect;
use Waka\Compilator\Classes\WordCreator;
use Waka\Compilator\Classes\WordProcessor;
use Waka\Compilator\Models\Document;

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
        $model = post('model');
        $modelId = post('modelId');

        $modelClassDecouped = explode('\\', $model);
        $modelClassName = array_pop($modelClassDecouped);
        //On initialise le dataSource
        $dataSource = \Waka\Utils\Models\DataSource::where('model', '=', $modelClassName)->first();
        //On recherche la collection de relations si elle existe, sinon retourne null
        $relations = $dataSource->getRelationCollection($modelId);

        $options = Document::whereHas('data_source', function ($query) use ($modelClassName) {
            $query->where('model', '=', $modelClassName);
        })->lists('name', 'id');

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['dataSrcId'] = $dataSource->id;
        $this->vars['relations'] = $relations;

        return $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_popup.htm');
    }
    public function onLoadWordBehaviorContentForm()
    {
        $model = post('model');
        $modelId = post('modelId');
        //
        $modelClassDecouped = explode('\\', $model);
        $modelClassName = array_pop($modelClassDecouped);
        //On initialise le dataSource
        $dataSource = \Waka\Utils\Models\DataSource::where('model', '=', $modelClassName)->first();
        //On recherche la collection de relations si elle existe, sinon retourne null
        $relations = $dataSource->getRelationCollection($modelId);

        $options = Document::whereHas('data_source', function ($query) use ($modelClassName) {
            $query->where('model', '=', $modelClassName);
        })->lists('name', 'id');
        //
        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['relations'] = $relations;

        return [
            '#popupActionContent' => $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_content.htm'),
        ];
    }
    public function onWordBehaviorPopupValidation()
    {
        $docId = post('documentId');
        $modelId = post('modelId');

        $dataSource = Document::find($docId)->data_source;
        //On recherche la collection de relations si elle existe, sinon retourne null
        $relations = $dataSource->getRelationCollection($modelId);

        $additionalParams = "";
        if ($relations) {
            trace_log("relations");
            foreach ($relations as $relation) {
                trace_log($relation['param']);
                $additionalParams .= '&' . $relation['param'] . '=' . post($relation['param']);
            }
        }

        return Redirect::to('/backend/waka/compilator/documents/makeword/?docId=' . $docId . '&modelId=' . $modelId . $additionalParams);

    }
    /**
     * Cette fonction est utilisé lors du test depuis le controller document.
     */
    public function onLoadWordBehaviorForm()
    {
        $id = post('id');
        $wp = new WordProcessor($id);
        $tags = $wp->checkTags();
        return Redirect::to('/backend/waka/compilator/documents/makeword/?id=' . $id);
    }
    public function makeword()
    {
        $docId = post('docId');
        $modelId = post('modelId');
        //On initialise le dataSource
        $dataSource = Document::find($docId)->data_source;
        //On recherche la collection de relations si elle existe, sinon retourne null
        $relations = $dataSource->getRelationCollection($modelId);
        $additionalParams = [];
        trace_log("Make Word");
        if ($relations) {
            trace_log("Make Word Has relation");
            foreach ($relations as $relation) {
                $additionalParams[$relation['param']] = post($relation['param']);
            }
        }
        trace_log("Make Word AdditionalParams");
        //
        $wc = new WordCreator($docId);
        $wc->setAdditionalParams($additionalParams);
        return $wc->renderWord($modelId);
    }
    public function onLoadWordCheck()
    {
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
    public function createWordBehaviorWidget()
    {

        $config = $this->makeConfig('$/waka/compilator/models/document/fields_for_test.yaml');
        $config->alias = 'wordBehaviorformWidget';
        $config->arrayName = 'wordBehavior_array';
        $config->model = new Document();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}
