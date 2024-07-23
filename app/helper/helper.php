<?php 
use Phalcon\Db;
use Phalcon\Di;
use Phalcon\Di\Injectable;
use Phalcon\Filter\FilterFactory;
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class helper extends Injectable {

    public function auditLog($uid,$transaction_type,$msg) {
        $date_today = new DateTime("now", new DateTimeZone("Asia/Manila")); //current datetime
        $log = new AuditTrail();
        $log->timestamp  = $date_today->format("Y-m-d H:i:s");
        $log->username = $uid;
        $log->transaction_type = $transaction_type;
        $log->event  = $msg;
        $log->save();
    }


	public function checkUsername($username) { 

        $display_profile_picture = ""; 

        $find_username = Users::findFirst(
            [
                "column"    => "username,password,fullname,profile_pic,permission,sign_key,active",
                "conditions" => "username = :uname:",
                "bind" => [
                  "uname" => $username
                ]
            ]
        );

        if ($find_username == false) {

            $arr = array('status' => 'username_not_exist');

        } else {

            if ($find_username->profile_pic == "no_picture") {
                $display_profile_picture = "no_picture.png";
            } else {
                $display_profile_picture = $find_username->profile_pic;
            }

            $arr = array(
                "status" => "username_exist",
                "uid" => $find_username->username,
                "password" => $find_username->password,
                "fullname" => $find_username->fullname,
                "profile_pic" => $display_profile_picture,
                "permission" => $find_username->permission,
                "sign_key" => $find_username->sign_key,
                "active" => $find_username->active
            );
              
        }

        return $arr;
    }

    public function updateProfilePicture($uid,$fileName) {
        $update_profile_picture = Users::findFirst(
            [
                'column' => 'username,profile_pic',
                'conditions' => 'username = :uid:',
                'bind' => [
                    'uid' => $uid
                ]
            ]
        );

        $current_filename = $update_profile_picture->profile_pic;

        if ($current_filename == "no_picture") {
            $current_filename = "no_picture.png";
        } else {
            $current_filename = $update_profile_picture->profile_pic;
        }

        $update_profile_picture->profile_pic = $fileName;

        if ($update_profile_picture->update() == false) {
            $arr[] = array("status" => "update_fail","current_filename" => $current_filename);
        } else {
            $arr[] = array("status" => "update_success","current_filename" => $current_filename);
        }

        return $arr;
    }

    public function updatePasswordFromDashboard($uid,$final_password) 
    {

        $update_password = Users::findFirst(
            [
                'column' => 'username,password',
                'conditions' => 'username = :uid:',
                'bind' => [
                    'uid' => $uid
                ]
            ]
        );

        $update_password->password = $final_password;

        if ($update_password->update() == false) {
            $arr[] = array("status" => "update_fail");
        } else {
            $arr[] = array("status" => "update_success");
        }

        return $arr;
    }

    public function loadAccounts($query,$sort,$order,$currentPage,$pageSize) 
    {
            $arr = array();
            $offset = (int) $currentPage * (int) $pageSize;
            $order = ($order == 'asc') ? "ASC" : "DESC";
            
            foreach ($query as $key => $value) {
                if ($key == 'username') {
                    $username = $value;
                }

                if ($key == 'fullname') {
                    $fullname = $value;
                }
            }


            // actual records
            $phql_values = array(
                "page_size" => $offset,
                "ofs" => $pageSize
            );

            $phql_binding_type = array(
                "page_size" => \Phalcon\Db\Column::BIND_PARAM_INT,
                "ofs" => \Phalcon\Db\Column::BIND_PARAM_INT
            );

            $phql = "SELECT Users.id_no,Users.username,Users.fullname,Users.profile_pic,Users.permission,Users.active FROM Users WHERE 1=1 ";

            if ($username != "") {
                $offset = (int) 0 * (int) $pageSize;
                $phql .= "AND Users.username = :uname: ";
                $phql_values["uname"] = $username;
                $phql_binding_type["uname"] = \Phalcon\Db\Column::BIND_PARAM_STR;
            }

            if ($fullname != "") {
                $offset = (int) 0 * (int) $pageSize;
                $phql .= "AND Users.fullname LIKE :fname: ";
                $phql_values["fname"] = '%'.$fullname.'%';
                $phql_binding_type["fname"] = \Phalcon\Db\Column::BIND_PARAM_STR;
            }

            $phql .= "ORDER BY Users.".$sort." ".$order." LIMIT :page_size:,:ofs:";

            $load_account = $this->modelsManager->executeQuery(
                $phql,
                $phql_values,
                $phql_binding_type
            );

            foreach ($load_account as $row) {
                $arr[] = array(
                    'id_no' => $row->id_no,
                    'username' => $row->username,
                    'fullname' => $row->fullname,
                    'profile_pic' => $row->profile_pic,
                    'permission' => $row->permission,
                    'active' => $row->active
                );
            }

            return $arr; 
    }

    public function loadAccountsCount($query,$sort,$order,$currentPage,$pageSize) 
    {
            $arr = array();
            $offset = (int) $currentPage * (int) $pageSize;
            $order = ($order == 'asc') ? "ASC" : "DESC";
            
            foreach ($query as $key => $value) {
                if ($key == 'username') {
                    $username = $value;
                }

                if ($key == 'fullname') {
                    $fullname = $value;
                }
            }

            $phql_values = array();

            $phql_binding_type = array();

            $phql = "SELECT COUNT(*) AS actual_count FROM Users WHERE 1=1 ";

            if ($username != "" || $username != null) {
                $phql .= "AND Users.username = :uname: ";
                $phql_values["uname"] = $username;
                $phql_binding_type["uname"] = \Phalcon\Db\Column::BIND_PARAM_STR;
            }

            if ($fullname != "" || $fullname != null) {
                $phql .= "AND Users.fullname LIKE :fname: ";
                $phql_values["fname"] = '%'.$fullname.'%';
                $phql_binding_type["fname"] = \Phalcon\Db\Column::BIND_PARAM_STR;
            }

            $phql .= "ORDER BY Users.".$sort." ".$order;

            $total_pages = $this->modelsManager->executeQuery(
                $phql,
                $phql_values,
                $phql_binding_type
            )->getFirst();

            $total_rows = $total_pages->actual_count;
            $total_pages = ceil((int) $total_rows / (int) $pageSize); 

            $arr = array("count" => $total_rows, "total_pages" => $total_pages);

            return $arr;
    }

    public function updateBasicInfo($id_no,$username,$fullname,$permission_arr,$active)
    {

        $update_account = Users::findFirst(
            [
                'column' => 'id_no,username,fullname,permission,sign_key,active',
                'conditions' => 'id_no = :idnum:',
                'bind' => [
                    'idnum' => $id_no
                ]
            ]
        );

        $update_account->username = $username;
        $update_account->fullname = $fullname;
        $update_account->permission = $permission_arr;
        $update_account->sign_key = $this->crypt->encryptBase64($username);
        $update_account->active = $active;

        if ($update_account->update() == false) {
            $arr[] = array("status" => "update_fail");
        } else {
            // if igbinary and memcached is enabled
            // if ($this->modelsCache->has('users-services')) {
            //     $this->modelsCache->delete('users-services');
            // }
            
            $arr[] = array("status" => "update_success");

        }

        return $arr;
    }


    public function updatePasswordofAccount($id_no,$final_password) 
    {

        $update_password = Users::findFirst(
            [
                'column' => 'id_no,password',
                'conditions' => 'id_no = :idno:',
                'bind' => [
                    'idno' => $id_no
                ]
            ]
        );

        $update_password->password = $final_password;

        if ($update_password->update() == false) {
            $arr[] = array("status" => "update_fail");
        } else {

            // if igbinary and memcached is enabled
            // if ($this->modelsCache->has('users-services')) {
            //     $this->modelsCache->delete('users-services');
            // }

            $arr[] = array("status" => "update_success");

        }

        return $arr;
    }

    public function newAccountNoProfilePicture($username,$final_password,$fullname,$permission_arr,$date_registered)
    {

        $check_username = Users::findFirst(
            [
                "column" => "username",
                "conditions" => "username = :uname:",
                "bind" => [
                  "uname" => $username
                ]
            ]
        );

        if ($check_username == false) {

            $new_account = new Users();
            $new_account->username = $username;
            $new_account->password = $final_password;
            $new_account->fullname = $fullname;
            $new_account->reg_date = $date_registered->format('Y-m-d H:i:s');
            $new_account->profile_pic = "no_picture";
            $new_account->permission = $permission_arr;
            $new_account->sign_key = $this->crypt->encryptBase64($username);
            $new_account->active = (int) 1;

            if ($new_account->save() == false) {
                $arr[] = array('status' => 'fail');
            } else {
                // if igbinary and memcached is enabled
                // if ($this->modelsCache->has('users-services')) {
                //     $this->modelsCache->delete('users-services');
                // }

                $arr[] = array('status' => 'success');
            }

        } else {

            $arr[] = array("status" => "username_exist");
              
        }

        return $arr;
    }


    public function newAccountWithProfilePicture($username,$final_password,$fullname,$permission_arr,$date_registered,$fileName)
    {

        $check_username = Users::findFirst(
            [
                "column" => "username",
                "conditions" => "username = :uname:",
                "bind" => [
                  "uname" => $username
                ]
            ]
        );

        if ($check_username == false) {

            $new_account = new Users();
            $new_account->username = $username;
            $new_account->password = $final_password;
            $new_account->fullname = $fullname;
            $new_account->reg_date = $date_registered->format('Y-m-d H:i:s');
            $new_account->profile_pic = $fileName;
            $new_account->permission = $permission_arr;
            $new_account->sign_key = $this->crypt->encryptBase64($username);
            $new_account->active = (int) 1;

            if ($new_account->save() == false) {
                $final_arr[] = array('status' => 'fail');
            } else {

                // if igbinary and memcached is enabled
                // if ($this->modelsCache->has('users-services')) {
                //     $this->modelsCache->delete('users-services');
                // }


                $final_arr[] = array('status' => 'success');

            }

        } else {

            $final_arr[] = array("status" => "username_exist");
              
        }

        return $final_arr;

    }


    public function unlockAccount($username)
    {
        $reset_attempt = LoginAttempt::findFirst(
            [
                "column" => "attempt_count",
                "conditions" => "username = :u_n:",
                "bind" => [
                    "u_n" => $username  
                ]
            ]
        );

        $reset_attempt->attempt_count = 0;

        if ($reset_attempt->update() == false) {
            $arr[] = array("status" => "reset_fail");
        } else {
            $arr[] = array("status" => "reset_success");
        }

        return $arr;
    }

    public function loadLeaderboard() 
    {
        $arr = array();
        $phql_query = "SELECT Scores.s_munic AS s_munic, SUM(Scores.s_score) AS total, Municipalities.munic_name AS municipality_name FROM Scores INNER JOIN Municipalities ON Municipalities.munic_id = Scores.s_munic GROUP BY Scores.s_munic ORDER BY total DESC";
        $execute_query = $this->modelsManager->executeQuery($phql_query);

        foreach ($execute_query as $val) {
            $arr[] = array(
                "s_munic" => $val->s_munic,
                "total" => $val->total,
                "municipality_name" => $val->municipality_name,
                "isOpen" => false
            );
        }

        return $arr;
    }

    public function getContestResults($munic_id) {
        $arr = array();
        $phql_query = "SELECT Scores.s_munic AS munic_id, Scores.s_contest AS contest_id, Scores.s_score AS score, Municipalities.munic_name AS munic_name, Contests.c_name AS contest_name FROM Scores INNER JOIN Municipalities ON Municipalities.munic_id = Scores.s_munic INNER JOIN Contests ON Contests.c_id = Scores.s_contest WHERE Scores.s_munic = :id_of_municipality:";
        $execute_query = $this->modelsManager->executeQuery(
            $phql_query,
            [
                "id_of_municipality" => $munic_id
            ]
        );

        foreach ($execute_query as $val) {
            $arr[] = array(
                "contest_name" => $val->contest_name,
                "score" => $val->score
            );
        }

        return $arr;
    }

    public function loadListofActivities() {
        $arr = array();

        $list_of_activities = ListOfActivities::find(
            [
                "column" => "a_id,c_id,a_datetime,a_venue,a_icon",
                "order" => "a_datetime ASC"
            ]
        );

        foreach($list_of_activities as $val) {
            $activity_date = new DateTime($val->a_datetime, new DateTimeZone("Asia/Manila"));

            $arr[] = array(
                "a_id" => (int) $val->a_id,
                "c_id" => $val->c_id,
                "a_date" => $activity_date->format("F d"),
                "a_time" => $activity_date->format("h:i A"),
                "a_venue" => $val->a_venue,
                "a_icon" => $val->a_icon,
            );
        }

        return $arr;
    }

    public function loadGuidelines() {
        $arr = array();
        $phql_query = "SELECT ListOfActivities.a_id AS a_id, ListOfActivities.c_id AS c_id, ListOfActivities.a_icon AS a_icon, ListOfActivities.a_guidelines AS a_guidelines, Contests.c_name AS c_name FROM ListOfActivities INNER JOIN Contests ON Contests.c_id = ListOfActivities.c_id ORDER BY ListOfActivities.a_datetime ASC";
        $execute_query = $this->modelsManager->executeQuery($phql_query);

        foreach ($execute_query as $val) {
            $arr[] = array(
                "a_id" => $val->a_id,
                "c_id" => $val->c_id,
                "a_icon" => $val->a_icon,
                "a_guidelines" => $val->a_guidelines,
                "c_name" => $val->c_name
            );
        }

        return $arr;
    }

}