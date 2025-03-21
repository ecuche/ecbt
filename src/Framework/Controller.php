<?php
declare(strict_types=1);

namespace Framework;

use Framework\Helpers\Session;
use Framework\Helpers\CSRF;

abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected TemplateViewerInterface $viewer;
    public function setRequest(Request $request) : void
    {
        $this->request = $request;
    }

    public function setViewer(TemplateViewerInterface $viewer) : void
    {
        $this->viewer = $viewer;
    }

    public function setResponse(Response $response) : void
    {
        $this->response = $response;
    }

    protected function view(string $template, array $data = []) : Response
    {
        $data['alert'] = Session::flash(['success', 'danger', 'info', 'warning']);
        $data['CSRF'] = CSRF::generate();
        $this->response->setBody($this->viewer->render($template, $data));
        return $this->response;
    }

    protected function redirect(string $url): Response
    {
        $this->response->redirect($url);
        return $this->response;
    } 

    public function raw(string $template, array $data = []) : string
    {
        $data['alert'] = Session::flash(['success', 'danger', 'info', 'warning']);
        $data['CSRF'] = CSRF::generate();
        return $this->viewer->render($template, $data);
    }
}