<?php
class HomeController extends Controller {
    public function index() {
        $data['judul'] = 'Sistem Absensi SMK Berbasis RFID & AI';
        
        // If user is already logged in, redirect them to dashboard unless preview parameter is set
        if (SessionHelper::get('user_id') && !isset($_GET['preview'])) {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $this->view('home/landing', $data);
    }
}
