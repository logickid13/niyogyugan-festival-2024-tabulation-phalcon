<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

class AccountsController extends \Phalcon\Mvc\Controller
{

    public function beforeExecuteRoute(Dispatcher $dispatcher) 
    {
      $arr = array();
      $cookies_arr = array();
      $accepted_routes = [
        "updateBasicInfo",
        "updateProfilePic",
        "updatePassword",
        "newAccountnoProfilePic",
        "newAccountWithProfilePic",
        "unlock"
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
          if ($action_name == "") {
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

    public function loadAction()
    {
    	$this->view->disable();
        $arr = array();

    	if ($this->request->isPost()) {

    		$factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {
                if ($key == 'query') {
                    $query = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'sort') {
                    $sort = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'order') {
                    $order = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'currentPage') {
                    $currentPage = $locator->sanitize($value, ['striptags', 'int']);
                }

                if ($key == 'pageSize') {
                    $pageSize = $locator->sanitize($value, ['striptags', 'int']);
                }
            }

            $load_accounts = (new helper())->loadAccounts($query,$sort,$order,$currentPage,$pageSize);
            $counter = (new helper())->loadAccountsCount($query,$sort,$order,$currentPage,$pageSize);

            $arr['rows']['data'] = $load_accounts;
            $arr['rows']['count'] = $counter['count'];
            $arr['rows']['total_pages'] = $counter['total_pages'];
            $arr['rows']['pageno'] = $currentPage;
            $arr['rows']['rows_per_page'] = $pageSize;
    	}

    	$this->response->setJsonContent($arr);
    	$this->response->send();
    }

    public function newAccountnoProfilePicAction()
    {
        $this->view->disable();
        $arr = array();

        if ($this->request->isPost()) {

            $factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);
            $date_registered = new DateTime('now', new DateTimeZone('Asia/Manila'));

            foreach ($rawBody as $key => $value) {
                if ($key == 'username') {
                    $username = $locator->sanitize($value, ['striptags', 'int']);
                }

                if ($key == 'password') {
                    $password = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'fullname') {
                    $fullname = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'permission_arr') {
                    $permission_arr = $locator->sanitize($value, 'striptags');
                }
            }

            $final_password = $this->security->hash($password);

            // $arr[] = array(
            //     "username" => $username,
            //     "password" => $password,
            //     "fullname" => $fullname,
            //     "permission_arr" => $permission_arr
            // );

            $insert_new_account = (new helper())->newAccountNoProfilePicture($username,$final_password,$fullname,$permission_arr,$date_registered);
            
            $status = $insert_new_account[0]['status'];

            switch($status) {
                case "username_exist":
                    $arr[] = array('status' => 'username_exist');
                break;
                case "fail":
                    $arr[] = array('status' => 'fail');
                break;
                case "success":
                    // Audit Log
                    $uid = $this->session->get("acc_uid");
                    $transaction_type = "CREATE";
                    $msg  = "Created New Account with following details: Username: ".$username.", Full Name: ".$fullname.", Permission: ".$permission_arr.", Date Registered: ".$date_registered->format('Y-m-d H:i:s').", Active Status: 1";
                    // Save Audit Log. Function is globally written on 'helper/helper.php'
                    $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);

                    $arr[] = array('status' => 'success');
                break;
                default:
                    $arr[] = array('status' => 'invalid_transaction');
                break;
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();


    }


    public function newAccountWithProfilePicAction()
    {
        $this->view->disable();
        $final_arr = array();

        if ($this->request->isPost()) {
            
            $extension_arr = ['jpg','jpeg','JPG','JPEG','png','PNG','tif','TIF','gif','GIF'];

            $username = $this->request->getPost("username", ["striptags", "int"]);
            $password = $this->request->getPost("password", "striptags");
            $fullname = $this->request->getPost("fullname", "striptags");
            $permission_arr = $this->request->getPost("permission_arr", "striptags");

            $final_password = $this->security->hash($password);
            $date_registered = new DateTime('now', new DateTimeZone('Asia/Manila'));

            if ($this->request->hasFiles()) {
                $files = $this->request->getUploadedFiles();

                foreach ($files as $file) {
                    // check file size
                    if ($file->getSize() > 1048576) {
                        $final_arr[] = array('status' => 'file_too_large');
                    } else if (!in_array($file->getExtension(),$extension_arr)) {
                        $final_arr[] = array('status' => 'format_not_supported');
                    } else {
                        // update database and move the file to directory
                        $fileName = $file->getName();

                        $insert_account = (new helper())->newAccountWithProfilePicture($username,$final_password,$fullname,$permission_arr,$date_registered,$fileName);
                        $status = $insert_account[0]['status'];

                        switch($status) {
                            case "username_exist":
                                $final_arr[] = array('status' => 'username_exist');
                            break;
                            case "fail":
                                $final_arr[] = array("status" => "fail");
                            break;
                            case "success":

                                $file->moveTo(
                                    'files/profile-pic/'.$fileName 
                                );

                                // Audit Log
                                $uid = $this->session->get("acc_uid");
                                $transaction_type = "CREATE";
                                $msg  = "Created New Account with following details: Username: ".$username.", Full Name: ".$fullname.", Permission: ".$permission_arr.", Date Registered: ".$date_registered->format('Y-m-d H:i:s').", Active Status: 1";
                                // Save Audit Log. Function is globally written on 'helper/helper.php'
                                $save_to_auditlog  = (new helper())->auditLog($uid,$transaction_type,$msg);

                                $final_arr[] = array("status" => "success");

                                

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

    public function updateBasicInfoAction()
    {
        $this->view->disable();
        $arr = array();

        if ($this->request->isPost()) {
            $factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {
                if ($key == 'id_no') {
                    $id_no = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'username') {
                    $username = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'fullname') {
                    $fullname = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'permission_arr') {
                    $permission_arr = $locator->sanitize($value, 'striptags');
                }

                if ($key == 'active') {
                    $active = $locator->sanitize($value, 'striptags');
                }
                
            }


            $update_basic_info = (new helper())->updateBasicInfo($id_no,$username,$fullname,$permission_arr,$active);

            $status = $update_basic_info[0]['status'];

            switch($status) {
                case "update_fail":
                    $arr[] = array("status" => "fail");
                break;
                case "update_success":

                    // Audit Log
                    $uid = $this->session->get("acc_uid");
                    $transaction_type = "UPDATE";
                    $msg  = "Updated Record No. (".$id_no.") SET Username = ".$uid.", Full Name = ".$fullname.", Permission = ".$permission_arr.", Active Status = ".$active;
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
                                $transaction_type = "UPDATE";
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
                if ($key == 'id_no') {
                    $id_no = $value;
                }

                if ($key == 'password') {
                    $password = $value;
                }
            }

            $final_password = $this->security->hash($password);

            $update = (new helper())->updatePasswordofAccount($id_no,$final_password);
            $status = $update[0]['status'];

            switch($status) {
                case "update_fail":
                    $arr[] = array("status" => "fail");
                break;
                case "update_success":

                    // Audit Log
                    $uid = $this->session->get("acc_uid");
                    $transaction_type = "UPDATE";
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

    public function unlockAction()
    {
        $this->view->disable();

        $arr = array();

        if ($this->request->isPost()) {

            $factory = new FilterFactory();
            $locator = $factory->newInstance();

            $rawBody = $this->request->getJsonRawBody(true);


            foreach ($rawBody as $key => $value) {
                if ($key == 'username') {
                    $username = $locator->sanitize($value, 'striptags');
                }
            }

            $unlock_account = (new helper())->unlockAccount($username);
            $status = $unlock_account[0]['status'];

            switch($status) {
                case "reset_fail":
                    $arr[] = array("status" => "fail");
                break;
                case "reset_success":

                    // Audit Log
                    $uid = $this->session->get("acc_uid");
                    $transaction_type = "UPDATE";
                    $msg  = "Update Login Attempts of Account (".$username.")";
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

