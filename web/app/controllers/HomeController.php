<?php
class HomeController extends Controller {
    public function index() {
        $data['judul'] = 'Sistem Absensi SMK Berbasis RFID & AI';
        $data['isLoggedIn'] = (bool)SessionHelper::get('user_id');
        $data['userName']   = SessionHelper::get('name') ?? 'Pengguna';
        $data['userRole']   = SessionHelper::get('role') ?? 'user';

        $this->view('home/landing', $data);
    }
}
