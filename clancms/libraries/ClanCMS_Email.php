<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * Clan CMS
 *
 * An open source application for gaming clans
 *
 * @package		Clan CMS
 * @author		Xcel Gaming Development Team
 * @copyright	Copyright (c) 2010 - 2011, Xcel Gaming, Inc.
 * @license		http://www.xcelgaming.com/about/license/
 * @link		http://www.xcelgaming.com
 * @since		Version 0.6.1
 */

// ------------------------------------------------------------------------

/**
 * Clan CMS Unzip Class
 *
 * @package		Clan CMS
 * @subpackage	Libraries
 * @category	Email
 * @author		Xcel Gaming Development Team
 * @link		http://www.xcelgaming.com
 */
class ClanCMS_Email extends CI_Email {

	public $CI;
	
	/**
	 * Constructor
	 *
	 */	
	function __construct()
	{	
		// Call the Email Constructor
        parent::__construct();
		
		// Create an instance to CI
		$this->CI =& get_instance();
		
		// Retrieve the email protocol
        $config['protocol'] = $this->CI->ClanCMS->get_setting('email_protocol');

        // Configure sendmail settings
        if($config['protocol'] == 'sendmail')
        {
			// Check the sendmail path
            if($this->CI->ClanCMS->get_setting('email_sendmail_path') == '')
            {
				// Set mail path
				$config['mailpath'] = '/usr/sbin/sendmail';
			}
			else
			{
				// Set mail path
				$config['mailpath'] = $this->CI->ClanCMS->get_setting('email_sendmail_path');
			}
		}
        
        // Configure smtp settings
        if ($config['protocol'] == 'smtp')
        {	
			// Configure additional settings
            $config['smtp_host'] = $this->CI->ClanCMS->get_setting('email_smtp_host');
            $config['smtp_user'] = $this->CI->ClanCMS->get_setting('email_smtp_user');
            $config['smtp_pass'] = $this->CI->ClanCMS->get_setting('email_smtp_pass');
			$config['smtp_port'] = $this->CI->ClanCMS->get_setting('email_smtp_port');
			$config['smtp_timeout'] = '30';
			$config['charset'] = 'utf-8';
			$config['newline'] = "\r\n"; 
        }

		// Initialize settings
        $this->initialize($config);
	}
	
}

/* End of file ClanCMS_Email.php */
/* Location: ./clancms/libraries/ClanCMS_Email.php */