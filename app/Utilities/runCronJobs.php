<?php
namespace App\Utilities;

use App\Console\Commands\create_reports;
use App\Console\Commands\remember_token_clear;
use App\Console\Commands\signout_guests_rooms;

class runCronJobs
{
    protected $CreateReports;
    protected $RememberToken;
    protected $SignOutGuests;
    public function __construct()
    {
        $this->RememberToken = new remember_token_clear;
        $this->SignOutGuests = new signout_guests_rooms;
        $this->CreateReports = new create_reports;
    }

    public function run()
    {
        $this->RememberToken->handle(); // clear remember tokens
        $this->SignOutGuests->handle(); // sign out guests
        $this->CreateReports->handle(); // create reports
        return true;
    }

}