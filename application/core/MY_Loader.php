<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package     CodeIgniter
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 * @link        http://codeigniter.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @author      ExpressionEngine Dev Team
 * @category    Loader
 * @link        http://codeigniter.com/user_guide/libraries/loader.html
 */


/****
 * Autoload
 */
function __autoload($className) {

    if (substr($className,0,4) == "Zend") {

        $zf_className = str_replace("_","/",$className);
        $path = BASEPATH;

        if(file_exists($path.$zf_className.EXT)) {

            if(! strpos(ini_get('include_path'), BASEPATH.'Zend') ) {

                ini_set('include_path',ini_get('include_path') . PATH_SEPARATOR . BASEPATH);
            }

            require_once $path.$zf_className.EXT;
            log_message('debug', "Class $className loaded");
            return;
        }

    } else {

        if (is_dir(BASEPATH.APPPATH)) {

            $applicationPath = BASEPATH.APPPATH;

        } else {

            $applicationPath = APPPATH;
        }

        $paths = array(
            BASEPATH."libraries/",
            BASEPATH."core/",
            $applicationPath."libraries/",
            $applicationPath."libraries/",
            $applicationPath."models/",
            $applicationPath."third_party/",
        );

        $ci_className = str_replace("_","/",str_replace("CI_","",$className));
        $my_className = str_replace("_","/",str_replace("MY_","",$className));

        foreach($paths as $path) {

            if(file_exists($path.$ci_className.EXT)) {

                include_once $path.$ci_className.EXT;
                log_message('debug', "Class $className loaded");
                return;

            } else if (file_exists($path.$my_className.EXT)) {

                include_once $path.$my_className.EXT;
                log_message('debug', "Class $className loaded");
                return;

            } else if(file_exists($path.$className.EXT)) {

                include_once $path.$className.EXT;
                log_message('debug', "Class $className loaded");
                return;
            }
        }
    }

    log_message('debug', "Class $className not found");
}

class MY_Loader extends CI_Loader {

    function __construct()
    {
        parent::__construct();
    }

    function model($model, $name = '', $db_conn = FALSE)
    {
        if (is_array($model)) {

            foreach($model as $babe) {

                $this->model($babe);
            }

            return;
        }

        if ($model == '') {

            return;
        }

        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== FALSE) {

            // The path is in front of the last slash
            $path = substr($model, 0, $last_slash + 1);

            // And the model name behind it
            $model = substr($model, $last_slash + 1);
        }

        if ($name == '') {

            $name = $model;
        }

        if (in_array($name, $this->_ci_models, TRUE)) {
            return;
        }

        $CI =& get_instance();
        if (isset($CI->$name)) {

            show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
        }

        $model = ucfirst(strtolower($model));

        foreach ($this->_ci_model_paths as $mod_path)
        {
            if ( ! file_exists($mod_path.'models/'.$path.$model.EXT))
            {
                continue;
            }

            if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
            {
                if ($db_conn === TRUE)
                {
                    $db_conn = '';
                }

                $CI->load->database($db_conn, FALSE, TRUE);
            }

            if ( ! class_exists('CI_Model'))
            {
                load_class('Model', 'core');
            }

            require_once($mod_path.'models/'.$path.$model.EXT);

            $model = ucfirst($model);
            $CI->$name = new $model();

            $this->_ci_models[] = $name;
            return;
        }

        // couldn't find the model
        show_error('Unable to locate the model you have specified: '.$model);
    }

    function masterview($view, $data=array(),$extra = 'default', $return = FALSE) {

        if (!file_exists(BASEPATH.'../application/config/masterview'.EXT))
            show_error('Unable to load requested file: '.BASEPATH.'../application/config/masterview'.EXT);

        if (!is_array($extra)) {

            $index = $extra;
        }

        require(APPPATH.'config/masterview'.EXT);
        foreach ($conf[$index] as $key=> $val) {

            if($val == "")  unset($conf[$index][$key]);
        }

        if (!isset($wrapper)) {
            $wrapper = isset($conf["wrapper"]) ? $conf[$index]["wrapper"] : "wrapper";
        }

        $data = array_merge($conf[$index], $data);

        //merge css's and js's with output dependencies
        $CI =& get_instance();
        if (isset($data["css"])) {
            $data["css"] = array_merge($data["css"], $CI->output->getCssDependencies());
        }
        
            
        if (isset($data["+css"])) {
            $data["css"] = array_merge($data["css"], $data["+css"]);
        }

        if (isset($data["js"])) {
            $data["js"] = array_merge($data["js"], $CI->output->getJsDependencies());
        }
        
        if (isset($data["+js"])) {
            $data["js"] = array_merge($data["js"], $data["+js"]);
        }

        $data["view"] = $view;
        $this->view($wrapper, $data, $return);
    }
}