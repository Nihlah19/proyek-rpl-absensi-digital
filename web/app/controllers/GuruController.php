<?php
class GuruController extends Controller {
    public function __construct() {
        AuthHelper::checkRole(['guru', 'wali_kelas', 'walikelas']);
    }

    public function scanner() {
        $data['judul'] = 'Scanner Absensi Guru';
        
        // Load model Guru
        $guruModel = $this->model('Guru');
        $user = SessionHelper::get('user_id') ?? null;
        $guru = $guruModel->getByUserId($user);
        $data['guru'] = $guru;
        
        $this->view('templates/header', $data);
        $this->view('guru/scanner', $data);
        $this->view('templates/footer', $data);
    }
}
