<?php
class GuruController extends Controller {
    public function __construct() {
        AuthHelper::checkRole(['guru']);
    }

    public function scanner() {
        $data['judul'] = 'Scanner Absensi Guru';
        
        $this->view('templates/header', $data);
        $this->view('guru/scanner', $data);
        $this->view('templates/footer', $data);
    }
}
