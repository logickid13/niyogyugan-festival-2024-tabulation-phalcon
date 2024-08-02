<?php
declare(strict_types=1);
// use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
// use Phalcon\Di\FactoryDefault as Di;

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

class AuthController extends \Phalcon\Mvc\Controller
{

    public function beforeExecuteRoute(Dispatcher $dispatcher) 
    {
      $arr = array();
      $cookies_arr = array();
      $accepted_routes = [
        "updateProfilePic",
        "updatePassword",
      ];
      $action_name = $dispatcher->getActionName();

      if (in_array($action_name, $accepted_routes)) {
          $http_auth_header = $this->request->getHeader('HTTP_AUTHORIZATION');

          $csrf_header = $this->request->getHeader('x-csrf-token');
          $cookie_header = $this->request->getHeader('Cookie');
          $headerCookies = explode('; ', $cookie_header);
          
          foreach($headerCookies as $itm) {
              list($key, $val) = explode('=', $itm, 2);
              $cookies_arr[$key] = $val;
          }

          $cookie_value = urldecode($cookies_arr['XSRF-TOKEN']);

          if (preg_match('/Bearer\s(\S+)/', $http_auth_header, $matches)) {
              // alternately '/Bearer\s((.*)\.(.*)\.(.*))/' to separate each JWT string
              $actual_token =  $matches[1];
          }

          $session_actual_token = $this->session->get("jwt_access_token");
          $permission = $this->session->get("permission");
          $decoded_permission = json_decode($permission);

          // check if account has permission
          if ($action_name == "updateProfilePic") {
            if (!in_array(1.01, $decoded_permission)) {
                $this->view->disable();
                $this->response->setStatusCode(403, 'Forbidden');
                $arr[] = array('status' => 'Forbidden', 'code' => 403);
                $this->response->setJsonContent($arr);
                $this->response->send(); 
                return false;
            }
          } else if ($action_name === "updatePassword") {
            if (!in_array(1.02, $decoded_permission)) {
                $this->view->disable();
                $this->response->setStatusCode(403, 'Forbidden');
                $arr[] = array('status' => 'Forbidden', 'code' => 403);
                $this->response->setJsonContent($arr);
                $this->response->send(); 
                return false;
            }
          } else if ($csrf_header != $cookie_value) { // if somehow local storage is deleted(tokens, logged_status), but still logged in on backend(session exist)
              $this->view->disable();
              $this->response->setStatusCode(403, 'Forbidden');
              $arr[] = array('status' => 'Forbidden', 'code' => 403);
              $this->response->setJsonContent($arr);
              $this->response->send(); 
              return false;
          } else if (empty($actual_token) || $actual_token !== $session_actual_token) {
              $this->view->disable();
              $this->response->setStatusCode(403, 'Forbidden');
              $arr[] = array('status' => 'Forbidden', 'code' => 403);
              $this->response->setJsonContent($arr);
              $this->response->send(); 
              return false;
          } else {
              $audience = $this->session->get('jwt_audience');
              $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
              $issued = $now->getTimestamp();
              $notBefore = $now->modify('-1 minute')->getTimestamp();
              $expires = $now->getTimestamp();
              $id = $this->session->get('acc_uid');
              $issuer = $this->session->get('jwt_issuer'); 
              $fullname = $this->session->get('acc_name');

              $signer = new Hmac('sha512');
              $passphrase = $this->session->get('jwt_passphrase');

              $parser = new Parser();
              $tokenObject = $parser->parse($actual_token);

              // Check Refresh Token Validity
              try {
                  $validator = new Validator($tokenObject, 100); // allow for a time shift of 100
                  $validator
                      ->validateAudience($audience)
                      ->validateExpiration($expires)
                      ->validateId($id)
                      ->validateIssuedAt($issued)
                      ->validateIssuer($issuer)
                      ->validateNotBefore($notBefore)
                      ->validateSignature($signer, $passphrase)
                  ;
                  return true;
              } catch (\Exception $ex) {
                  $this->view->disable();
                  $this->response->setStatusCode(401, 'Unauthorized');
                  $arr[] = array("code" => $ex->getCode(), "message" => $ex->getMessage());
                  $this->response->setJsonContent($arr);
                  $this->response->send(); 
                  return false;
              } 

          }

      }
      return true;
    } 

    public function indexAction()
    {

    }

    public function shitAction()
    {
        $this->view->disable();

        // $this->session->set('login_attempt', (int) 0);

        // if ($this->session->has('login_attempt') == false) {                 
        //     echo "no_session";
        // } else {
        //     echo $this->session->get('login_attempt');
        // }

        // $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));

        // $string = 'fuck';
        // $this->cookies->set(
        //     "phalcon-cookie",
        //     $string,
        //     (int) $tomorrow->format('U'),
        //     "/",
        //     false,
        //     "",
        //     true
        // );

        // $this->cookies->send();

        // // $encrypted_key = $this->crypt->encrypt($string);
        // echo $encrypted_key." CHARACTER LENGTH: ".strlen($encrypted_key);

        $string = '12296';

        print utf8_encode($this->crypt->encrypt($string));

        // print mb_substr($encrypt, 0, 490, "UTF-8");

        // print mb_convert_encoding(html_entity_decode($encrypt, "UTF-8");
    }

    public function loginAction()
    {
        $this->view->disable();

        $final_arr = array();

        if ($this->request->isPost()) {

            $factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);


            foreach ($rawBody as $key => $value) {

                if ($key == 'username') {
                    $username = $locator->sanitize($value, ['striptags', 'string']);
                }

                if ($key == 'password') {
                    $password = $locator->sanitize($value, ['striptags', 'string']);
                }
            }

            $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));

            $find_username_attempt = Users::findFirst(
                [
                    "column" => "username,attempt_count",
                    "conditions" => "username = :u_n:",
                    "bind" => [
                        "u_n" => $username
                    ]
                ]
            );

            if ($find_username_attempt == false) {

                // $helper = new helper(); // instance of helper class
                $validate_username = (new helper())->checkUsername($username);

                switch($validate_username['status']) {
                    case "username_not_exist":
                        $final_arr[] = array("status" => "username_not_exist");
                    break;
                    case "username_exist":
                        if ($validate_username['active'] == "0") {
                            $final_arr[] = array("status" => "account_inactive");
                        } else if ($this->security->checkHash($password, $validate_username['password'])) {

                            $uid = $validate_username['uid'];
                            $fullname = $validate_username['fullname'];
                            $profile_pic = $validate_username['profile_pic'];
                            $permission = $validate_username['permission'];
                            $sign_key = $validate_username['sign_key'];

                            // Build The Token

                            // JWT Signer
                            $token_jwt_signer  = new Hmac('sha512');
                            // Builder object (Access Token - 30 minutes)
                            $access_token_jwt_builder = new Builder($token_jwt_signer);
                            $access_token_jwt_now        = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                            $access_token_jwt_issued     = $access_token_jwt_now->getTimestamp();
                            $access_token_jwt_notBefore  = $access_token_jwt_now->modify('-1 minute')->getTimestamp();
                            $access_token_jwt_expires    = $access_token_jwt_now->modify('+1 hour')->getTimestamp();

                            // Builder object (Refresh Token - 1 Day)
                            $refresh_token_jwt_builder = new Builder($token_jwt_signer);
                            $refresh_token_jwt_now        = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                            $refresh_token_jwt_issued     = $refresh_token_jwt_now->getTimestamp();
                            $refresh_token_jwt_notBefore  = $refresh_token_jwt_now->modify('-1 minute')->getTimestamp();
                            $refresh_token_jwt_expires    = $refresh_token_jwt_now->modify('+1 day')->getTimestamp();


                            // Unique Passphrase(assign your own)
                            $passphrase = $sign_key;


                            $server_ip_address = $this->request->getServerAddress();

                            // $scheme = $this->request->getScheme(); // http or https

                            // check if connection is SSL Secured or not(https:// or http://)
                            // $client_ip_url = $scheme."://".$client_ip_address."/";

                            // change this to actual client url on production
                            // $client_ip_url = "https://qhcpd.quezonsystems.com/";
                            $client_ip_url = "http://localhost:4200/";

                            // $server_ip_url = "https://".$server_ip_address."/";
                            $server_ip_url = "http://".$server_ip_address."/";

                            $server_issuer = $server_ip_url; // change this to actual server on production


                            // Setup (Access Token)
                            $access_token_jwt_builder
                                ->setAudience($client_ip_url)  // aud 
                                ->setContentType('application/json')        // cty - header
                                ->setExpirationTime($access_token_jwt_expires)               // exp 
                                ->setId($uid)                               // JTI id 
                                ->setIssuedAt($access_token_jwt_issued)                      // iat 
                                ->setIssuer($server_issuer)           // iss - change this to actual server address on production 
                                ->setNotBefore($access_token_jwt_notBefore)                  // nbf
                                ->setSubject($fullname)   // subject
                                ->setPassphrase($passphrase)                // passphrase
                            ;

                            // Phalcon\Security\JWT\Token\Token object
                            $accessTokenObject = $access_token_jwt_builder->getToken();


                            // Setup (Refresh Token)
                            $refresh_token_jwt_builder
                                ->setAudience($client_ip_url)  // aud
                                ->setContentType('application/json')        // cty - header
                                ->setExpirationTime($refresh_token_jwt_expires)               // exp 
                                ->setId($uid)                               // JTI id 
                                ->setIssuedAt($refresh_token_jwt_issued)                      // iat 
                                ->setIssuer($server_issuer)           // iss - change this to actual server address on production 
                                ->setNotBefore($refresh_token_jwt_notBefore)                  // nbf
                                ->setSubject($fullname)   // subject
                                ->setPassphrase($passphrase)                // passphrase
                            ;

                            // Phalcon\Security\JWT\Token\Token object
                            $refreshTokenObject = $refresh_token_jwt_builder->getToken(); // Refresh Token

                            // Set Session
                            $this->session->set('acc_uid', $uid);
                            $this->session->set('acc_name', $fullname);
                            $this->session->set('profile_pic', $profile_pic);
                            $this->session->set('permission', $permission);
                            $this->session->set('sign_key', $passphrase);
                            $this->session->set('jwt_audience', $client_ip_url);
                            $this->session->set('jwt_issuer', $server_issuer);
                            $this->session->set('jwt_passphrase', $passphrase);
                            $this->session->set('jwt_access_token', $accessTokenObject->getToken());
                            $this->session->set('jwt_refresh_token', $refreshTokenObject->getToken());


                            // cookies to be generated as CSRF Protection for front-end to send on each request
                            $tomorrow = $now->modify('tomorrow');
                            $this->cookies->set(
                                "XSRF-TOKEN",
                                $uid,
                                (int) $tomorrow->format('U'),
                                "/",
                                false,
                                "",
                                false
                            );

                            // Audit Log
                            $uid = $this->session->get("acc_uid");
                            $transaction_type = "LOGIN";
                            $msg  = $this->session->get('acc_name')."(".$uid.") logged-in.";
                            // Save Audit Log. Function is globally written on 'helper/helper.php'
                            $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);
                            
                            $final_arr[] = array(
                                "status"  => "logged_in",
                                "uid" => $uid,
                                "fullname" => $fullname,
                                "profile_pic" => $profile_pic,
                                "permission" => $permission,
                                "access_token" => $accessTokenObject->getToken(),
                                "refresh_token" => $refreshTokenObject->getToken()
                            );

                        } else {
                            // update login attempt count
                            // $login_attempt_register = new LoginAttempt();
                            // $login_attempt_register->username = $username;
                            // $login_attempt_register->attempt_count = 1;
                            // $login_attempt_register->save();
                            $login_attempt_register = Users::findFirst(
                                [
                                    "column"    => "attempt_count",
                                    "conditions" => "username = :uname:",
                                    "bind" => [
                                      "uname" => $username
                                    ]
                                ]
                            );

                            $login_attempt_register->attempt_count = 1;
                            $login_attempt_register->update();

                            $final_arr[] = array("status" => "password_fail");
                        }
                    break;
                    default:
                        $final_arr[] = array("status" => "invalid_transaction");
                    break;
                }

            } else {

                $count = (int) $find_username_attempt->attempt_count;

                if ($count > 2) {
                    $final_arr[] = array("status" => "attempt_limit_reached");
                } else {

                    // $helper = new helper(); // instance of helper class
                    $validate_username = (new helper())->checkUsername($username);

                    switch($validate_username['status']) {
                        case "username_not_exist":
                            $final_arr[] = array("status" => "username_not_exist");
                        break;
                        case "username_exist":
                            if ($validate_username['active'] == "0") {
                                $final_arr[] = array("status" => "account_inactive");
                            } else if ($this->security->checkHash($password, $validate_username['password'])) {

                                $uid = $validate_username['uid'];
                                $fullname = $validate_username['fullname'];
                                $profile_pic = $validate_username['profile_pic'];
                                $permission = $validate_username['permission'];
                                $sign_key = $validate_username['sign_key'];

                                // Build The Token

                                // JWT Signer
                                $token_jwt_signer  = new Hmac('sha512');
                                // Builder object (Access Token - 30 minutes)
                                $access_token_jwt_builder = new Builder($token_jwt_signer);
                                $access_token_jwt_now        = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                                $access_token_jwt_issued     = $access_token_jwt_now->getTimestamp();
                                $access_token_jwt_notBefore  = $access_token_jwt_now->modify('-1 minute')->getTimestamp();
                                $access_token_jwt_expires    = $access_token_jwt_now->modify('+1 hour')->getTimestamp();

                                // Builder object (Refresh Token - 1 Day)
                                $refresh_token_jwt_builder = new Builder($token_jwt_signer);
                                $refresh_token_jwt_now        = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                                $refresh_token_jwt_issued     = $refresh_token_jwt_now->getTimestamp();
                                $refresh_token_jwt_notBefore  = $refresh_token_jwt_now->modify('-1 minute')->getTimestamp();
                                $refresh_token_jwt_expires    = $refresh_token_jwt_now->modify('+1 day')->getTimestamp();


                                // Unique Passphrase(assign your own)
                                $passphrase = $sign_key;


                                $server_ip_address = $this->request->getServerAddress();

                                // $scheme = $this->request->getScheme(); // http or https

                                // check if connection is SSL Secured or not(https:// or http://)
                                // $client_ip_url = $scheme."://".$client_ip_address."/";

                                // change this to actual client url on production
                                // $client_ip_url = "https://qhcpd.quezonsystems.com/";
                                $client_ip_url = "http://localhost:4200/";

                                // $server_ip_url = "https://".$server_ip_address."/";
                                $server_ip_url = "http://".$server_ip_address."/";

                                $server_issuer = $server_ip_url; // change this to actual server on production


                                // Setup (Access Token)
                                $access_token_jwt_builder
                                    ->setAudience($client_ip_url)  // aud 
                                    ->setContentType('application/json')        // cty - header
                                    ->setExpirationTime($access_token_jwt_expires)               // exp 
                                    ->setId($uid)                               // JTI id 
                                    ->setIssuedAt($access_token_jwt_issued)                      // iat 
                                    ->setIssuer($server_issuer)           // iss - change this to actual server address on production 
                                    ->setNotBefore($access_token_jwt_notBefore)                  // nbf
                                    ->setSubject($fullname)   // subject
                                    ->setPassphrase($passphrase)                // passphrase
                                ;

                                // Phalcon\Security\JWT\Token\Token object
                                $accessTokenObject = $access_token_jwt_builder->getToken();


                                // Setup (Refresh Token)
                                $refresh_token_jwt_builder
                                    ->setAudience($client_ip_url)  // aud
                                    ->setContentType('application/json')        // cty - header
                                    ->setExpirationTime($refresh_token_jwt_expires)               // exp 
                                    ->setId($uid)                               // JTI id 
                                    ->setIssuedAt($refresh_token_jwt_issued)                      // iat 
                                    ->setIssuer($server_issuer)           // iss - change this to actual server address on production 
                                    ->setNotBefore($refresh_token_jwt_notBefore)                  // nbf
                                    ->setSubject($fullname)   // subject
                                    ->setPassphrase($passphrase)                // passphrase
                                ;

                                // Phalcon\Security\JWT\Token\Token object
                                $refreshTokenObject = $refresh_token_jwt_builder->getToken(); // Refresh Token

                                // Set Session
                                $this->session->set('acc_uid', $uid);
                                $this->session->set('acc_name', $fullname);
                                $this->session->set('profile_pic', $profile_pic);
                                $this->session->set('permission', $permission);
                                $this->session->set('sign_key', $passphrase);
                                $this->session->set('jwt_audience', $client_ip_url);
                                $this->session->set('jwt_issuer', $server_issuer);
                                $this->session->set('jwt_passphrase', $passphrase);
                                $this->session->set('jwt_access_token', $accessTokenObject->getToken());
                                $this->session->set('jwt_refresh_token', $refreshTokenObject->getToken());


                                // reset attempts
                                $update_attempts = Users::findFirst(
                                    [
                                        "column" => "attempt_count",
                                        "conditions" => "username = :u_n:",
                                        "bind" => [
                                            "u_n" => $username
                                        ]
                                    ]
                                );

                                $update_attempts->attempt_count = (int) 0;
                                $update_attempts->update();

                                // cookies to be generated as CSRF Protection for front-end to send on each request
                                $tomorrow = $now->modify('tomorrow');
                                $this->cookies->set(
                                    "XSRF-TOKEN",
                                    $uid,
                                    (int) $tomorrow->format('U'),
                                    "/",
                                    false,
                                    "",
                                    false
                                );

                                // Audit Log
                                $uid = $this->session->get("acc_uid");
                                $transaction_type = "LOGIN";
                                $msg  = $this->session->get('acc_name')."(".$uid.") logged-in.";
                                // Save Audit Log. Function is globally written on 'helper/helper.php'
                                $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);
                                
                                $final_arr[] = array(
                                    "status"  => "logged_in",
                                    "uid" => $uid,
                                    "fullname" => $fullname,
                                    "profile_pic" => $profile_pic,
                                    "permission" => $permission,
                                    "access_token" => $accessTokenObject->getToken(),
                                    "refresh_token" => $refreshTokenObject->getToken()
                                );

                            } else {

                                // add +1 to attempts of user
                                $update_attempts = Users::findFirst(
                                    [
                                        "column" => "attempt_count",
                                        "conditions" => "username = :u_n:",
                                        "bind" => [
                                            "u_n" => $username
                                        ]
                                    ]
                                );

                                $count = (int) $update_attempts->attempt_count;

                                $update_attempts->attempt_count = $count += 1;
                                $update_attempts->update();

                                $final_arr[] = array("status" => "password_fail");

                                // $remaining_attempts = (int) 3 - $count;
                                // if($remaining_attempts == (int) 0) {
                                //     $final_arr[] = array("status" => "attempt_limit_reached");
                                // } else {
                                //     $final_arr[] = array("status" => "password_fail");
                                // }

                                
                                // $current_time = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                                // $current_time_format = $now->format('U');

                                // $login_attempt_register = new LoginAttempt();
                                // $login_attempt_register->ip_address = $client_ip;
                                // $login_attempt_register->login_time = $current_time_format;
                                // $login_attempt_register->save();

                            }
                        break;
                        default:
                            $final_arr[] = array("status" => "invalid_transaction");
                        break;
                    }

                }

            }
            
                                  
        }

        $this->cookies->send();
        $this->response->setJsonContent($final_arr);
        $this->response->send();
    }


    public function sessionCheckAction()
    {
        $this->view->disable();

        $arr = array();
        if ($this->request->isGet()) {

            if ($this->session->has('acc_uid')) {

                $acc_uid = $this->session->get('acc_uid');
                $acc_name = $this->session->get('acc_name');
                $profile_pic = $this->session->get('profile_pic');
                $permission = $this->session->get('permission');            
                $access_token = $this->session->get('jwt_access_token');
                $refresh_token = $this->session->get('jwt_refresh_token');

                $arr[] = array(
                    "status"  => "currently_logged_in",
                    "uid" => $acc_uid,
                    "fullname" => $acc_name,
                    "profile_pic" => $profile_pic,
                    "permission" => $permission,
                    "access_token" => $access_token,
                    "refresh_token" => $refresh_token
                );

            } else {
                $arr[] = array('status' => 'no_session');
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function isLoggedInAction()
    {
        $this->view->disable();

        $arr = array();
        if ($this->request->isGet()) {

            if ($this->session->has('acc_uid')) {

                $permission = json_decode($this->session->get('permission'), true);

                $arr[] = array("status"  => $permission);

            } else {
                $arr[] = array('status' => 'no_session');
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function logoutAction() 
    {

        $this->view->disable();

        $arr = array();

        if ($this->request->isPost()) {

           // activity log
           $uid = $this->session->get("acc_uid");
           $transaction_type = "LOGOUT";
           $msg  = $this->session->get('acc_name')."(".$uid.") logged-out.";
           // Save Audit Log. Function is globally written on 'helper/helper.php'
           $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);
            
            $rawBody = $this->request->getJsonRawBody(true); // gets Gets decoded JSON HTTP raw request body, if set to 'true', will output as associative array

            // $csrf_header = $this->request->getHeader('X-XSRF-TOKEN');
            // $cookie_header = $this->request->getHeader('Cookie');
            // $headerCookies = explode('; ', $cookie_header);
            // $cookies_arr = array();
            // foreach($headerCookies as $itm) {
            //     list($key, $val) = explode('=', $itm, 2);
            //     $cookies_arr[$key] = $val;
            // }

            foreach ($rawBody as $key => $value) {
                if ($key == 'grant_type') {
                    
                    $grant_type = $value;

                    if ($grant_type == 'logout') { 


                        // remove sessions on backend  
                        $this->session->remove('acc_uid');
                        $this->session->remove('acc_name');
                        $this->session->remove('profile_pic');
                        $this->session->remove('permission');
                        $this->session->remove('sign_key');
                        $this->session->remove('jwt_audience');
                        $this->session->remove('jwt_issuer');
                        $this->session->remove('jwt_passphrase');
                        $this->session->remove('jwt_access_token');
                        $this->session->remove('jwt_refresh_token');


                        // clear CSRF Cookie
                        if ($this->cookies->has('XSRF-TOKEN')) {
                            $current_cookie = $this->cookies->get('XSRF-TOKEN');
                            $current_cookie->delete();
                        }



                        $arr[] = array('status' => "logout_success");
                    }
                }
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function refreshTokenExpireAction() 
    {

        $this->view->disable();

        $arr = array();

        if ($this->request->isGet()) {
            // remove sessions on backend  
            $this->session->remove('acc_uid');
            $this->session->remove('acc_name');
            $this->session->remove('profile_pic');
            $this->session->remove('permission');
            $this->session->remove('sign_key');
            $this->session->remove('jwt_audience');
            $this->session->remove('jwt_issuer');
            $this->session->remove('jwt_passphrase');
            $this->session->remove('jwt_access_token');
            $this->session->remove('jwt_refresh_token');

            // clear CSRF Cookie
            if ($this->cookies->has('XSRF-TOKEN')) {
                $current_cookie = $this->cookies->get('XSRF-TOKEN');
                $current_cookie->delete();
            }

            $arr[] = array('status' => "refresh_token_expired" );
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function refreshTokenAction()
    {
        $this->view->disable();

        $arr = array();
        
        // $http_auth_header = $this->request->getHeader('HTTP_AUTHORIZATION');

        // if (preg_match('/Bearer\s(\S+)/', $http_auth_header, $matches)) {
        //     // alternately '/Bearer\s((.*)\.(.*)\.(.*))/' to separate each JWT string
        //     $actual_token =  $matches[1];
        // }
        if ($this->request->isPost()) {

            $rawBody = $this->request->getJsonRawBody(true); // gets Gets decoded JSON HTTP raw request body, if set to 'true', will output as associative array


            foreach ($rawBody as $key => $value) {
                if ($key == 'refresh_token') {
                    $actual_token = $value;
                }
            }

                
            $audience = $this->session->get('jwt_audience');
            $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
            $issued = $now->getTimestamp();
            $notBefore = $now->modify('-1 minute')->getTimestamp();
            $expires = $now->getTimestamp();
            $id = $this->session->get('acc_uid');
            $issuer = $this->session->get('jwt_issuer'); 
            $fullname = $this->session->get('acc_name');


            $signer = new Hmac('sha512');
            $passphrase = $this->session->get('jwt_passphrase');

            $parser = new Parser();
            $tokenObject = $parser->parse($actual_token);


            // check first if refresh token is valid then issue new access token, else throw error
            try {

                // Check Refresh Token Validity
                $validator = new Validator($tokenObject, 100); // allow for a time shift of 100
                $validator
                    ->validateAudience($audience)
                    ->validateExpiration($expires)
                    ->validateId($id)
                    ->validateIssuedAt($issued)
                    ->validateIssuer($issuer)
                    ->validateNotBefore($notBefore)
                    ->validateSignature($signer, $passphrase)
                ;


                // Builder object (Access Token - 30 minutes)
                $access_token_jwt_builder = new Builder($signer);
                $access_token_jwt_now        = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
                $access_token_jwt_issued     = $access_token_jwt_now->getTimestamp();
                $access_token_jwt_notBefore  = $access_token_jwt_now->modify('-1 minute')->getTimestamp();
                $access_token_jwt_expires    = $access_token_jwt_now->modify('+1 hour')->getTimestamp();
                // Setup (Access Token)
                $access_token_jwt_builder
                    ->setAudience($audience)  // aud - change this to recepients $client_ip_url on production
                    ->setContentType('application/json')        // cty - header
                    ->setExpirationTime($access_token_jwt_expires)               // exp 
                    ->setId($id)                               // JTI id 
                    ->setIssuedAt($access_token_jwt_issued)                      // iat 
                    ->setIssuer($issuer)           // iss - change this to actual server address on production 
                    ->setNotBefore($access_token_jwt_notBefore)                  // nbf
                    ->setSubject($fullname)   // subject
                    ->setPassphrase($passphrase)                // password 
                ;

                // Phalcon\Security\JWT\Token\Token object
                $accessTokenObject = $access_token_jwt_builder->getToken();

                // update session value of Access token with newly created
                if ($this->session->has('jwt_access_token')) {
                    $this->session->set('jwt_access_token', $accessTokenObject->getToken());
                }

                $arr[] = array("access_token" => $accessTokenObject->getToken());

            } catch (Exception $ex) {
                $this->response->setStatusCode(403, 'Forbidden');
                $arr[] = array("code" => $ex->getCode(), "message" => $ex->getMessage());
            }

        }

        $this->response->setJsonContent($arr);
        $this->response->send();

    }

    public function updateProfilePicAction()
    {
        $this->view->disable();

        $final_arr = array();
        $extension_arr = ['jpg','jpeg','JPG','JPEG','png','PNG','tif','TIF','gif','GIF'];

        if ($this->request->isPost()) {
            $uid = $this->request->getPost('uid', ['striptags', 'string']);

            if ($this->request->hasFiles()) {
                $files = $this->request->getUploadedFiles();

                foreach ($files as $file) {
                    // echo $file->getName(), ' ', $file->getSize(), '\n'
                    // $files_arr[] = array(
                    //     "file_name" => $val->getName(),
                    //     "file_extension" => $val->getExtension(),
                    //     "cover_letter" => $decode_cover_letter_arr[$key]['cover_letter']
                    // );

                    // $files_arr[] = array(
                    //     "file_name" => $val->getName(),
                    //     "file_extension" => $val->getExtension(),
                    //     "cover_letter" => false
                    // );

                    // check file size
                    if ($file->getSize() > 1048576) {
                        $final_arr[] = array('status' => 'file_too_large');
                    } else if (!in_array($file->getExtension(),$extension_arr)) {
                        $final_arr[] = array('status' => 'format_not_supported');
                    } else {
                        // update database and move the file to directory
                        $fileName = $file->getName();
                        $update = (new helper())->updateProfilePicture($uid,$fileName);
                        $current_filename = $update[0]['current_filename'];
                        $status = $update[0]['status'];

                        switch($status) {
                            case "update_fail":
                                $final_arr[] = array("status" => "fail");
                            break;
                            case "update_success":

                                // Audit Log
                                $uid = $this->session->get("acc_uid");
                                $transaction_type = "update";
                                $msg  = "Updated Profile Picture of Account No. (".$uid.")";
                                // Save Audit Log. Function is globally written on 'helper/helper.php'
                                $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);

                                $final_arr[] = array("status" => "success", "profile_pic" => $fileName);

                                if ($current_filename == "no_picture.png") {
                                    $file->moveTo(
                                        'files/profile-pic/'.$fileName 
                                    );
                                } else {
                                    unlink('files/profile-pic/'.$current_filename);
                                    $file->moveTo(
                                        'files/profile-pic/'.$fileName 
                                    );
                                }

                                
                            break;
                            default:
                                $final_arr[] = array("status" => "invalid_transaction");
                            break;
                        }
                    }

                }
            }
        }

        $this->response->setJsonContent($final_arr);
        $this->response->send();
    }


    public function updatePasswordAction()
    {
        $this->view->disable();

        $arr = array();

        if ($this->request->isPost()) {

            $rawBody = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {
                if ($key == 'uid') {
                    $uid = $value;
                }

                if ($key == 'password') {
                    $password = $value;
                }
            }

            $final_password = $this->security->hash($password);

            $update = (new helper())->updatePasswordFromDashboard($uid,$final_password);
            $status = $update[0]['status'];

            switch($status) {
                case "update_fail":
                    $arr[] = array("status" => "fail");
                break;
                case "update_success":

                    // Audit Log
                    $uid = $this->session->get("acc_uid");
                    $transaction_type = "update";
                    $msg  = "Updated Password of Account No. (".$uid.") to ".$password;
                    // Save Audit Log. Function is globally written on 'helper/helper.php'
                    $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);

                    $arr[] = array("status" => "success");
                break;
                default:
                    $arr[] = array("status" => "invalid_transaction");
                break;
            }

        }

        $this->response->setJsonContent($arr);
        $this->response->send();


    }

}

