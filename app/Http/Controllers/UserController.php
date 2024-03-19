<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index() {
        try{
            $users = User::all();

            return response()->json(["users"=> $users], 200);
        } catch(\Exception $exception) {
            return response()->json(["message"=>"Error - {$exception->getMessage()}"]);
        }
    }

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
}
