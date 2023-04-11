<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\hotel_guests;
use App\Models\hotel_rooms;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class signout_guests_rooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signout_guests_rooms:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sign out guests who paid, automatically , when the time runs out ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $guests = hotel_guests::where([
                ['status','=', "active"],
                ['paid','=', 1],
                ['checkIn', '<>', '']
                ])->get();
            foreach ($guests as $guest) {
                $days = $guest->rdays;
                $DateNow =  now();
                $Datecheckin = $guest->checkIn;
                $diff_in_days = Carbon::parse($DateNow)->diffInDays($Datecheckin);  
                if ($diff_in_days > $days) {
                    hotel_guests::where('id', $guest->id)->update(['leaveDate' => now(), 'status' => 'inactive']);
                    hotel_rooms::where('roomNo', $guest->roomNo)->update(['updated_at' => now(), 'occupied' => false]);
                    $this->log("info", "Sucessfully Checked out Guest: Name:" . $guest->fullname . " IDType:" . $guest->idType . " IDNumber:" . $guest->idNum . " CheckedIn:" . $guest->checkIn . " Days:" . $guest->rdays );
                }     
            } 
            $this->log("info", "Sucessfully Ran Checked OutGuests");
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $this->log("critical", $th);
        }
        return 0;
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
