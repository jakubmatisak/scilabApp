<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API Endpoints for authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register user",
     *     description="Registers new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", description="Full name", example="Test"),
     *             @OA\Property(property="email", type="string", description="Email address", example="test@example.com"),
     *             @OA\Property(property="password", type="string", description="Password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="User Created Successfully"),
     *              @OA\Property(property="token", type="string", example="9|iwvR6QMeX1s8a83wZFhxvApSbgu7Ee8CJs9N7nR4b05f26e3")
     *          )
     *     )
     * )
     */
    public function createUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'User Created Successfully',
                'token' => $user->createToken("AUTH TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */

     /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     description="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", description="Email address", example="test@example.com"),
     *             @OA\Property(property="password", type="string", description="Password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="User Logged In Successfully"),
     *              @OA\Property(property="token", type="string", example="9|iwvR6QMeX1s8a83wZFhxvApSbgu7Ee8CJs9N7nR4b05f26e3")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Wrong credentials",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Wrong Email or Password"),
     *          )
     *     )
     * )
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 400);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'message' => 'Wrong Email or Password.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("AUTH TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
