<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Config class
*/
class Config {
	private $data = array();
    
	/**
     * 
     *
     * @param	string	$key
	 * 
	 * @return	mixed
     */
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}
	
    /**
     * 
     *
     * @param	string	$key
	 * @param	string	$value
     */
	public function set($key, $value) {
		$this->data[$key] = $value;
	}

    /**
     * 
     *
     * @param	string	$key
	 *
	 * @return	mixed
     */
	public function has($key) {
		return isset($this->data[$key]);
	}
	
    /**
     * 
     *
     * @param	string	$filename
     */
	public function load($filename) {

		if (file_exists($filename)) {
			$_ = array();

			require($filename);
			var_dump($this->data);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			exit();
		}
	}
}