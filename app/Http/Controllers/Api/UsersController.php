<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utilities\ProxyRequest;
use App\Models\User;
use App\Utilities\verifyEmailClass;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    protected $out;
    protected $proxy;
    protected $mail;

    public function __construct(ProxyRequest $proxy, verifyEmailClass $mail)
    {
        $this->proxy = $proxy;
        $this->mail = $mail;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    private function getIDfromReq()
    {
        $id = app('request')->__get('id');
        abort_unless(
            ($id !== null || !is_int($id)),
            403,
            json_encode([
                'state' => 'user_id',
                'message' => 'This user ID is poorly formated'
            ]),
        );
        return $id;
    }

    public function TogglePermissions(Request $request)
    {
        $id = $this->getIDfromReq();
        $user = User::where('id', $id)->first();
        $permission = $user->permission;
        if ($permission == 10) {
            $permission = 5;
        } else {
            $permission = 10;
        }

        DB::beginTransaction();
        try {
            $op = User::where('id', $id)->update(['permission' => $permission]);
            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function DeleteUser(Request $request)
    {
        $id = $this->getIDfromReq();
        DB::beginTransaction();
        try {
            $op =  User::where('id', $id)->delete();
            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function VerifyUser(Request $request)
    {  
        $id = $this->getIDfromReq();
        $op =  $this->mail->sendEmailReminder($id);
        return response([
            'result' => $op
        ], 200);
    }

    public function AuthEmailUserCode(Request $request)
    {
        
        $token = app('request')->__get('token');
        $code = app('request')->__get('code');
        $id = $this->getIDfromReq();
        $op =  $this->mail->verifyEmailCode($id, $code, $token);
        return response([
            'result' => $op
        ], 200);
    }

    public function updateProfPic(Request $request)
    {
        
        $this->validate($request, [
            'file' => 'required|image|mimes:jpg,png,jpeg|max:2048|dimensions:min_width=100,min_height=100,max_width=500,max_height=500',
        ]);
        try {
            $image = base64_encode(file_get_contents($request->file('file')));
            $this->out->writeln("image>>>>" . $image);
            $id = $this->getIDfromReq();
            DB::beginTransaction();
            try {
                $op =  User::where('id', $id)->update(['image' => 'data:image/png;base64,' . $image]);
                DB::commit();
                return response([
                    'result' => $op
                ], 200);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Throwable $th) {
            abort_unless(
                false,
                422,
                json_encode([
                    'state' => 'auth',
                    'message' => 'Service Error::' . $th
                ]),
            );
        }
    }

    public function AllUsers(Request $request)
    {
        
        $users = User::all();

        return [
            'success' => true,
            'users' => $users
        ];
    }
}
