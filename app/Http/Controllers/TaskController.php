<?php

namespace App\Http\Controllers;

use App\Models\TaskRequest;
use App\Models\TaskReward;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return "Hello atocha tasks";
    }

    public function requestList(Request $request, $request_owner)
    {
        $request_owner = trim($request_owner);
        /* @var $request_list \Illuminate\Database\Eloquent\Collection */
        $request_list = TaskRequest::where('request_owner', '=', $request_owner)->orderBy('created_at', 'DESC')->get();

        $result = [];
        foreach ($request_list as $data) {
            $result[] = $data->getAttributes();
        }
        //
        return $result;
    }

    public function rewardList(Request $request)
    {
        $task_status = trim($request->get('status'));
        $task = null;
        switch ($task_status) {
            case 'active':
            case 'inactive':
                $task = TaskReward::where('task_status', '=', $task_status)->get();
                break;
            default:
                $task = TaskReward::all();
        }
        /* @var $task \Illuminate\Database\Eloquent\Collection */

        $result = [];
        foreach ($task as $data) {
            $result[] = $data->getAttributes();
        }
        //
        return $result;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Illuminate\Validation\ValidationException
     *
     * @property integer $id
     * @property string $request_owner
     * @property integer $request_status
     * @property string $request_detail
     */
    public function do(Request $request )
    {
        $this->validate($request, [
            'request_owner' => 'required',
            'request_detail' => 'required',
        ]);

        $task_id = (int)$request->post('task_id');
        $request_owner = $request->post('request_owner');
        $request_detail = $request->post('request_detail');

        // Check task status
        $task_reward = TaskReward::find($task_id);
        if(!$task_reward || $task_reward->task_status != TaskReward::TASK_STATUS_IS_ACTIVE){
            return view('task/failed_json', []);
        }

        // Check exits data
        $task_request = TaskRequest::where('task_id', '=', $task_id)->first();
        if(is_null($task_request)){
            $task_request = new TaskRequest();
            $task_request->request_status = TaskRequest::REQUEST_STATUS_IS_SUBMITTED;
            $task_request->request_expand = '{}';
        }else if(TaskRequest::REQUEST_STATUS_IS_SUBMITTED != $task_request->request_status){
            return view('task/apply_failed', []);
        }
        $task_request->request_owner = $request_owner;
        $task_request->request_detail = $request_detail;
        $task_request->task_id = $task_id;
        $result = $task_request->save();

        if ($result > 0) {
            return view('task/success_json', []);
        }

        return view('task/failed_json', []);
    }

    public function apply(Request $request )
    {
        $this->validate($request, [
            'task_kind' => 'required',
            'task_title' => 'required',
            'task_detail' => 'required',
            'task_prize' => 'required|integer|min:1|max:50000',
        ]);

        $task_kind = $request->post('task_kind');
        $task_title = $request->post('task_title');
        $task_detail = $request->post('task_detail');
        $task_prize = $request->post('task_prize');

        $task_request = new TaskReward();
        $task_request->task_status = TaskReward::TASK_STATUS_IS_ACTIVE;
        $task_request->task_kind = $task_kind;
        $task_request->task_title = $task_title;
        $task_request->task_detail = $task_detail;
        $task_request->task_prize = $task_prize;
        $result = $task_request->save();

        if ($result > 0) {
            return view('task/apply_success', []);
        }
        return view('task/apply_failed', []);
    }

    public function payto(Request $request )
    {
//        $this->validate($request, [
//            'task_kind' => 'required',
//            'task_title' => 'required',
//            'task_detail' => 'required',
//            'task_prize' => 'required|integer|min:1|max:50000',
//        ]);

        $task_id = trim($request->get('task_id'));
        $to_addr = trim($request->get('to_addr'));
        // Check item exists from on database
        $task_request = TaskRequest::where('task_id', '=', $task_id)->where('request_owner', '=', $to_addr)->firstOrFail();
        if($task_request) {
            return view('task/payto', ['task_id'=>$task_id, 'to_addr'=>$to_addr]);
        }
        return 'Data not found.';
    }

    public function admin()
    {
        //
        return view('task/admin', ['name' => 'James']);
    }
}
