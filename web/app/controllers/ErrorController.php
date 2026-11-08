<?php
class ErrorController extends Controller {
    public function notFound() {
        http_response_code(404);
        $data['judul'] = '404 - Halaman Tidak Ditemukan';
        if (SessionHelper::get('user_id')) {
            $this->view('templates/header', $data);
            $this->view('errors/404', $data);
            $this->view('templates/footer', $data);
        } else {
            $this->view('errors/404_public', $data);
        }
    }

    public function forbidden() {
        http_response_code(403);
        $data['judul'] = '403 - Akses Ditolak';
        if (SessionHelper::get('user_id')) {
            $this->view('templates/header', $data);
            $this->view('errors/403', $data);
            $this->view('templates/footer', $data);
        } else {
            $this->view('errors/404_public', $data);
        }
    }

    public function serverError() {
        http_response_code(500);
        $data['judul'] = '500 - Gangguan Server Internal';
        if (SessionHelper::get('user_id')) {
            $this->view('templates/header', $data);
            $this->view('errors/500', $data);
            $this->view('templates/footer', $data);
        } else {
            $this->view('errors/404_public', $data);
        }
    }
}
