<?php

class App
{

    protected $controller = 'index';

    protected $method = 'index';

    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();
 //       print_r($url);
        if (file_exists('controllers/' . $url[0] . '.php')) {
            $this->controller = $url[0];
            unset($url[0]);
        } else {
            if($url[0]==""){
                $this->controller = 'index';
            }else{
                $this->controller = 'help';
            }
            
        }
        
        require_once 'controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller();
        
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                $this->method = 'index';
            }
        }
        
        $this->params = $url ? array_values($url) : [];
        
        call_user_func_array([
            $this->controller,
            $this->method
        ], $this->params);
    }

    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            
            return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}

?>
