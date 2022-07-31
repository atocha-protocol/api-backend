<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaskRewardRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TaskRewardCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TaskRewardCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\TaskReward::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/task-reward');
        CRUD::setEntityNameStrings('task reward', 'task rewards');
        $this->crud->removeButton("delete");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        CRUD::column('id');
        CRUD::column('task_kind');
        CRUD::column('task_status')->type('select_from_array')->options([1=>'Valid', 2=>'Invalid']);;
        CRUD::column('task_title');
        CRUD::column('task_detail');
        CRUD::column('task_prize');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TaskRewardRequest::class);


//        CRUD::field('id');
//        CRUD::field([
//            'name' => 'task_kind',
//            'label' => 'TaskKind',
//            'type' => 'ckeditor',
//            'placeholder' => 'Your textarea text here',
//            'validationRules' => 'required|min:10',
//            'validationMessages' => [
//                'required' => 'You gotta write smth man.',
//                'min' => 'More than 10 characters, bro. Wtf... You can do this!',
//            ]
//        ]);

//        CRUD::enum('task_kind',  ['In attesa', 'Aperto', 'Chiuso']);

//        $this->crud->field('task_kind')->type('enum')->attributes(['In attesa', 'Aperto', 'Chiuso']);
//        CRUD::field('task_kind')->type('enum')->options( ['In attesa', 'Aperto', 'Chiuso']);
//        $this->crud->addField([   // radio
//            'name'        => 'task_kind', // the name of the db column
//            'label'       => 'Task Kind', // the input label
//            'type'        => 'select_from_array',
//            'options'     => [
//                // the key will be stored in the db, the value will be shown as label;
//                'DEFAULT' => 'DEFAULT',
//            ],
//            'default' => 'DEFAULT',
//            // optional
//            //'inline'      => false, // show the radios all on the same line?
//        ]);

        CRUD::field('task_kind')->type('select_from_array')->options(['Default' => 'Default']);
        CRUD::field('task_status')->type('select_from_array')->options([1=>'Valid', 2=>'Invalid']);
        CRUD::field('task_title');
        CRUD::field('task_detail');
        CRUD::field('task_prize')->label('Task prize (Ato)');
//        CRUD::field('created_at');
//        CRUD::field('updated_at');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
