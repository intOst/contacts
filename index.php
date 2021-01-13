<?php
    error_reporting(-1);
    require_once('config.php');
    require_once(DIR_LIBRARY . 'db.php');
    require_once(DIR_LIBRARY . 'registry.php');
    require_once(DIR_LIBRARY . 'controller.php');
    require_once(DIR_LIBRARY . 'model.php');
    require_once(DIR_LIBRARY . 'template/twig.php');

    $routes = [
        'contacts' => [
            'list' => 'index',
            'view' => 'view',
            'edit' => 'edit',
            'add' => 'add',
            'remove' => 'remove'
        ]
    ];


    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    $registry = new Registry();
    $registry->set('db',$db);
    $registry->set('routes',$routes);



    if (class_exists('Template\\Twig')) {
        $twig = new Template\Twig();
        $registry->set('twig',$twig);
    } else {
        throw new Exception('Error: Could not load template adaptor Twig!');
    }


    if(isset($_GET['route'])){
        $route = explode('?',$_GET['route']);
        $route = explode('/',$route[0]);
    }

    // base page for start
    reset($routes);
    $base_page = HTTPS_SERVER.key($routes).'/'.key(current($routes));
    $registry->set('base_page',$base_page);

    if(isset($route) && isset($route[1]) && !empty($route[1]) && $routes[$route[0]][$route[1]]){
        // route contains [class => [methods]]
        $class = $route[0];
        $method = $routes[$route[0]][$route[1]];
        $registry->set('method', $method);
        $registry->set('class', $class);
        if(isset($route[2])){
            $registry->set('id',$route[2]);
        }
    }else{

        header("Location: ".$base_page);
        die();
    }

    // Initialize the class
    $file = DIR_CONTROLLER.$class.'.php';
    if (is_file($file)) {
        include_once($file);
        $class = ucfirst($class);
        $class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $class);
        $controller = new $class($registry);
        echo call_user_func_array(array($controller, $method),  array(&$registry));
    } else {
        echo new Exception('Error: Could not call ' . $class . '/' . $method . '!');
    }

