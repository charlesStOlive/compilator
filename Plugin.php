<?php namespace Waka\Compilator;

use Backend;
use Event;
use Lang;
use System\Classes\PluginBase;
use View;

/**
 * Compilator Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Waka.Utils',
        'Waka.Informer',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Compilator',
            'description' => 'No description provided yet...',
            'author' => 'Waka',
            'icon' => 'icon-leaf',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    public function registerFormWidgets(): array
    {
        return [
            'Waka\Compilator\FormWidgets\ShowAttributes' => 'showattributes',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('backend.down.update', function ($controller) {
            if (get_class($controller) == 'Waka\Compilator\Controllers\Documents') {
                return;
            }

            if (in_array('Waka.Compilator.Behaviors.WordBehavior', $controller->implement)) {
                $data = [
                    'model' => $modelClass = str_replace('\\', '\\\\', get_class($controller->formGetModel())),
                    'modelId' => $controller->formGetModel()->id,
                ];
                return View::make('waka.compilator::publishWord')->withData($data);;
            }
        });
        Event::listen('popup.actions.line1', function ($controller, $model, $id) {
            if (get_class($controller) == 'Waka\Compilator\Controllers\Documents') {
                return;
            }

            if (in_array('Waka.Compilator.Behaviors.WordBehavior', $controller->implement)) {
                trace_log("Laligne 1 est ici");
                $data = [
                    'model' => str_replace('\\', '\\\\', $model),
                    'modelId' => $id,
                ];
                return View::make('waka.compilator::publishWordContent')->withData($data);;
            }
        });

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Waka\Compilator\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'waka.compilator.admin.super' => [
                'tab' => 'Waka',
                'label' => 'Administrateur de Compilator',
            ],
            'waka.compilator.admin' => [
                'tab' => 'Waka',
                'label' => 'Administrateur de Compilator',
            ],
            'waka.compilator.user' => [
                'tab' => 'Waka',
                'label' => 'Utilisateur de Compilator',
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];

    }
    public function registerSettings()
    {
        return [
            'documents' => [
                'label' => Lang::get('waka.compilator::lang.menu.documents'),
                'description' => Lang::get('waka.compilator::lang.menu.documents_description'),
                'category' => Lang::get('waka.compilator::lang.menu.settings_category'),
                'icon' => 'icon-file-word-o',
                'url' => Backend::url('waka/compilator/documents'),
                'permissions' => ['waka.compilator.admin'],
                'order' => 1,
            ],
            'bloc_types' => [
                'label' => Lang::get('waka.compilator::lang.menu.bloc_type'),
                'description' => Lang::get('waka.compilator::lang.menu.bloc_type_description'),
                'category' => Lang::get('waka.compilator::lang.menu.settings_category'),
                'icon' => 'icon-th-large',
                'url' => Backend::url('waka/compilator/bloctypes'),
                'permissions' => ['waka.compilator.admin'],
                'order' => 1,
            ],
        ];
    }
}
