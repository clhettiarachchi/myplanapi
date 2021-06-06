<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class TaskController extends Controller
{
    public $LOCAL_TIMEZONE = '+05:30';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $active_user = Auth::user();
        $tasks = Task::where('user_id', $active_user->id)->get();

        if (count($tasks) > 0) {
            return response(['status' => true, 'data' => TaskResource::collection($tasks)]);
        }
        else {
            return response(['status' => false, 'message' => 'No tasks found'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $active_user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'error' => $validator->errors(), 'message' => 'Validation error']);
        }

        $task = Task::create([
            'user_id' => $active_user->id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date
        ]);

        if ($task) {
            return response(['status' => true, 'data' => new TaskResource($task)], 201);
        }
        else {
            return response(['status' => false, 'data' => new TaskResource($task)], 500);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $active_user = Auth::user();
        $task = Task::where('user_id', $active_user->id)->find($id);

        if ($task) {
            return response(['status' => true, 'data' => new TaskResource($task)], 200);
        }

        else {
            return response(['status' => false, 'message' => 'Not found or forbidden'], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $active_user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'error' => $validator->errors(), 'message' => 'Validation error']);
        }

        $task = Task::where('user_id', $active_user->id)->find($id);
        
        if ($task) {
            $task->update($request->all());
            return response(['status' => true, 'data' => new TaskResource($task), 'message' => 'Task was updated successfully'], 201);
        } 
        else {
            return response(['status' => false, 'error' => 'Not found or forbidden'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $active_user = Auth::user();
        $task = Task::where('user_id', $active_user->id)->find($id);

        if (!$task) {
            return response(['status' => false, 'message' => 'Not found or forbidden'], 403);
        }

        if ($task->delete() > 0) {
            return response(['status' => true, 'message' => 'Task was deleted']);
        }
        else {
            return response(['status' => false, 'message' => 'Error in deleting the task'], 400);
        }
    }

    /**
     * Get task by date today.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTasksToday()
    {
        $active_user = Auth::user();

        $tasks = Task::where([
            'user_id' => $active_user->id,
            ])
            ->whereDate('due_date', Carbon::today($this->LOCAL_TIMEZONE))
            ->get();

            if (count($tasks) > 0) {
                return response(['status' => true, 'data' => TaskResource::collection($tasks)], 200);
            }
            else {
                return response(['status' => false, 'message' => 'No tasks found'], 404);
            }
        
    }

   /**
     * Get task by date tommorow.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTasksTomorrow()
    {
        $active_user = Auth::user();
        $tasks = Task::where([
            'user_id' => $active_user->id,
            ])
            ->whereDate('due_date', Carbon::tomorrow($this->LOCAL_TIMEZONE))
            ->get();

            if (count($tasks) > 0) {
                return response(['status' => true, 'data' => TaskResource::collection($tasks)], 200);
            }
            else {
                return response(['status' => false, 'message' => 'No tasks found'], 404);
            }
    }

       /**
     * Get upcoming tasks after tomorrow.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTasksAfterTomorrow()
    {
        $active_user = Auth::user();
        $tasks = Task::where([
            'user_id' => $active_user->id,
            ])
            ->whereDate('due_date', '>', Carbon::tomorrow($this->LOCAL_TIMEZONE))
            ->get();

            if (count($tasks)) {
                return response(['status' => true, 'data' => TaskResource::collection($tasks)], 200);
            }
            else {
                return response(['status' => false, 'message' => 'No tasks found'], 404);
            }
    }

    /**
     * Get task by any date.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTasksByDate(Request $request)
    {
        $date = $request->date;
        $tasks = Task::where('due_date', new Carbon($date))->get();

        if (count($tasks) > 0) {
            return response(['status' => true, 'data' => TaskResource::collection($tasks)], 200);
        }
        else {
            return response(['status' => false, 'message' => 'No tasks found'], 404);
        }
    }

    public function toggleTask(int $id) {
        $active_user = Auth::user();
        $task = Task::where('user_id', $active_user->id)->find($id);

        if(!$task) {
            return response(['status' => false, 'message' => 'Not found or Forbidden'], 403);
        }

        $completed = $task->completed;
        $task->completed = !$completed;
        $task->save();

        return response(['status' => true, 'data' => new TaskResource($task), 'message' => 'Task status updated successfully'], 200);
    }
}