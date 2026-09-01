<?php
class HomeController extends Controller {
    public function index() {
        $data['judul'] = 'Sistem Absensi SMK Berbasis RFID & AI';
        
        // If user is already logged in, redirect them to dashboard
        if (SessionHelper::get('user_id')) {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $this->view('home/landing', $data);
    }
}
