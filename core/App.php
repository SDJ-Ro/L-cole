<?php
// =============================================================================
// L'ÉCOLE MVC AUTOLOADER
// Automatically resolves Models and Core classes without manual require_once.
// Supports both global and App\Models\ namespaces transparently.
// =============================================================================
spl_autoload_register(function ($class) {
    $cleanClass = ltrim($class, '\\');
    
    // Support App\Models\ namespace by stripping prefix
    if (str_starts_with($cleanClass, 'App\\Models\\')) {
        $cleanClass = substr($cleanClass, strlen('App\\Models\\'));
    }

    // 1. Models in app/Models/
    $modelFile = dirname(__DIR__) . '/app/Models/' . $cleanClass . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        // Dual-alias: support both global and App\Models\ seamlessly
        if (class_exists($cleanClass, false) && !class_exists('App\\Models\\' . $cleanClass, false)) {
            class_alias($cleanClass, 'App\\Models\\' . $cleanClass);
        }
        return;
    }

    // 2. Core classes in core/
    $coreFile = dirname(__DIR__) . '/core/' . $cleanClass . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
});

class App {
    // Default controller and method if the URL is empty
    protected $controller = 'LandingController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Friendly top-level route aliases
        if (isset($url[0])) {
            $alias = strtolower($url[0]);
            if (in_array($alias, ['signin', 'login', 'access'], true)) {
                $url[0] = 'auth';
            } elseif ($alias === 'logout') {
                $url[0] = 'auth';
                $url[1] = 'logout';
            } elseif ($alias === 'signup') {
                $url[0] = 'auth';
            }
        }

        // 1. Look for a matching controller file
        if (isset($url[0]) && file_exists('../app/Controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        // 2. Load and instantiate the controller
        require_once '../app/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // 3. Look for a matching method inside that controller
        if (isset($url[1])) {
            $methodCandidate = $url[1];
            $camelCase = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $methodCandidate))));
            if (method_exists($this->controller, $methodCandidate)) {
                $this->method = $methodCandidate;
                unset($url[1]);
            } elseif (method_exists($this->controller, $camelCase)) {
                $this->method = $camelCase;
                unset($url[1]);
            }
        }

        // 4. Pass any remaining URL parts as parameters
        $this->params = $url ? array_values($url) : [];

        // 5. Execute the controller method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    // Breaks the URL (e.g. "/auth/student" or "/landing/achievements") into an array
    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = trim($path, '/');
            if (!empty($path)) {
                return explode('/', filter_var($path, FILTER_SANITIZE_URL));
            }
        }

        return [];
    }
}