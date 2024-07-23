<?php
declare(strict_types=1);

use Phalcon\Filter\FilterFactory;

class LeaderboardsController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function loadAction()
    {
        $this->view->disable();
        if ($this->request->isGet()) {
            $leaderboard = (new helper())->loadLeaderboard();
        }
        
        $this->response->setJsonContent($leaderboard);
        $this->response->send();
    }

    public function loadContestResultsPerMunicipalityAction()
    {
        $this->view->disable();
        if ($this->request->isPost()) {
            $factory = new FilterFactory();
            $locator = $factory->newInstance();
            $rawBody = $this->request->getJsonRawBody(true);

            foreach ($rawBody as $key => $value) {
                if ($key == 'munic_id') {
                    $munic_id = $locator->sanitize($value, 'striptags');
                }
            }

            $scores = (new helper())->getContestResults($munic_id);
        }

        $this->response->setJsonContent($scores);
        $this->response->send();
    }

}

