<?php namespace Waka\Compilator\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Yaml;

/**
 * Documents Back-end Controller
 */
class Documents extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
        'Backend.Behaviors.RelationController',
        'Waka.Informer.Behaviors.PopupInfo',
        'Waka.Compilator.Behaviors.WordBehavior',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    //public $duplicateConfig = 'config_duplicate.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $reorderConfig = 'config_reorder.yaml';
    public $contextContent;

    public function __construct()
    {
        parent::__construct();

        //BackendMenu::setContext('Waka.Compilator', 'compilator', 'side-menu-documents');
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Compilator', 'documents');

    }

    public function onTestList()
    {
        $model = \Waka\Compilator\Models\Document::find($this->params[0]);
    }

    public function onCreateItem()
    {
        $bloc = $this->getBlocModel();

        $data = post($bloc->bloc_type->code . 'Form');
        $sk = post('_session_key');
        $bloc->delete_informs();

        $model = new \Waka\Compilator\Models\Content;
        $model->fill($data);
        $model->save();

        $bloc->contents()->add($model, $sk);

        return $this->refreshOrderItemList($sk);
    }

    public function onUpdateContent()
    {
        $bloc = $this->getBlocModel();

        $recordId = post('record_id');
        $data = post($bloc->bloc_type->code . 'Form');
        $sk = post('_session_key');

        $model = \Waka\Compilator\Models\Content::find($recordId);
        $model->fill($data);
        $model->save();

        return $this->refreshOrderItemList($sk);
    }

    public function onDeleteItem()
    {
        $recordId = post('record_id');
        $sk = post('_session_key');

        $model = \Waka\Compilator\Models\Content::find($recordId);

        $bloc = $this->getBlocModel();
        $bloc->contents()->remove($model, $sk);

        return $this->refreshOrderItemList($sk);
    }

    protected function refreshOrderItemList($sk)
    {
        $bloc = $this->getBlocModel();
        $contents = $bloc->contents()->withDeferred($sk)->get();

        $this->vars['contents'] = $contents;
        $this->vars['bloc_type'] = $bloc->bloc_type;
        return [
            '#contentList' => $this->makePartial('content_list'),
        ];
    }

    public function getBlocModel()
    {
        $manageId = post('manage_id');

        $bloc = $manageId
        ? \Waka\Compilator\Models\Bloc::find($manageId)
        : new \Waka\Compilator\Models\Bloc;

        return $bloc;
    }
    public function relationExtendManageWidget($widget, $field, $model)
    {
        $widget->bindEvent('form.extendFields', function () use ($widget) {

            if (!$widget->model->bloc_type) {
                return;
            }

            $options = [];

            $yaml = Yaml::parse($widget->model->bloc_type->config);

            $modelOptions = $yaml['model']['options'] ?? false;
            if ($modelOptions) {
                foreach ($modelOptions as $key => $opt) {
                    $options[$key] = $opt;
                }
            }

            $fields = $yaml['fields'];
            foreach ($fields as $field) {
                if ($field['options'] ?? false) {
                    foreach ($field['options'] as $key => $opt) {
                        $options[$key] = $opt;
                    }

                }
            }
            if (count($options) > 0 ?? false) {
                $fieldtoAdd = [
                    'bloc_form' => [
                        'tab' => 'content',
                        'type' => 'nestedform',
                        'usePanelStyles' => false,
                        'form' => [
                            'fields' => $options,
                        ],
                    ],
                ];
                $widget->addTabFields($fieldtoAdd);
            }

        });
    }

}
