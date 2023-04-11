<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class remember_token_clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remember_token_clear:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear all user remember_tokens for fresh log in';

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
            User::query()->update(['remember_token' => null]);
            DB::commit();
            $this->log("info", "remember tokens cleared");
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
                    Log::critical($message);
                    break;
                case 'info':
                    Log::info($message);
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
