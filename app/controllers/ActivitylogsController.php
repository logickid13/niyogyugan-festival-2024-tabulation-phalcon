<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Phalcon\Mvc\Dispatcher;

class ActivitylogsController extends \Phalcon\Mvc\Controller
{

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

            $load_audit_logs = (new helper())->loadActivityLogs($query,$sort,$order,$currentPage,$pageSize);
            $counter = (new helper())->loadActivityLogsCount($query,$sort,$order,$currentPage,$pageSize);

            $arr['rows']['data'] = $load_audit_logs;
            $arr['rows']['count'] = $counter['count'];
            $arr['rows']['total_pages'] = $counter['total_pages'];
            $arr['rows']['pageno'] = $currentPage;
            $arr['rows']['rows_per_page'] = $pageSize;

    	}

    	$this->response->setJsonContent($arr);
    	$this->response->send();
    }

    public function loadUsersAutoCompleteAction()
    {
        $this->view->disable();

        $arr = array();

        if ($this->request->isGet()) {
            $load_users_auto_complete = (new helper())->loadUsersAutoComplete();
        }

        $this->response->setJsonContent($load_users_auto_complete);
        $this->response->send();
    }

}

