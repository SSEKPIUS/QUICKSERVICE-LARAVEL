<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\reportemails;
use DateTime;
use DateInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class verifyEmailClass
{
    protected $out;
    protected $enc;

    public function __construct(encryptDecrypt $enc)
    {
        $this->enc = $enc;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }
    /**
     * Send an e-mail reminder to the user.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function sendEmailReminder($id)
    {
        abort_unless(
            ($id),
            403,
            json_encode([
                'state' => 'auth',
                'message' => ' "Provide User ID"'
            ]),
        );

        $user = User::findOrFail($id);
        $userID = $user->id;
        $tokenValue = rand(1000, 9999);
        $minutes = 30;

        try {
                Mail::send('emails.verifyEmailreminder', ['user' => $user, 'code' => "{$tokenValue}  Expires in {$minutes} minutes"], function ($m) use ($user) {
                    $m->from('no-reply@mqs.com', "MacsedoQuickService Mail Verification`"/*Header*/);
                    $m->to($user->email, $user->name)->subject('Verify Email!'/*Title*/);
                });
                return $this->setToken($userID, $tokenValue, $minutes);
        } catch (Exception $th) {
            $this->out->writeln("Error>>>>" . $th);
                abort_unless(
                    (false),
                    403,
                    "The mail server could not deliver mail to {$user->email}",
                );
        }  
    }

    public function sendEmailReports( $collection,  $doughnut,  $line_labels,  $line_rooms,  $line_sauna_masage,  $line_bar_kitchen){
        $data["title"] = "Stock, Sales Reports";
        $data["body"] = "Reports-". (Carbon::now()->subDays(1))->format("Y-m-d");
        $files = [... $collection->files];
        $emails = reportemails::get();
        if ($emails != null){
            foreach ($emails as $email_){
                try {
                    $data["email"] = $email_->email;
                    $data["name"] = $email_->email;
                    $data["doughnut"] = $doughnut;
                    $data["line_labels"] = $line_labels;
                    $data["line_rooms"] = $line_rooms;
                    $data["line_sauna_masage"] = $line_sauna_masage;
                    $data["line_bar_kitchen"] = $line_bar_kitchen;
                    Mail::send(
                        'emails.sendEmailReports', 
                        [
                            'usermail' => $data["email"], 
                            'body' => $data["body"], 
                            'doughnut' => $data["doughnut"], 
                            'line_labels' => $data["line_labels"],
                            'line_rooms' => $data["line_rooms"],
                            'line_sauna_masage' => $data["line_sauna_masage"],
                            'line_bar_kitchen' => $data["line_bar_kitchen"]
                        ], 
                        function($message)use($data, $files) {
                        $message->to($data["email"], $data["name"])
                                ->subject($data["title"]);
                        foreach ($files as $file){
                            if ($file != null)  $message->attachData($file->file, $file->name, ['mime' => 'application/pdf']);
                        }  
                    });
                    $this->log("info",  "Successfully send Mail Report on " . now() . " to " . $data["email"]);
                } catch (\Throwable $th) {
                    $this->log("critical",  $th);
                }
            }  
        }
    }

     public function verifyEmailCode($id, $code, $token)
    {
        abort_unless(
            ($id),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>  "Provide User ID"
            ]),
        );
        abort_unless(
            ($code),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>   "Provide User CODE"
            ]),
        );
        abort_unless(
            ($token),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>   "Provide User TOKEN"
            ]),
        );

        [ $ID, $tokenValue, $cenvertedTime ] = $this->getTokenValues($token);
        abort_unless(
            ($id == $ID),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>   "Invalid User"
            ]),
        );
        abort_unless(
            ($code == $tokenValue),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>   "Invalid CODE, Use one sent to your Email"
            ]),
        );
        abort_unless(
            (intval( $this->dateDiff($cenvertedTime) ) > 1 ),
            403,
            json_encode([
                'state' => 'auth',
                'message' =>   "CODE is Expired"
            ]),
        );

        $op =  User::where('id', $id)->update(['email_verified_at' => now()]);
        return response("success:$op", 200)->header('Content-Type', 'text');
    }

    function dateDiff($date)
    {
        $mydate = new DateTime(date("Y-m-d H:i:s"));
        $difference = $mydate->diff(new DateTime($date));
        $diffInSeconds = $difference->s; //45
        $diffInMinutes = $difference->i; //23
        $diffInHours   = $difference->h; //8
        $diffInDays    = $difference->d; //21
        $diffInMonths  = $difference->m; //4
        $diffInYears   = $difference->y; //1
        $this->out->writeln("diffInMinutes>>>>" . $diffInMinutes);
        return $diffInMinutes;
    }

    public function setToken($userID, $tokenValue, $minutes)
    {
        $startTime = date("Y-m-d H:i:s");
        $cenvertedTime = date('Y-m-d H:i:s', strtotime("+$minutes minutes", strtotime($startTime)));
        $token = "$userID?$tokenValue?$cenvertedTime";
        $token= $this->enc->encryptor('encrypt', $token);
        return response($token, 200)->header('Content-Type', 'text');
    }

    public function getTokenValues($token)
    {
        $userID = null;
        $tokenValue = null;
        $cenvertedTime  = null;
        try {
            $token = $this->enc->encryptor('decrypt', $token);
            //$this->out->writeln("token>>>>" . $token);
            $pieces = explode("?", $token);
            $userID = $pieces[0];
            $tokenValue = $pieces[1];
            $cenvertedTime  = $pieces[2];
        } catch (\Throwable $th) {
            $this->out->writeln("Error>>>>" . $th);
            abort_unless(
                (false),
                403,
                json_encode([
                    'state' => 'auth',
                    'message' =>    "Cannot Read User TOKEN"
                ]),
            );
        }
        return [$userID, $tokenValue, $cenvertedTime];
    }

    
    private function log($mode, $message){
        try {
            switch ($mode) {
                case 'critical':
                    Log::critical( $message);
                    break;
                case 'info':
                    Log::info( $message);
                    break;
                default:
                    # code...
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

}