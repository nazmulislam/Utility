<?php
declare(strict_types=1);

namespace  NazmulIslam\Utility\Authentication;

use Firebase\JWT\JWT;
use App\Models\App\User;
use NazmulIslam\Utility\Logger\Logger;

/**
 * Class to handle authentication activities
 * Class Authentication
 * @package App\Domain\Authentication
 */
class Authentication
{

    private $user;
   
    
    public function setUser(User $user):void
    {
        $this->user = $user;
    }
    /**
     * Creates the access token
     */
    public function createAccessToken(int $expiredAt):string {
        
        $key = $_ENV['ACCESS_TOKEN_SECRET'];
        $issuer =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        Logger::debug($issuer);
        $issuedAt = time();
        
        //Time until access token expires 15 minutes
        //$expiredAt = $issuedAt + ($minutes * $seconds);
        $payload = array(
            "iss" => $issuer,
            "iat" => $issuedAt,
            "exp" => $expiredAt,
            "userId" => $this->user->user_id,
            
            "userType" => intval($this->user->user_type),
            "fullname" => $this->user->first_name . ' ' . $this->user->last_name,
            "firstname" => $this->user->first_name,
            "lastname" => $this->user->last_name,
        );
        
        $jwt =  JWT::encode($payload, $key,'HS256');
        return $jwt;
    }

    /**
     * Creates the refresh token
     */
    public function createRefreshToken(int $expiredAt):string {
        $key = $_ENV['REFRESH_TOKEN_SECRET'];
        $issuer = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $issuedAt = time();
        $seconds = 60;
        $minutes = 60;
        $hours = 24;
        $days = 7;
        // Logger::debug('createRefreshtoken ->', [$this->user]);
        //Time until refresh token expires 1 week
        // $expiredAt = $issuedAt + ($minutes * $seconds * $hours * $days);
        $payload = array(
            "iss" => $issuer,
            "iat" => $issuedAt,
            "exp" => $expiredAt,
            "userId" => $this->user->user_id,
            "tokenVersion" => $this->user->refresh_token_count,
            
            "userType" => intval($this->user->user_type),
            "fullname" => $this->user->first_name . ' ' . $this->user->last_name,
            "firstname" => $this->user->first_name,
            "lastname" => $this->user->last_name,
            
        );
        $jwt =  JWT::encode($payload, $key,'HS256');
        return $jwt;
    }

    /**
     * Sets refresh token in httponly cookie.
     */
    public function sendRefreshToken() {
        
   
                
                //setcookie(name:'jid',value:$this->createRefreshToken(), expires_or_options:'',path:'/',domain:'',secure:true, httponly:true)
//        setcookie('jid',$this->createRefreshToken(), [
//           'httponly' => true,
//           'secure' => true,
//           'SameSite' => 'None' 
//        ]);
    }
}
