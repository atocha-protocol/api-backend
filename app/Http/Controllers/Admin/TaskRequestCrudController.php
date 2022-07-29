<?php

namespace App\Http\Controllers\Admin;

use Abraham\TwitterOAuth\Request;
use App\Http\Requests\TaskRequestRequest;
use App\Models\TaskReward;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\KamiOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

/**
 * Class TaskRequestCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TaskRequestCrudController extends CrudController
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

        $uri = config('backpack.base.route_prefix') . '/task-request';
        CRUD::setModel(\App\Models\TaskRequest::class);
        CRUD::setRoute($uri);
        CRUD::setEntityNameStrings('task request', 'task requests');
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
        $this->crud->removeButton("create");
        $this->crud->removeButton("delete");
//        $this->crud->addButton('line', 'pay_fee', 'view', 'Pay to', false);
        $this->crud->addButtonFromModelFunction('line', 'pay_fee', 'buttonPayTo', false);

        // Get all task list.
        $task_list = TaskReward::all();
        $select_task_list = [];
        foreach ($task_list as $task_obj) {
            $select_task_list[$task_obj->id] = $task_obj->task_title;
        }

        // Filter list
        $task_id = $this->crud->getRequest()->get('task_id');
        if(0 != $task_id){
            $this->crud->addClause('where', 'task_id', '=', $task_id);
        }

        CRUD::column('id');
        CRUD::column('request_owner');
        CRUD::column('request_status')->type('select_from_array')->options($this->getRequestStatusSelectArray());
        CRUD::column('request_detail');
        CRUD::column('created_at');
        CRUD::column('updated_at');
        CRUD::column('task_id')->type('select_from_array')->options($select_task_list);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->removeButton("delete");
        CRUD::setValidation(TaskRequestRequest::class);
        CRUD::field('request_status')->type('select_from_array')->options($this->getRequestStatusSelectArray(false));
    }

    private function getRequestStatusSelectArray($update_mod=true) {
        $base_arr = [2=>'Valid', 3=>'Invalid'];
        if($update_mod) {
            $base_arr[1]='Submitted';
            $base_arr[4]='Final';
        }
        return $base_arr;
    }
}
