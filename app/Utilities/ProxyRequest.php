<?php
namespace App\Utilities;

use PHPUnit\TextUI\Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Cookie\CookieValuePrefix;
use App\Http\Middleware\EncryptCookies;

class ProxyRequest
{
    protected $out;
    protected $ed;
    public function __construct(encryptDecrypt $ed)
    {
        $this->ed = $ed;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    //not much happens in this method, we are just setting the parameters needed for Passport "password grant" and make POST request.
    public function grantPasswordToken(string $user_id, string $email, string $section)
    {
        $params = array(
            'user_id' => $user_id,
            'email' => $email,
            'section' => $section,
        );

        return $this->makePostRequest($params, $email);
    }
    public function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }

    /* this is the key method of this class.
        We are setting client_id and client_secret from the config, and we are merging additional parameters that are passed as argument
        Then we are making internal POST request to the Passport routes with the needed parameters
        We are json decoding the response
        Set the httponly cookie with refresh_token
        Return the response */
    public function makePostRequest (array $params, string $email) {
        $seconds = 3600;
        $params = array_merge([
            # Issuer (the token endpoint)
            'iss' => 'https://' . $_SERVER['PHP_SELF'],
            # Client ID (this is a non-standard claim)
            'cid' => $this->getIp(),
            'scope' => 'read write',
            # Issued At
            'iat' => time(),
            # Expires At
            'exp' => time() +  $seconds, // Valid for 1 minute
        ], $params);
        // Create the token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        // Create the token payload
        $payload = json_encode($params);
        // Encode Header
        $base64UrlHeader = base64_encode($header);
        // Encode Payload
        $base64UrlPayload = base64_encode($payload);
        // Create Signature Hash
        $signature = $this->ed->encryptor('encrypt', $base64UrlHeader . "." . $base64UrlPayload);
        // Encode Signature to Base64Url String
        $base64UrlSignature = base64_encode($signature);
        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        $this->setHttpOnlyCookie($jwt, $seconds, $email);
        return [$jwt,$seconds];
    }

    // set the httponly cookie with refresh_token in the response.  
    protected function setHttpOnlyCookie(string $refreshToken, int $seconds, string $email)
    {
        cookie()->queue(
            $email,
            $refreshToken,
            ($seconds / 60), // minutes
            null,
            null,
            false,
            true // httponly
        );
    }

    public function verifyPasswordToken(string $token)
    {
        // $this->out->writeln('token-verify>>>>'. $token);

        $bol = false;
        $payload = null;
        try {
            # Note: You must provide the list of supported algorithms in order to prevent 
            # an attacker from bypassing the signature verification. See:
            # https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/
            list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode(".", $token);
            // decode Signature from Base64Url String
            $signature = base64_decode($base64UrlSignature);
            $payload = base64_decode($base64UrlPayload);
            $header = base64_decode($base64UrlHeader);
            //verify signature
            $signature = $this->ed->encryptor('decrypt', $signature);
            abort_unless(
                $signature !== null,
                401,
                json_encode([
                    'state' => 'auth',
                    'message' => 'Cant validate Authentication key'
                ]),
            );
            list($base64UrlHeaderEnc, $base64UrlPayloadEnc) = explode(".", $signature);
           
            abort_unless(
                $base64UrlHeaderEnc === $base64UrlHeader,
                401,
                json_encode([
                    'state' => 'auth',
                    'message' => 'Your Authentication key Mismatch'
                ]),
            );
            $bol = true;
        } catch (Exception $e) {
            $this->out->writeln($e);
        }
        return  [$bol, $payload];
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    function getTokenFromRequest(Request $request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = CookieValuePrefix::remove($this->encrypter->decrypt($header, $this->serialized()));
            } catch (DecryptException $e) {
                $token = '';
            }
        }

        return $token != null ? $token : '';
    }
    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    public static function serialized()
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
}
