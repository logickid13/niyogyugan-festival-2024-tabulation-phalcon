<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

class FloatVotingManagmentController extends \Phalcon\Mvc\Controller
{

    public function indexAction(){

    }

    public function addNewVoteAction(){
        $this->view->disable();
        $arr = array();

        $factory = new FilterFactory();
        $locator = $factory->newInstance();

        if ($this->request->isPost()) {
            $date_today = new DateTime("now", new DateTimeZone("Asia/Manila"));
            $rawBody    = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {
                if ($key == 'fullname') {
                    $fullname = $locator->sanitize($value, 'striptags');
                }
                if ($key == 'email') {
                    $email = $locator->sanitize($value, 'striptags');
                }
                if ($key == 'facebook') {
                    $facebook = $locator->sanitize($value, 'striptags');
                }
                if ($key == 'mobileno') {
                    $mobileno = $locator->sanitize($value, 'striptags');
                }
                if ($key == 'address') {
                    $address = $locator->sanitize($value, 'striptags');
                }
                if ($key == 'data_privacy') {
                    switch ($locator->sanitize($value, 'striptags')) {
                        case true:
                            $data_privacy = 1;
                            break;

                        case false:
                            $data_privacy = 0;
                            break;
                    }
                }
                if ($key == 'municipalitySelections') {
                    $municipalitySelections = json_encode($value);
                }
            }

            $is_existing = FloatVotes::findFirst(array(
                "conditions" => "facebook_profile = :1:",
                "bind"       => array("1" => $facebook)
            ));

            switch ($is_existing) {
                case true:
                    $arr[] = array('status' => 'fb_profile_has_record');
                    break;
                
                default:
                    $new_vote = new FloatVotes();
                    $new_vote->email               = $email;
                    $new_vote->fullname            = $fullname;
                    $new_vote->address             = $address;
                    $new_vote->cellphone_number    = $mobileno;
                    $new_vote->facebook_profile    = $facebook;
                    $new_vote->data_privacy        = 1;
                    $new_vote->float_vote_choices  = $municipalitySelections;
                    $new_vote->date_registered     = $date_today->format('Y-m-d H:i:s');
        
                    if ($new_vote->save() == false) {
                        $arr[] = array("status" => "fail");
                    }else{
                        $arr[] = array("status" => "success");
                    }
                    break;
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

}

