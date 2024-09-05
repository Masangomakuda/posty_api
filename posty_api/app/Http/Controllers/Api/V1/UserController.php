<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\V1\UserResource;
use App\http\Resources\V1\UserCollection;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * @group Users
     */

    /**
     * @OA\Get(
     * path="/api/v1/users",
     * summary="Display All users",
     * tags={"Users"},
     * security={ {"sanctum": {} }},
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function index()
    {
        $users = QueryBuilder::for(User::class)
                ->allowedSorts(['name', 'email', 'created_at'])
                ->withCount('posts')
                ->paginate(5);
        return new UserCollection($users);
    }

    /**
     * Display the specified resource.
     * @group Users
     */
    /**
     * @OA\Get(
     * path="/api/v1/users/{id}",
     * summary="Get User ",
     * tags={"Users"},
     * security={ {"sanctum": {} }},
     * @OA\Parameter(name="id",description="User id",required=true,in="path", @OA\Schema(type="integer" )),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function show($id)
    {
        $user = User::withcount('posts')->with('posts')->find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User Not Found',
            ], Response::HTTP_NOT_FOUND);
        }
        return new UserResource($user);
    }
}
