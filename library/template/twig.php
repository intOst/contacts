<?php
namespace Template;
final class Twig {
	private $twig;
	private $data = array();
	
	public function __construct() {
		// include and register Twig auto-loader
		include_once(DIR_LIBRARY . 'template/Twig/Autoloader.php');
		
		\Twig_Autoloader::register();
	}
	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function render($template, $data = []) {

	    foreach ($data as $key=>$value){
	        $this->set($key,$value);
        }

		// specify where to look for templates
		$loader = new \Twig_Loader_Filesystem(DIR_TEMPLATE);

		// initialize Twig environment
		$config = array('autoescape' => false);

		$this->twig = new \Twig_Environment($loader, $config);
		
		try {
            $template_header = $this->twig->loadTemplate('base/header.twig');
            $template_header = $template_header->render($this->data);
            // load template
			$template = $this->twig->loadTemplate($template . '.twig');
            $template = $template->render($this->data);

            $template_footer = $this->twig->loadTemplate('base/footer.twig');
            $template_footer = $template_footer->render($this->data);

            return $template_header . $template .$template_footer;
		} catch (Exception $e) {
			trigger_error('Error: Could not load template ' . $template . '!');
			exit();	
		}	
	}	
}
