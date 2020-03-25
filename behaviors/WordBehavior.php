<?php namespace Waka\Compilator\Behaviors;

use Backend\Classes\ControllerBehavior;
use Redirect;
use Waka\Compilator\Classes\WordCreator2;
use Waka\Compilator\Classes\WordProcessor2;
use Waka\Compilator\Models\Document;

class WordBehavior extends ControllerBehavior
{
    protected $wordBehaviorWidget;

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->wordBehaviorWidget = $this->createWordBehaviorWidget();
    }

    /**
     * METHODES
     */

    public function getDataSourceClassName(String $model)
    {
        $modelClassDecouped = explode('\\', $model);
        return array_pop($modelClassDecouped);

    }

    public function getDataSourceFromModel(String $model)
    {
        $modelClassName = $this->getDataSourceClassName($model);
        //On recherche le data Source depuis le nom du model
        return \Waka\Utils\Models\DataSource::where('model', '=', $modelClassName)->first();
    }

    public function getModel($model, $modelId)
    {
        $myModel = $model::find($modelId);
        return $myModel;
    }

    public function getPartialOptions($model, $modelId)
    {
        $modelClassName = $this->getDataSourceClassName($model);

        $options = Document::whereHas('data_source', function ($query) use ($modelClassName) {
            $query->where('model', '=', $modelClassName);
        });

        $myModel = $this->getModel($model, $modelId);

        $optionsList = [];

        foreach ($options->get() as $option) {
            if ($option->scopes) {
                if ($this->checkScopes($myModel, $option->scopes)) {
                    $optionsList[$option->id] = $option->name;
                }
            } else {
                $optionsList[$option->id] = $option->name;
            }
        }
        return $optionsList;

    }
    /**
     * LOAD DES POPUPS
     */
    public function onLoadWordBehaviorPopupForm()
    {
        $model = post('model');
        $modelId = post('modelId');

        $options = $this->getPartialOptions($model, $modelId);

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        // $this->vars['dataSrcId'] = $dataSource->id;

        return $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_popup.htm');
    }
    public function onLoadWordBehaviorContentForm()
    {
        $model = post('model');
        $modelId = post('modelId');

        $options = $this->getPartialOptions($model, $modelId);

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;

        return [
            '#popupActionContent' => $this->makePartial('$/waka/compilator/behaviors/wordbehavior/_content.htm'),
        ];
    }

    public function onWordBehaviorPopupValidation()
    {
        $errors = $this->CheckValidation(\Input::all());
        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }
        $docId = post('documentId');
        $modelId = post('modelId');

        return Redirect::to('/backend/waka/compilator/documents/makeword/?docId=' . $docId . '&modelId=' . $modelId);

    }

    /**
     * Validations
     */
    public function CheckValidation($inputs)
    {
        $rules = [
            'modelId' => 'required',
            'documentId' => 'required',
        ];

        $validator = \Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }
    /**
     * Cette fonction est utilisé lors du test depuis le controller document.
     */
    public function onLoadWordBehaviorForm()
    {
        $id = post('id');
        $wp = new WordProcessor2($id);
        $tags = $wp->checkTags();
        return Redirect::to('/backend/waka/compilator/documents/makeword/?docId=' . $id);
    }
    public function makeword()
    {
        $docId = post('docId');
        $modelId = post('modelId');

        $wc = new WordCreator2($docId);

        return $wc->renderWord($modelId);
    }
    public function onLoadWordCheck()
    {
        $id = post('id');
        $wp = new WordProcessor2($id);
        return $wp->checkDocument();
    }

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
