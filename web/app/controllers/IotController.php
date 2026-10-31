<?php
class IotController extends Controller {
    public function index() {
        AuthHelper::checkRole(['admin']);
        header('Location: ' . BASEURL . '/admin/iot');
        exit;
    }
}
