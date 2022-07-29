<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $task_kind
 * @property integer $task_status
 * @property string $task_title
 * @property string $task_detail
 * @property string $task_prize
 * @property string $created_at
 * @property string $updated_at
 */
class TaskReward extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    const TASK_STATUS_IS_ACTIVE = 1;
    const TASK_STATUS_IS_INACTIVE = 2;

    use HasFactory;
    protected $table = 'task_reward';

    protected $fillable = ['task_kind', 'task_status', 'task_title', 'task_detail', 'task_prize', 'created_at',];

}
