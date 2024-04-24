<?php

namespace App\Http\Controllers;

use auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Task; // Assuming Task model is located in app/Models

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retrieve all tasks of the authenticated user
        $tasks = auth()->user()->tasks()->latest()->get();

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'required|date',
                'priority' => 'required|in:low,medium,high',
            ]);

            // Return error response if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation Error',
                    'message' => $validator->errors()
                ], 400);
            }

            // Retrieve the authenticated user's ID
            $user_id = auth()->id();


            // Create the task with user_id
            DB::beginTransaction();
            $task = Task::create(array_merge($validator->validated(), ['user_id' => $user_id]));
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Task created successfully',
                'task' => $task
            ], 201);

        } catch (\Exception $e) {
            // Rollback transaction if an exception occurs
            DB::rollBack();

            // Return error response for exceptions
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['task' => $task]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        try {
            // Check if the task belongs to the authenticated user
            if ($task->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'required|date',
                'priority' => 'required|in:low,medium,high',
                'completed' => 'required|boolean',
            ]);

            // Return error response if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation Error',
                    'message' => $validator->errors()
                ], 400);
            }

            // Update the task
            DB::beginTransaction();
            $task->update($validator->validated());
            DB::commit();

            // Return success response
            return response()->json(['message' => 'Task updated successfully', 'task' => $task]);

        } catch (\Exception $e) {
            // Rollback transaction if an exception occurs
            DB::rollBack();

            // Return error response for exceptions
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }


    public function search(Request $request)
    {
        try {
            $query = Task::where('user_id', auth()->id());

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhere('title', 'like', '%' . $searchTerm . '%');
                    $query->orWhere('description', 'like', '%' . $searchTerm . '%');
                    $query->orWhere('due_date', 'like', '%' . $searchTerm . '%');
                    $query->orWhere('priority', 'like', '%' . $searchTerm . '%');
                });
            }

            $tasks = $query->orderBy('created_at')->limit(100)->get();

            return response()->json(['message' => 'List fetched successfully', 'tasks' => $tasks], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }
}
