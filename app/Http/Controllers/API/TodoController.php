<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TodoController extends Controller
{
    // public $claims;

    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => []]);
        // $this->claims =  JWTAuth::parseToken()->getPayload();
    }

    public function getClaims()
    {
        return JWTAuth::parseToken()->getPayload();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $claims = $this->getClaims();
        return TodoResource::collection(Todo::where('user_id', $claims->get("sub"))->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TodoRequest $request)
    {
        $claims = $this->getClaims();
        $data = $request->all();
        $data['user_id'] = $claims->get("sub");
        return new TodoResource(Todo::create($data));
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        $claims = $this->getClaims();
        if ($todo->user_id != $claims->get("sub")) {
            return response()->json([
                "status" => false,
                "message" => "you dont have permission",
            ], 403);
        }

        return new TodoResource($todo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TodoRequest $request, Todo $todo)
    {
        $claims = $this->getClaims();
        if ($todo->user_id != $claims->get("sub")) {
            return response()->json([
                "status" => false,
                "message" => "you dont have permission",
            ], 403);
        }

        $todo->update($request->all());

        return response()->json([
            "status" => true,
            "message" => "todo data with id " . $todo->id . " updated successfully",
            "todo" => new TodoResource($todo),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        $claims = $this->getClaims();
        if ($todo->user_id != $claims->get("sub")) {
            return response()->json([
                "status" => false,
                "message" => "you dont have permission",
            ], 403);
        }

        $todo->delete();
        return response()->json([], 204);
    }
}
