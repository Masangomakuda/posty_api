<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\RegisterRequest;
use function Laravel\Prompts\password;

class AuthController extends Controller
{
    /**
     * Login
     *   
     *
     * This endpoint is used to login a user to the system.
     *
     * @bodyParam email string required Example: ian@gmail.com
     * @bodyParam password string required Example: 12345678
     *
     * @response scenario="Successful Login" {
     * "message": "User Login Successfully",
     * "access_token": "8|MgowQLkdpShwrb8AI9j1YAGmwnDjAOeE17XrP5nb",
     * "token_type": "Bearer"
     * }
     *
     * @response 401 scenario="Failed Login"{
     * "message": "Invalid login credentials"
     * }
     * @group Auth
     */

     /**
     * @OA\Post(
     * path="/api/login",
     *   tags={"Auth"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\Parameter(name="email",in="query",required=true,@OA\Schema(type="string")),
     *   @OA\Parameter(name="password",in="query",required=true, @OA\Schema(type="string") ),
     *   @OA\Response(response=200,description="Success",
     *   @OA\MediaType(mediaType="application/json",)),
     *   @OA\Response(response=401, description="Unauthenticated" ),
     *   @OA\Response(response=400,description="Bad Request" ),
     *   @OA\Response(response=404,description="not found"),
     *   @OA\Response(response=403,description="Forbidden")
     *)
     **/


    public function login(LoginRequest $request)
    {
        $userdata = $request->validated();
        if (!Auth::attempt($userdata)) {
            return response()->json([
                'message' => 'No User Found'
            ], 401);
        }

        $user = User::where('email', $userdata['email'])->first();

        // dd($user->tokens);
        /**
         *limit tokens per user  
         */
        if ($user->tokens->count() > 0) {
            return response()->json([
                'user' => $user->name,
                'message' => 'Successfully Logged In',


            ]);
        } else {
            return response()->json([
                'user' => $user->name,
                'message' => 'Successfully Logged In',
                'token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer Token',

            ]);
        };
    }


    /**
     *  @group Auth
     */

     
     /**
     * @OA\Post(
     * path="/api/register",
     *   tags={"Auth"},
     *   summary="Register",
     *   operationId="Register",
     *
     *  @OA\Parameter(name="name",in="query",required=true,@OA\Schema(type="string")),
     *   @OA\Parameter(name="email",in="query",required=true,@OA\Schema(type="string")),
     *   @OA\Parameter(name="password",in="query",required=true, @OA\Schema(type="string") ),
     *   @OA\Response(response=200,description="Success",
     *   @OA\MediaType(mediaType="application/json",)),
     *   @OA\Response(response=401, description="Unauthenticated" ),
     *   @OA\Response(response=400,description="Bad Request" ),
     *   @OA\Response(response=404,description="Not found"),
     *   @OA\Response(response=403,description="Forbidden")
     *)
     **/

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }


    /**
     *  @group Auth
     */

   /**
 * Logout
 * @OA\Post (
 *     path="/api/logout",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(  @OA\Property(),example={} )
 *         )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\JsonContent(
 *              @OA\Property(property="meta", type="object",
 *                  @OA\Property(property="code", type="number", example=200),
 *                  @OA\Property(property="status", type="string", example="success"),
 *                  @OA\Property(property="message", type="string", example="Successfully logged out"),
 *              ),
 *              @OA\Property(property="data", type="object", example={}),
 *          )
 *      ),
 *      @OA\Response(response=401, description="Invalid token",)),
 *      security={{"bearerAuth": {}},},
 * )
 */

    public function logout(Request $request)
    {
        // Auth::user()->tokens()->delete();
      
        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return [
                'message' => 'Succesfully Logged out'
            ];
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
          
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
      
       
    }
}
