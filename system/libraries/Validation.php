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
 * Form Validation Class
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Validation
 * @author      ExpressionEngine Dev Team
 * @link        http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class CI_Validation {

    var $CI;
    var $_field_data            = array();
    var $_config_rules          = array();
    var $_error_array           = array();
    var $_error_messages        = array();
    var $_error_prefix          = '<p>';
    var $_error_suffix          = '</p>';
    var $error_string           = '';
    var $_safe_form_data        = FALSE;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();

        // Automatically load the form helper
        $this->CI->load->helper('form');

        // Set the character encoding in MB.
        if (function_exists('mb_internal_encoding'))
        {
            mb_internal_encoding($this->CI->config->item('charset'));
        }

        log_message('debug', "Validation Class Initialized");
    }
    
    //--------------------------------------------------------------------

    function validate($rules, $data = NULL)
    {

        $rules = explode("|",$rules);
        // --------------------------------------------------------------------

        // If the field is blank, but NOT required, no further tests are necessary
        $callback = FALSE;
        if ( ! in_array('required', $rules) AND is_null($postdata))
        {
            // Before we bail out, does the rule contain a callback?
            if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match))
            {
                $callback = TRUE;
                $rules = (array('1' => $match[1]));
            }
            else
            {
                return;
            }
        }

        // --------------------------------------------------------------------

        // Isset Test. Typically this rule will only apply to checkboxes.
        if (is_null($postdata) AND $callback == FALSE)
        {
            if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
            {
                // Set the message type
                $type = (in_array('required', $rules)) ? 'required' : 'isset';

                if ( ! isset($this->_error_messages[$type]))
                {
                    if (FALSE === ($line = $this->CI->lang->line($type)))
                    {
                        $line = 'The field was not set';
                    }
                }
                else
                {
                    $line = $this->_error_messages[$type];
                }

                // Build the error message
                $message = sprintf($line, $this->_translate_fieldname($row['label']));

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if ( ! isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }
            }

            return;
        }

        // --------------------------------------------------------------------

        // Cycle through each rule and run it
        foreach ($rules As $rule)
        {
            $_in_array = FALSE;

            // We set the $postdata variable with the current data in our master array so that
            // each cycle of the loop is dealing with the processed data from the last cycle
            if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
            {
                // We shouldn't need this safety, but just in case there isn't an array index
                // associated with this cycle we'll bail out
                if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
                {
                    continue;
                }

                $postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
                $_in_array = TRUE;
            }
            else
            {
                $postdata = $this->_field_data[$row['field']]['postdata'];
            }

            // --------------------------------------------------------------------

            // Is the rule a callback?
            $callback = FALSE;
            if (substr($rule, 0, 9) == 'callback_')
            {
                $rule = substr($rule, 9);
                $callback = TRUE;
            }

            // Strip the parameter (if exists) from the rule
            // Rules can contain a parameter: max_length[5]
            $param = FALSE;
            if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
            {
                $rule   = $match[1];
                $param  = $match[2];
            }

            // Call the function that corresponds to the rule
            if ($callback === TRUE)
            {
                if ( ! method_exists($this->CI, $rule))
                {
                    continue;
                }

                // Run the function and grab the result
                $result = $this->CI->$rule($postdata, $param);

                // Re-assign the result to the master data array
                if ($_in_array == TRUE)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                }

                // If the field isn't required and we just processed a callback we'll move on...
                if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
                {
                    continue;
                }
            }
            else
            {
                if ( ! method_exists($this, $rule))
                {
                    // If our own wrapper function doesn't exist we see if a native PHP function does.
                    // Users can use any native PHP function call that has one param.
                    if (function_exists($rule))
                    {
                        $result = $rule($postdata);

                        if ($_in_array == TRUE)
                        {
                            $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                        }
                        else
                        {
                            $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                        }
                    }

                    continue;
                }

                $result = $this->$rule($postdata, $param);

                if ($_in_array == TRUE)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                }
            }

            // Did the rule test negatively?  If so, grab the error.
            if ($result === FALSE)
            {
                if ( ! isset($this->_error_messages[$rule]))
                {
                    if (FALSE === ($line = $this->CI->lang->line($rule)))
                    {
                        $line = 'Unable to access an error message corresponding to your field name.';
                    }
                }
                else
                {
                    $line = $this->_error_messages[$rule];
                }

                // Is the parameter we are inserting into the error message the name
                // of another field?  If so we need to grab its "field label"
                if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
                {
                    $param = $this->_translate_fieldname($this->_field_data[$param]['label']);
                }

                // Build the error message
                $message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if ( ! isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }

                return;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Required
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    function required($str)
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
    function regex_match($str, $regex)
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
    function matches($str, $field)
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
    function min_length($str, $val)
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
    function max_length($str, $val)
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
    function exact_length($str, $val)
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
    function valid_email($str)
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
    function valid_emails($str)
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
    function valid_ip($ip)
    {
        return $this->CI->input->valid_ip($ip);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    function alpha($str)
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
    function alpha_numeric($str)
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
    function alpha_dash($str)
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
    function numeric($str)
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
    function is_numeric($str)
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
    function integer($str)
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
    function is_natural($str)
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
    function is_natural_no_zero($str)
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
    function valid_base64($str)
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
    function prep_for_form($data = '')
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
    function prep_url($str = '')
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
    function strip_image_tags($str)
    {
        return $this->CI->input->strip_image_tags($str);
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * @access  public
     * @param   string
     * @return  string
     */
    function xss_clean($str)
    {
        if ( ! isset($this->CI->security))
        {
            $this->CI->load->library('security');
        }

        return $this->CI->security->xss_clean($str);
    }

    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @access  public
     * @param   string
     * @return  string
     */
    function encode_php_tags($str)
    {
        return str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }

}
// END Form Validation Class

/* End of file Form_validation.php */
/* Location: ./system/libraries/Form_validation.php */