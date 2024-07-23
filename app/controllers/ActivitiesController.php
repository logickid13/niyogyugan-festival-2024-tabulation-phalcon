<?php
declare(strict_types=1);

class ActivitiesController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function loadActivitiesAction()
    {
        $this->view->disable();

        if ($this->request->isGet()) {
            $activities_list = (new helper())->loadListofActivities();
        }

        $this->response->setJsonContent($activities_list);
        $this->response->send();
    }

}

