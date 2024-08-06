<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

class ConsolationController extends \Phalcon\Mvc\Controller
{

    public function beforeExecuteRoute(Dispatcher $dispatcher) 
    {
      $arr = array();
      $cookies_arr = array();
      $accepted_routes = [
        "updateConsolationScore",
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
          if ($action_name == "updateConsolationScore") {
            if (!in_array(5.01, $decoded_permission)) {
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
        $arr = array();

        $username = "9999";

        $base64_encrypt = $this->crypt->encryptBase64($username);

        $password_hash = $this->security->hash($username);

        $arr[] = array(
            "base64_encode" => $base64_encrypt,
            "password_hash" => $password_hash
        );

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function loadMunicipalityAction()
    {
        $this->view->disable();

        if ($this->request->isGet()) {
            $load_municipalities = (new helper())->loadMunicipalities();
        }

        $this->response->setJsonContent($load_municipalities);
        $this->response->send();
    }

    public function loadContestAction()
    {
        $this->view->disable();

        if ($this->request->isGet()) {
            $load_contest = (new helper())->loadContests();
        }

        $this->response->setJsonContent($load_contest);
        $this->response->send();
    }


    public function updateConsolationScoreAction()
    {
        $this->view->disable();

        $arr = array();

        if ($this->request->isPost()) {

            $factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {

                if ($key == 'municipality_name') {
                    $municipality_name = $locator->sanitize($value, ['striptags', 'string']);
                }

                if ($key == 'contest_name') {
                    $contest_name = $locator->sanitize($value, ['striptags', 'string']);
                }

                if ($key == 'municipality_id') {
                    $municipality_id = $locator->sanitize($value, ['striptags', 'string']);
                }

                if ($key == 'munic_arr') {
                    $munic_arr = $value;
                }

                if ($key == 'municipality_id') {
                    $municipality_id = $locator->sanitize($value, ['striptags', 'string']);
                }

                if ($key == 'score_to_be_added') {
                    $score_to_be_added = $locator->sanitize($value, ['striptags', 'string']);
                }
            }

            $decoded_munic_arr = json_decode($munic_arr, true);

            foreach ($decoded_munic_arr as $value) {
                $munic_id = $value["municipality_id"];
                $contest_id = $value["contest_id"];

                $update_query = Scores::findFirst(
                    [
                        "column" => "s_score",
                        "conditions" => "s_munic = :sm: AND s_contest = :sc:",
                        "bind" => [
                            "sm" => $munic_id,
                            "sc" => $contest_id
                        ]
                    ]
                );

                $current_score = (int) $update_query->s_score;
                $updated_score = $current_score + (int) $score_to_be_added;

                $update_query->s_score = (int) $updated_score;
                $update_query->update();
                
            }

            $arr[] = array("status" => "success");

        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    

}

