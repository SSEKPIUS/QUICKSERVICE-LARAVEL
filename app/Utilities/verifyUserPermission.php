<?php
namespace App\Utilities;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class verifyUserPermission {

    protected $out;
    public function __construct()
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    public function permission($userID, $path) {
            abort_unless(
                ($userID),
                403,
                json_encode([
                    'state' => 'auth',
                    'message' =>    "Provide User ID"
                ]),
            );
          
            $exception = array("api/getAllMenu", "api/getAllInventoryMenu", "api/getAllInventoryStock", "api/getAllInventoryStock");
            if (in_array($path, $exception)) {
                return true;
            }

            $user = User::findOrFail($userID);
            $perm = $user->permission;
            switch ($perm) {
                case 5:
                    return false;
                    break;
                case 10:
                    return true;
                    break;
                default: false;
            }
    }

    public function authProcessWithSupervisor() {
        $email = app('request')->__get('email');
        $password = app('request')->__get('password');
        abort_unless(
            ($this->validEmail($email)),
            403,
            'This email is poorly formated'
        );
        abort_unless(
            ($password),
            403,
            'Password is not provided'
        );
        $user = User::where('email', $email)->first();
        abort_unless(
            $user !== null,
            403,
            'email does not exists'
        );
        abort_unless(
            $user->section === "SUPERVISOR",
            403,
            "$email  is not a Supervisor"
        );
        abort_unless(
            $user->email_verified_at !== null,
            403,
            "$email  is Active But not Verified! Contact Administrator"
        );
        abort_unless(
            $user->permission > 5,
            403,
            "$email  has limited Permissions"
        );
        abort_unless(
            Hash::check($password, $user->password),
            403,
            'wrong password'
        );
        return  $user->email ;
    }

    function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function authProcessWithSupervisorQAC () {
        $qac = app('request')->__get('qac');
        abort_unless(
            ($qac),
            403,
            'Quick Access Code is not provided'
        );
        $user = User::where('qac', $qac)->first();
        abort_unless(
            $user !== null,
            403,
            'QAC does not exists'
        );
        abort_unless(
            $user->section === "SUPERVISOR",
            403,
            "QAC deoes not belong a Supervisor"
        );
        abort_unless(
            $user->email_verified_at !== null,
            403,
            "$user->email  is Active But not Verified! Contact Administrator"
        );
        abort_unless(
            $user->permission > 5,
            403,
            "$user->email  has limited Permissions"
        );
        return  $user->email ;
    }

}



// public function check_sup_cred($supervisorQAC, $supervisorPWD)
    // {
    //     //check QAC
    //     abort_unless(
    //         ($supervisorQAC),
    //         403,
    //         json_encode([
    //             'state' => 'QAC',
    //             'message' => "Supervisor's QAC is not provided"
    //         ]),
    //     );
    //     //check password
    //     abort_unless(
    //         ($supervisorPWD),
    //         403,
    //         json_encode([
    //             'state' => 'Password',
    //             'message' => "Supervisor's Password is not provided"
    //         ]),
    //     );
    //     //check user migration
    //     $user = User::where(
    //         function ($query) use ($supervisorQAC) {
    //             $query->where('qac', '=', $supervisorQAC)
    //                 ->Where('section', '=', 'SUPERVISOR');
    //         }
    //     )->first();
    //     abort_unless(
    //         $user !== null,
    //         403,
    //         json_encode([
    //             'state' => 'message',
    //             'message' => 'QAC does not exists'
    //         ]),
    //     );

    //     abort_unless(
    //         $user->email_verified_at !== null &&  $user->permission == 10,
    //         403,
    //         json_encode([
    //             'state' => 'message',
    //             'message' => "$user->name  is Active But not Verified or lacks Permissions! Contact Administrator"
    //         ]),
    //     );

    //     abort_unless(
    //         Hash::check($supervisorPWD, $user->password),
    //         403,
    //         json_encode([
    //             'state' => 'password',
    //             'message' => 'wrong password'
    //         ]),
    //     );

    //     return true;
    // }