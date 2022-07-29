<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property integer $id
 * @property string $request_owner
 * @property integer $request_status
 * @property string $request_detail
 * @property string $created_at
 * @property string $updated_at
 * @property integer $task_id
 */
class TaskRequest extends Model
{
    //1=>'Submitted', 2=>'Valid', 3=>'Invalid', 4=>'Final',
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    const REQUEST_STATUS_IS_SUBMITTED = 1;
    const REQUEST_STATUS_IS_VALID = 2;
    const REQUEST_STATUS_IS_INVALID = 3;
    const REQUEST_STATUS_IS_FINAL = 4;

    use HasFactory;

    protected $table = 'task_request';

    protected $fillable = ['request_owner', 'request_status', 'request_detail', '$task_id',];

    /**
     *
     */
    public function relationTaskReward()
    {
        return $this->hasOne(TaskReward::class, 'id', 'task_id');
//        return $this->belongsTo(TaskReward::class, 'task_id', 'id');
    }

    public function getTaskList(){
        $collection = TaskRequest::all(['task_id'])->groupBy('task_id');
        $task_result = [];
        foreach ($collection as $value_obj)    {
            $task_result[]=[$value_obj[0]->task_id, $value_obj[0]->relationTaskReward->task_title];
        }
        return $task_result;
    }

    public function buttonPayTo($crud = false)
    {
        if (Self::REQUEST_STATUS_IS_VALID == $this->request_status) {
            $link_url = route('task.payto', ['to_addr'=> $this->request_owner, 'task_id'=>$this->task_id ]);
            return "<a class='btn btn-sm btn-link' target='_blank' href='{$link_url}' data-toggle='tooltip' title='Click the button to send awards.'><i class='fa fa-search'></i>Pay to</a>";
        }
        return '<span>--</span>';
    }
}
