<?php
class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseURL();

        // Cari Controller
        if (isset($url[0]) && !empty($url[0])) {
            $formattedUrl = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $url[0])));
            $controllerName = $formattedUrl . 'Controller';
            if (file_exists(__DIR__ . '/../controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // Fallback pencarian case-insensitive & tanpa tanda minus/garis bawah untuk Linux/Vercel
                $found = false;
                $targetClean = strtolower(str_replace(['-', '_'], '', $url[0])) . 'controller.php';
                $files = scandir(__DIR__ . '/../controllers/');
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..') continue;
                    if (strtolower(str_replace(['-', '_'], '', $f)) === $targetClean) {
                        $this->controller = pathinfo($f, PATHINFO_FILENAME);
                        unset($url[0]);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->controller = 'ErrorController';
                    $this->method = 'notFound';
                }
            }
        }

        require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Method
        if (isset($url[1]) && !empty($url[1])) {
            $methodName = str_replace('-', '_', $url[1]);
            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } else {
                // Fallback pencarian method case-insensitive
                $cleanTarget = strtolower(str_replace(['-', '_'], '', $url[1]));
                $methods = get_class_methods($this->controller) ?: [];
                foreach ($methods as $m) {
                    if (strtolower(str_replace(['-', '_'], '', $m)) === $cleanTarget) {
                        $this->method = $m;
                        unset($url[1]);
                        break;
                    }
                }
            }
        }

        // Params
        if (!empty($url)) {
            $this->params = array_values($url);
        }

        // Jalankan controller & method, serta kirim params jika ada
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL() {
        // Prioritas 1: Parameter $_GET['url'] (dari Apache .htaccess atau rewrite vercel.json)
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        // Prioritas 2: Fallback membaca REQUEST_URI (Vercel Serverless Function / Caddy / Nginx / Apache subdirektori)
        $reqUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($reqUri, PHP_URL_PATH) ?? '/';

        // Buang prefix subdirektori jika aplikasi berjalan di folder (misal /AbsensiRFID/web/public, /AbsensiRFID, atau /web/public)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir  = dirname($scriptName);
        
        $dir = $scriptDir;
        while ($dir !== '/' && $dir !== '.' && $dir !== '\\' && !empty($dir)) {
            if (str_starts_with($path, $dir)) {
                $path = substr($path, strlen($dir));
                break;
            }
            $parent = str_replace('\\', '/', dirname($dir));
            if ($parent === $dir) break;
            $dir = $parent;
        }

        // Buang awalan index.php jika ada di URL
        $path = preg_replace('#^/index\.php#', '', $path);
        $path = preg_replace('#^/(web/public|web|public|api)#', '', $path);
        $path = trim($path, '/');
        if (!empty($path)) {
            $path = filter_var($path, FILTER_SANITIZE_URL);
            return explode('/', $path);
        }

        return [];
    }
}
