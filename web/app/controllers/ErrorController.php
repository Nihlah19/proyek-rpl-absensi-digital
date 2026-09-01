<?php
class ErrorController extends Controller {
    public function notFound() {
        $data['judul'] = '404 Not Found';
        $this->view('templates/header', $data);
        $this->view('errors/404', $data);
        $this->view('templates/footer', $data);
    }

    public function forbidden() {
        $data['judul'] = '403 Forbidden';
        $this->view('templates/header', $data);
        $this->view('errors/403', $data);
        $this->view('templates/footer', $data);
    }

    public function serverError() {
        $data['judul'] = '500 Internal Server Error';
        $this->view('templates/header', $data);
        $this->view('errors/500', $data);
        $this->view('templates/footer', $data);
    }
}
