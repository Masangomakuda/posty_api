<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Like;
use App\Models\Post;
use App\Mail\PostLiked;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Requests\StorePostRequest;
use App\http\Resources\V1\PostResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdatePostRequest;
use App\http\Resources\V1\PostCollection;
use App\Jobs\ProcessLikedPost;
use Exception;
use PhpParser\Node\Stmt\TryCatch;

class PostController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/v1/posts",
     * security={ {"sanctum": {} }},
     * summary="Get a list of all Posts",
     * tags={"Posts"},
     * @OA\Parameter( name="filter[user_id]", in="query",description="Filter posts by User id",required=false, @OA\Schema(type="integer") ),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function index()
    {

        $posts = QueryBuilder::for(Post::orderBy('created_at', 'desc'))
                ->allowedFilters('user_id')
                ->with('user','likes')
                ->withCount('likes')
                ->paginate(50);
        return new PostCollection($posts);
    }

    /**
     * @OA\Post(
     * path="/api/v1/posts",
     * tags={"Posts"},
     * summary="Create a new post",
     * security={ {"sanctum": {} }},
     *  *      @OA\RequestBody( required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema( 
     *                      @OA\Property( property="post", type="string",description="New post", ),
     *                      @OA\Property(property="image",type="file",description="image post", ),
     *                      required={"post"}))
     *                      ),
     * @OA\Response(response=201,description="Post Created"),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response="422", description="Validation errors"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $validated['image'] = $request->file('image')->store('uploads', 'public');
        $post = Post::create($validated + ['user_id' => auth()->id() ]);

        if ($post) {
            return response()->json([
                'data' => new PostResource($post),
                'message' => 'Post Succesfully Created',
            ], Response::HTTP_CREATED);
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/posts/{id}",
     * summary="Display a specified  Post",
     * tags={"Posts"},
     * security={ {"sanctum": {} }},
     * @OA\Parameter(name="id",description="Post id",required=true,in="path", @OA\Schema(type="integer" )),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function show($id)
    {
        $post = Post::with('user')->find($id);
        if (!$post) {
            return response()->json([
                'message' => 'Post Not Found',
            ], Response::HTTP_NOT_FOUND);
        }
        return new PostResource($post);
    }

    /**
     * @OA\Put(
     * path="/api/v1/posts/{id}",
     * summary="Update Existing Post",
     * security={ {"sanctum": {} }},
     * tags={"Posts"},
     *    @OA\Parameter(name="id",  description="Project id", required=true, in="path",  @OA\Schema( type="integer" ) ),
     *      @OA\RequestBody( required=true,
     *         @OA\MediaType(mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema( @OA\Property( property="post", type="string",description="New post", ),required={"post"}))
     *                      ),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=201,description="Post Created"),
     * @OA\Response(response="422", description="Validation errors"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=405,description="Method not Allowed"),
     * )
     */
    public function update(UpdatePostRequest $request, $id)
    {

        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'message' => 'Post Not Found',
            ], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('update', $post);
        $post->update($request->validated());
        return response()->json([
            'data' => new PostResource($post),
            'message' => 'Post Succesfully Updated',
        ], Response::HTTP_CREATED);
    }


    /**
     * @OA\Delete(
     * path="/api/v1/posts/{id}",
     * summary="Removes a Post only if you own it",
     * tags={"Posts"},
     * security={ {"sanctum": {} }},
     * @OA\Parameter( name="id",description="Post id",  required=true,in="path", @OA\Schema(type="integer")),
     * @OA\Response(response=200,description="Post deleted Successfully"),
     * @OA\Response(response=204,description="No Content"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=404,description="Post Not Found"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=500,description="Server Error")
     * 
     * )
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post Not Found',
            ], Response::HTTP_NOT_FOUND);
        }
        Gate::authorize('delete', $post);
        $post->delete();
        return response()->json([
            'message' => 'Post Succesfully Deleted',
        ], Response::HTTP_NO_CONTENT);
    }

 
    /**
     * @OA\Get(
     * path="/api/v1/posts/search/{searchword}",
     * summary="Search for a Post",
     * tags={"Posts"},
     * security={ {"sanctum": {} }},
     * @OA\Parameter(name="searchword", in="path", description="post",required=true,* @OA\Schema(type="string")),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function search($searchword)
    {
        return new PostCollection(Post::with('user')->where('post', 'like', '%' . $searchword . '%')->paginate(5));
    }


        /**
     * @OA\Post(
     * path="/api/v1/posts/like/{id}",
     * summary="Like a Post",
     * tags={"Posts"},
     * security={ {"sanctum": {} }},
     * @OA\Parameter(name="id", in="path", description="post id",required=true,* @OA\Schema(type="integer")),
     * @OA\Response(response=200,description="Success"),
     * @OA\Response(response=401,description="Unauthenticated"),
     * @OA\Response(response=403,description="Forbidden"),
     * @OA\Response(response=404,description="Not Found"),
     * @OA\Response(response=500,description="Server Error"),
     * )
     */
    public function like($id)
    {
        $like = Like::where('post_id',$id)->where('user_id',auth()->id(),)->first();
        $post = Post::findOrFail($id);
        if ($like) {
            $like->delete();
        } else {
            try {
                Like::create([
                    'post_id' => $id,
                    'user_id' => auth()->id(),
                ]);
            
                ProcessLikedPost::dispatch($post,Auth::user());
            } catch (Exception $e) {
              
               return response()->json([
                // 'message' => $e->getMessage(),
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
           
        }
       
    }
}
