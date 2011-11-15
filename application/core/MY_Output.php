<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Output Class
 *
 * Responsible for sending final output to browser
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Output
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/output.html
 */
class My_Output extends CI_Output {

    private $_js = array(); //javascript files to load
    private $_css = array(); //css files to load

	function __construct()
	{
	    parent::__construct();
	}

	public function injectJs($js)
	{
	    if (is_array($js)) {

	       $this->_js = array_merge($this->_js,$js);

	    } else {

	       $this->_js[] = $js;
	    }
	}

	public function getJsDependencies()
	{
	   return $this->_js;
	}

	public function injectCss($css)
	{
	    if (is_array($css)) {

           $this->_css = array_merge($this->_css,$css);

        } else {

           $this->_css[] = $css;
        }
	}

	public function getCssDependencies()
	{
	   return $this->_css;
	}
}