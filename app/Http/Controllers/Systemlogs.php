<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Receipts\ReceiptsController;
use Illuminate\Support\Facades\Storage;

class Systemlogs extends Controller
{
    protected $receiptsController;
    public function __construct(ReceiptsController $receiptsController)
    {
        $this->receiptsController = $receiptsController;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
        //$this->out->writeln("id>>>>" . app('request')->__get('id'));
    }
    public function getPaginatedSystemLogs(Request $request)
    {
        $result = [];
        $logs = collect(Storage::disk('public_logs')->allFiles())->reverse();

        $startStack = true;
        foreach ($logs as $log) {
            if (substr_compare($log, '.log', -strlen('.log')) === 0) {
              $file = Storage::disk('public_logs')->path($log);
              $myfile = fopen($file, "r") or die("Unable to open file!");
              //$read = fread($myfile,filesize($file));
              //array_push($result, $read);
              $stack = "";
              while(!feof($myfile)) {
                $read = fgets($myfile);
                if (str_starts_with($read , "[".now()->year)){
                    if ($startStack === false && strlen($stack) > 0) array_push($result,  $stack);
                    $stack =  $read;
                    $startStack = true;
                } else {
                    $startStack = false;
                    $stack =  $stack . $read;
                }
              }
              if ( $startStack === false && strlen($stack) > 0) array_push($result,  $stack);
              $startStack = false;
              fclose($myfile);
            }
        }
        $result =  $this->receiptsController->paginate(collect($result ), 15, null, [], $request);
        return response([
            'result' => true,
            'logs' => $result
        ], 200);
    }
}
