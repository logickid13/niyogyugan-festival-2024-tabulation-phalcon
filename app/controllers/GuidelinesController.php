<?php
declare(strict_types=1);

class GuidelinesController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function loadGuidelinesAction()
    {
        $this->view->disable();

        if ($this->request->isGet()) {
            $guidelines = (new helper())->loadGuidelines();
        }

        $this->response->setJsonContent($guidelines);
        $this->response->send();
    }

}

