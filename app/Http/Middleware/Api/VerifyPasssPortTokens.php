<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use App\Utilities\ProxyRequest;
use App\Utilities\verifyUserPermission;
use Error;
use Illuminate\Support\Facades\Session;

class VerifyPasssPortTokens
{
    protected $out;
    protected $proxy;
    protected $perm;
    public function __construct(ProxyRequest $proxy, verifyUserPermission $perm )
    {
        $this->proxy = $proxy;
        $this->perm = $perm;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //$this->out->writeln("request>>>>" . $request);
        // $this->out->writeln("path>>>>" . $request->path());
        $err = '';
        try {
            $exception = array(
                "api/web-auth",
                'api/getPaginatedReceipts',
                'api/getPaginatedInventoryLogs',
                'api/getPaginatedOrders',
                'api/api-Users',
                'api/getGuestsrooms',
                'api/getSteamSaunaMassage',
                'api/getMenuBar',
                'api/getMenuKitchen',
                'api/searchSortGuests',
                'api/searchSteamSaunaMassagePaginated',
                'api/stockreportlist',
                'api/stockreportlist_download',
                'api/reportsettings',
                'api/api/auth/logout'
            );

            if (in_array($request->path(), $exception)) {
                return $next($request);
            } else {
                $token = $this->proxy->getTokenFromRequest($request);
                [$bol, $payload] = $this->proxy->verifyPasswordToken($token);
                $payload = json_decode($payload);
                $userID = $payload->user_id;
                //Session::put('userID', $userID);
                $request->session()->put('userID', $userID);
                $perm = $this->perm->permission($userID, $request->path());
                if(!$perm) throw new Error('You Need Permision from Admin to use');
                if(!$bol) throw new Error('You are not authenticated to this service');
                return $next($request);
            }
        } catch (\Throwable $th) {
            $this->out->writeln("th>>>>" . $th);
            abort_unless(
                false,
                403, //403 Forbidden
                "You Need Permision from Admin to use or You are not authenticated to this service"
            );
        }
       
    }
}
