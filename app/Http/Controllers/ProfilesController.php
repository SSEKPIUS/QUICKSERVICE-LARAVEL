<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Attribute;

class ProfilesController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($user)
    {
        $user = User::find($user);
        return view('home',['user'=>$user,]);
    }
}
