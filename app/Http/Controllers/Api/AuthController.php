<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utilities\ProxyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    protected $out;
    protected $proxy;

    public function __construct(ProxyRequest $proxy)
    {
        $this->proxy = $proxy;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    public function logout () {}

    public function login(Request $request)
    {
        
        $email = app('request')->__get('email');
        $pw = app('request')->__get('password');
        //$this->out->writeln("request>>>>" . $request);
        //$this->out->writeln("email>>>>" . $email);
        //$this->out->writeln("pw>>>>" . $pw);
        abort_unless(
            ($this->validEmail($email)),
            403,
            json_encode([
                'state' => 'email',
                'message' => 'This email is poorly formated'
            ]),
        );
        //check password
        abort_unless(
            ($pw),
            403,
            json_encode([
                'state' => 'password',
                'message' => 'Password is not provided'
            ]),
        );
        //check user migration
        $user = User::where('email', $email)->first();
        abort_unless(
            $user !== null,
            403,
            json_encode([
                'state' => 'message',
                'message' => 'email does not exists'
            ]),
        );

        abort_unless(
            $user->email_verified_at !== null,
            403,
            json_encode([
                'state' => 'message',
                'message' => "$email  is Active But not Verified! Contact Administrator"
            ]),
        );

        abort_unless(
            Hash::check($pw, $user->password),
            403,
            json_encode([
                'state' => 'password',
                'message' => 'wrong password'
            ]),
        );

        [$jwt, $seconds] = $this->proxy->grantPasswordToken($user->id, $email, $user->section);
        return response([
            'token' => $jwt,
            'expiresIn' => $seconds,
            'user' => $user,
        ], 200);
    }

    function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function Users(Request $request){
        $token = $this->proxy->getTokenFromRequest($request);
        [$bol, $payload] = $this->proxy->verifyPasswordToken($token);
        $input =  $request->all();
        $selection = $input['selection'] ?? '';
        $users = User::where([
            ['section', 'like', '%' . $selection . '%']
        ])->orderBy('created_at', 'DESC')->get()->except(json_decode($payload)->user_id);
        return ['success' => true,
                'users' => $users ];
    }
}
