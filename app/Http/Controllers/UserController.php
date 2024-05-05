<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for user management"
 * )
 */
class UserController extends Controller
{
    /**
     *  @OA\Get(
     *      path="/api/users",
     *      tags={"Users"},
     *      summary="Get a list of users",
     *      description="Returns all existing users",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="users",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Test"),
     *                      @OA\Property(property="email", type="string", example="test1@example.com"),
     *                      @OA\Property(property="email_verified_at", type="string", example="null"),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time")
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function index() {
        try{
            $users = User::all();

            return response()->json(["users"=> $users], 200);
        } catch(\Exception $exception) {
            return response()->json(["message"=>"Error - {$exception->getMessage()}"]);
        }
    }

    /**
     *  @OA\Get(
     *      path="/api/user/{id}",
     *      tags={"Users"},
     *      summary="Get user info by ID",
     *      description="Returns a user based on ID",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the user",
     *          @OA\Schema(type="string"),
     *          example=1
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Test"),
     *                  @OA\Property(property="email", type="string", example="test1@example.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example=null),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Invalid id."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="There is no user with that id."
     *              )
     *          )
     *      )
     * )
     */
    public function show(string $id) {
         if(!$id){
            return response()->json(["message"=>"Invalid id."], 400);
        }

        try{
            $user = User::findOrFail($id);

            return response()->json(["user"=>$user], 200);
        } catch(\Exception $_){
            return response()->json(["message"=>"There is no user with that id."], 400);
        }
    }

    /**
    *  @OA\Get(
    *      path="/api/user",
    *      tags={"Users"},
    *      summary="Get logged in user info",
    *      description="Returns a user info based on provided API token",
    *      security={{"bearerAuth": {}}},
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(
    *                  property="user",
    *                  type="object",
    *                  @OA\Property(property="id", type="integer", example=1),
    *                  @OA\Property(property="name", type="string", example="Test"),
    *                  @OA\Property(property="email", type="string", example="test1@example.com"),
    *                  @OA\Property(property="email_verified_at", type="string", example=null),
    *                  @OA\Property(property="created_at", type="string", format="date-time"),
    *                  @OA\Property(property="updated_at", type="string", format="date-time")
    *              )
    *          )
    *      )
    *  )
    */
    public function loggedInUser(Request $request)
    {
        return response()->json($request->user());
    }
}
