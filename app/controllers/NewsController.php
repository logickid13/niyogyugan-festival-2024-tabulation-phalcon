<?php
declare(strict_types=1);

class NewsController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function loadAction()
    {
        $this->view->disable();

        if ($this->request->isGet()) {
            $news_list = (new helper())->loadNews();
        }

        $this->response->setJsonContent($news_list);
        $this->response->send();
    }

}

