<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Task::query();
    
        // ✅ فلترة حسب الحالة
        if ($request->status) {
            $query->where('status', $request->status);
        }
    
        // ✅ فلترة حسب الشخص المسؤول
        if ($request->assignee_id) {
            $query->where('assignee_id', $request->assignee_id);
        }
    
        // ✅ فلترة بالتاريخ
        if ($request->has(['from', 'to'])) {
            $query->whereBetween('due_date', [$request->from, $request->to]);
        }
    
        // ✅ لو المستخدم الحالي role = user → يشوف بس التاسكات بتاعته
        if ($request->user()->role === 'user') {
            $query->where('assignee_id', $request->user()->id);
        }
    
        // ✅ نجيب التاسكات مع الـ dependencies
        $tasks = $query->with('dependencies')->get();
    
        // ✅ لو مفيش نتائج
        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found for the given filters , [ Please Try Again ]'
            ], 404);
        }
    
        // ✅ في نتائج
        return response()->json($tasks, 200);
    }
    

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'required|exists:users,id',
            'status' => 'in:pending,completed,canceled',
            'due_date' => 'nullable|date',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::create($request->only('title','description','assignee_id','status','due_date'));

        if ($request->dependencies) {
            $task->dependencies()->attach($request->dependencies);
        }

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load('dependencies')
        ], 201);
    }

    // GET /api/tasks/{id}
    public function show($id, Request $request)
    {
        $task = Task::with('dependencies')->find($id);

        if (!$task) {
            return response()->json(['message' => 'Task is not found , [ Please Try Again ]'], 404);
        }

        if ($request->user()->role === 'user' && $task->assignee_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($task);
    }

    // PUT /api/tasks/{id}
    public function update($id, Request $request)
{
    $task = Task::find($id);

    if (!$task) {
        return response()->json(['message' => 'Task is not found , [ Please Try Again ]'], 404);
    }

    $user = $request->user();

   
    $rules = [
        'status' => 'in:pending,completed,canceled',
        'title' => 'string|max:255',
        'description' => 'nullable|string',
        'assignee_id' => 'exists:users,id',
        'due_date' => 'nullable|date',
        'dependencies' => 'nullable|array',
        'dependencies.*' => 'exists:tasks,id'
    ];

   
    if ($user->role === 'user') {
        if ($task->assignee_id !== $user->id) {
            return response()->json(['message' => 'Sorry you can not update this task , This task is not assigned to you'], 403);
        }
        $rules = ['status' => 'required|in:pending,completed,canceled'];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    
    if (
        $request->status === 'completed' &&
        $task->dependencies()->where('status', '!=', 'completed')->exists()
    ) {
        return response()->json(['message' => 'Sorry , You can not complete this task until all dependent tasks are completed.'], 400);
    }

   
    if ($user->role === 'user') {
        $task->update(['status' => $request->status]);
    } else {
        $task->update($request->only('title', 'description', 'assignee_id', 'status', 'due_date'));
        if ($request->dependencies) {
            $task->dependencies()->sync($request->dependencies);
        }
    }

    
    if ($task->status === 'completed') {
        $dependentTasks = Task::whereHas('dependencies', function ($q) use ($task) {
            $q->where('depends_on_task_id', $task->id);
        })->get();
        

        foreach ($dependentTasks as $dep) {
            if (!$dep->dependencies()->where('status', '!=', 'completed')->exists()) {
                $dep->update(['status' => 'pending']);
            }
        }
    }

    return response()->json([
        'message' => 'Task updated successfully',
        'task' => $task->load('dependencies')
    ]);
}

}
