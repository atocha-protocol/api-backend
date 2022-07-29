<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait KamiOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupListRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/kami', [
            'as'        => $routeName.'.kami',
            'uses'      => $controller.'@kami',
            'operation' => 'list',
        ]);

    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupListDefaults()
    {
        $this->crud->allowAccess('list');

        $this->crud->operation('list', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getListView(), $this->data);
    }

}
