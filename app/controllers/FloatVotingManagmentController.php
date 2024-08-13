<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
use Phalcon\Mvc\Model\Query\BuilderInterface;

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

            // $is_existing = FloatVotes::findFirst(array(
            //     "conditions" => "facebook_profile = :1:",
            //     "bind"       => array("1" => $facebook)
            // ));

            $is_existing = FloatVotes::findFirst(array(
                "conditions" => "fullname = :1: AND voters_address = :2:",
                "bind"       => array(
                    "1" => $facebook,
                    "2" => $address
                )
            ));

            switch ($is_existing) {
                case true:
                    $arr[] = array('status' => 'fb_profile_has_record');
                    break;
                
                default:
                    $new_vote = new FloatVotes();
                    $new_vote->email               = $email;
                    $new_vote->fullname            = $fullname;
                    $new_vote->voters_address      = $address;
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

    public function rankingAction(){
        $this->view->disable();
        $arr = array();

        if ($this->request->isGet()) {
            $towns = [
                "POLILLO", "UNISAN", "CALAUAG", "ATIMONAN", "LUCBAN", "PANUKULAN",
                "CATANAUAN", "SAN FRANCISCO", "GENERAL LUNA", "ALABAT", "GUMACA",
                "MULANAY", "MAUBAN", "TIAONG", "SARIAYA", "LOPEZ", "TAYABAS CITY",
                "PAGBILAO", "QUEZON", "GENERAL NAKAR", "CANDELARIA", "JOMALIG",
                "REAL", "GUINAYANGAN", "PEREZ"
            ];

            foreach ($towns as $town) {
                $sql = 'SELECT COUNT(*) as count FROM FloatVotes WHERE JSON_CONTAINS(float_vote_choices, '."'[".'"'.$town.'"'."]'".')'; 
                $query = $this->modelsManager->createQuery($sql);
                $results  = $query->execute();

                $count = 0;
                foreach ($results as $key => $val) {
                    $count += $val['count'];
                }

                $arr[] = array("town" => $town, 'votes' => $count);
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }

    public function votersDataAction(){
        $this->view->disable();
        $arr = array();

        if ($this->request->isGet()) {
            $towns = [
                "POLILLO", "UNISAN", "CALAUAG", "ATIMONAN", "LUCBAN", "PANUKULAN",
                "CATANAUAN", "SAN FRANCISCO", "GENERAL LUNA", "ALABAT", "GUMACA",
                "MULANAY", "MAUBAN", "TIAONG", "SARIAYA", "LOPEZ", "TAYABAS CITY",
                "PAGBILAO", "QUEZON", "GENERAL NAKAR", "CANDELARIA", "JOMALIG",
                "REAL", "GUINAYANGAN", "PEREZ"
            ];

            foreach ($towns as $town) {
                $sql = 'SELECT fullname, email, cellphone_number as mobileno, facebook_profile as fb_profile, voters_address as v_address FROM FloatVotes WHERE JSON_CONTAINS(float_vote_choices, '."'[".'"'.$town.'"'."]'".')'; 
                $query = $this->modelsManager->createQuery($sql);
                $results  = $query->execute();

                $arr[] = array("town" => $town, 'voters' => $results);
            }
        }

        $this->response->setJsonContent($arr);
        $this->response->send();
    }
}

