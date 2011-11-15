<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Esaizu!
 * @version 1.0
 * Copyright (C) ESLE & Irontec 2011
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Biblioteca encargada de la validación de datos
 * @author Mikel Madariaga
 */
class Common_Validator
{

    protected $_ci;
    protected $_errorArray = array();
    protected $_errorMessages = array();
    
    static protected $_instance;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_ci =& get_instance();

        // Set the character encoding in MB.
        if (function_exists('mb_internal_encoding')) {

            mb_internal_encoding($this->_ci->config->item('charset'));
        }

        $this->_ci->lang->load('form_validation');
        log_message('debug', "Common_Validator Class Initialized");
    }

    public static function &get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Common_Validator();
        }

        return self::$_instance;
    }

    public function getErrorArray()
    {
        return $this->_errorArray;
    }
    
    public function getErrorMessages()
    {
        return $this->_errorMessages;
        
    }

    //--------------------------------------------------------------------

    public function validate($rules, $fieldname, $postdata = NULL, $reset = true)
    {
        if ($reset == true) {
        
            $this->_errorArray = array();
            $this->_errorMessages = array();
        }

        $rules = explode("|", $rules);
        // --------------------------------------------------------------------

        // If the field is blank, but NOT required, no further tests are necessary
        $callback = FALSE;
        if ( ! in_array('required', $rules) AND is_null($postdata)) {

            // Before we bail out, does the rule contain a callback?
            if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match)) {

                $callback = TRUE;
                $rules = (array('1' => $match[1]));

            } else {

                return;
            }
        }

        // --------------------------------------------------------------------

        // Isset Test. Typically this rule will only apply to checkboxes.
        if (is_null($postdata) AND $callback == FALSE) {

            if (in_array('isset', $rules, TRUE) OR in_array('required', $rules)) {

                // Set the message type
                $type = (in_array('required', $rules)) ? 'required' : 'isset';

                if ( ! isset($this->_errorMessages[$type])) {

                    if (FALSE === ($line = $this->_ci->lang->line($type))) {

                        $line = 'The field was not set';
                    }

                } else {

                    $line = $this->_errorMessages[$type];
                }

                // Build the error message
                $message = sprintf($line, $this->_translate_fieldname($fieldname));

                // Save the error message

                if ( ! isset($this->_errorArray[$fieldname])) {

                    $this->_errorArray[$fieldname] = $message;
                }
            }

            return;
        }

        // --------------------------------------------------------------------

        // Cycle through each rule and run it
        foreach ($rules As $rule) {

            $_in_array = FALSE;

            // Is the rule a callback?
            $callback = FALSE;
            if (substr($rule, 0, 9) == 'callback_') {

                $rule = substr($rule, 9);
                $callback = TRUE;
            }

            // Strip the parameter (if exists) from the rule
            // Rules can contain a parameter: max_length[5]
            $param = FALSE;
            if (preg_match("/(.*?)\[(.*)\]/", $rule, $match)) {

                $rule   = $match[1];
                $param  = $match[2];
            }

            // Call the function that corresponds to the rule  TODO
            if ($callback === TRUE) {

                if ( ! method_exists($this->_ci, $rule)) {

                    continue;
                }

                // Run the function and grab the result
                $result = $this->_ci->$rule($postdata, $param);

                // Re-assign the result to the master data array
                //$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
 
                // If the field isn't required and we just processed a callback we'll move on...
                if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE) {

                    continue;
                }
            } else {

                if ( ! method_exists($this, $rule)) {

                    // If our own wrapper function doesn't exist we see if a native PHP function does.
                    // Users can use any native PHP function call that has one param.
                    if (function_exists($rule)) {

                        $result = $rule($postdata);
                        //$result = (is_bool($result)) ? $postdata : $result;
                    }

                    continue;
                }

                $result = $this->$rule($postdata, $param);
            }

            // Did the rule test negatively?  If so, grab the error.
            if ($result === FALSE) {

                if ( ! isset($this->_errorMessages[$rule])) {

                    if (FALSE === ($line = $this->_ci->lang->line($rule))) {

                        $line = 'Unable to access an error message corresponding to your field name.';
                    }
                    
                } else {
                    
                    $line = $this->_errorMessages[$rule];
                }

                // Build the error message
                //$message = sprintf($line, $this->_translate_fieldname($fieldname), $param);
                $message = sprintf($line, $param);

                // Save the error message
                //$this->_field_data[$row['field']]['error'] = $message;

                if ( ! isset($this->_errorArray[$fieldname]))
                {
                    $this->_errorArray[$fieldname] = $message;
                }

                return false;
            }
        }
        
        if ( isset($this->_errorArray[$fieldname]) )
        {
            unset($this->_errorArray[$fieldname]);
        }
        return true;
    }


    /**
     * Translate a field name
     *
     * @access  private
     * @param   string  the field name
     * @return  string
     */
    function _translate_fieldname($fieldname)
    {
        // Do we need to translate the field name?
        // We look for the prefix lang: to determine this
        if (substr($fieldname, 0, 5) == 'lang:')
        {
            // Grab the variable
            $line = substr($fieldname, 5);

            // Were we able to translate the field name?  If not we use $line
            if (FALSE === ($fieldname = $this->CI->lang->line($line)))
            {
                return $line;
            }
        }

        return $fieldname;
    }
    
    // --------------------------------------------------------------------

    /**
     * Required
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function required($str)
    {
        if ( ! is_array($str))
        {
            return (trim($str) == '') ? FALSE : TRUE;
        }
        else
        {
            return ( ! empty($str));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Performs a Regular Expression match test.
     *
     * @access  public
     * @param   string
     * @param   regex
     * @return  bool
     */
    private function regex_match($str, $regex)
    {
        if ( ! preg_match($regex, $str))
        {
            return FALSE;
        }

        return  TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @access  public
     * @param   string
     * @param   field
     * @return  bool
     */
    private function matches($str, $field)
    {
        if ( ! isset($_POST[$field]))
        {
            return FALSE;
        }

        $field = $_POST[$field];

        return ($str !== $field) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    private function min_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) < $val) ? FALSE : TRUE;
        }

        return (strlen($str) < $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    private function max_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) > $val) ? FALSE : TRUE;
        }

        return (strlen($str) > $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    private function exact_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) != $val) ? FALSE : TRUE;
        }

        return (strlen($str) != $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function valid_email($str)
    {
        return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Emails
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function valid_emails($str)
    {
        if (strpos($str, ',') === FALSE)
        {
            return $this->valid_email(trim($str));
        }

        foreach(explode(',', $str) as $email)
        {
            if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function valid_ip($ip)
    {
        return $this->_ci->input->valid_ip($ip);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function alpha($str)
    {
        return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function alpha_numeric($str)
    {
        return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function alpha_dash($str)
    {
        return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function numeric($str)
    {
        return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

    }

    // --------------------------------------------------------------------

    /**
     * Is Numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function is_numeric($str)
    {
        return ( ! is_numeric($str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function integer($str)
    {
        return (bool)preg_match( '/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function is_natural($str)
    {
        return (bool)preg_match( '/^[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function is_natural_no_zero($str)
    {
        if ( ! preg_match( '/^[0-9]+$/', $str))
        {
            return FALSE;
        }

        if ($str == 0)
        {
            return FALSE;
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    private function valid_base64($str)
    {
        return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Prep data for form
     *
     * This function allows HTML to be safely shown in a form.
     * Special characters are converted.
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function prep_for_form($data = '')
    {
        if (is_array($data))
        {
            foreach ($data as $key => $val)
            {
                $data[$key] = $this->prep_for_form($val);
            }

            return $data;
        }

        if ($this->_safe_form_data == FALSE OR $data === '')
        {
            return $data;
        }

        return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    /**
     * Prep URL
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function prep_url($str = '')
    {
        if ($str == 'http://' OR $str == '')
        {
            return '';
        }

        if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
        {
            $str = 'http://'.$str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function strip_image_tags($str)
    {
        return $this->_ci->input->strip_image_tags($str);
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function xss_clean($str)
    {
        if ( ! isset($this->_ci->security))
        {
            $this->_ci->load->library('security');
        }

        return $this->_ci->security->xss_clean($str);
    }

    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @access  public
     * @param   string
     * @return  string
     */
    private function encode_php_tags($str)
    {
        return str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }

}