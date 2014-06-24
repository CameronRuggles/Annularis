<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * General Library
 * 
 * A general library containing miscellaneous functions used throughout
 * the application.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	General
 * @author		BitWasp
 */
class General {

	/**
	 * CI
	 */
	protected $CI;

	/**
	 * Construct
	 * 
	 * Load the CodeIgniter framework and load the general model.
	 */
	public function __construct() { 	
		$this->CI = &get_instance();
		$this->CI->load->model('general_model');
	}

	/**
	 * Random Data
	 * 
	 * Generate pseudo-random data of a specified length
	 * 
	 * @param		int
	 * @return		string
	 */
	public function random_data($length) {
		return openssl_random_pseudo_bytes($length);
	}
	
	/**
	 * Generate Salt
	 * 
	 * Generates a hash from random data.
	 * 
	 * @return		string
	 */
	public function generate_salt() {
		return bin2hex($this->random_data('32'));
	}

    /**
     * New Password
     *
     * Given $password, return the salt and hash.
     *
     * @param $password
     * @return array
     */
    public function new_password($password) {
        $rounds = '10';

        $salt = '$2a$'.$rounds.'$'.str_replace("+", "o", base64_encode(openssl_random_pseudo_bytes(22)));
        $hash = crypt($password, $salt);
        return array('hash' => $hash,
                    'salt' => $salt);
    }

    /**
     * Password
     *
     * Takes $given_password, the $salt, and returns a hash.
     * @param $given_password
     * @param $salt
     * @return string
     */
    public function password($given_password, $salt) {
        return crypt($given_password, $salt);
    }
    /**
	 * Unique Hash
	 * 
	 * Generates a unique hash, in the $table table, and column $column.
	 * Default length is 16 characters long.
	 * Generated by creating a salt, trimming it to the required length, 
	 * and checking if it's unique. Will loop until entry is unique.
	 * 
	 * @param		string	$table
	 * @param		string	$column
	 * @param		int		$length
	 * @return		string
	 */ 
	public function unique_hash($table, $column, $length = 16) {

		$hash = substr($this->generate_salt(), 0, $length);
		// Test the DB, see if the hash is unique. 
		$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);

		while($test == FALSE) {
            $hash = substr($this->generate_salt(), 0, $length);

			// Perform the test again, and see if the loop goes on.
			$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);	
		}

		// Finally return the generated unique hash.
		return $hash;			
	}
	
	/**
	 * Role from ID
	 * 
	 * Used to determine which role the ID relates to.
	 * 	1 - Buyer
	 *  2 - Vendor
	 *  3 - Admin
	 * 
	 * @param		int	$id
	 * @return		string
	 */
	public function role_from_id($id) {
		switch($id) {
			case '1':
				$result = 'Buyer';
				break;
			case '2':
				$result = 'Vendor';
				break;
			case '3':
				$result = 'Admin';
				break;
			default:
				$result = 'Buyer';
				break;
		}
		return $result;
	}
	
	/**
	 * Format Time
	 * 
	 * Create a human readable string of a timestamp.
	 * 
	 * @param		int	$timestamp
	 * @return		string
	 */
	public function format_time($timestamp) {
		// Load the current time, and check the difference between the times in seconds.
		$currentTime = time();
		$difference = $currentTime-$timestamp;
		if ($difference < 60) {					// within a minute.
			return 'less than a minute ago';
		} else if($difference < 120) {			// 60-120 seconds.
			return 'about a minute ago';
		} else if($difference < (60*60)) {		// Within the hour. 
			return round($difference / 60) . ' minutes ago';
		} else if($difference < (120*60)) {		// Within a few hours.
			return 'about an hour ago';
		} else if($difference < (24*60*60)) {		// Within a day.
			return 'about ' . round($difference / 3600) . ' hours ago';
		} else if($difference < (48*60*60)) {		// Just over a day.
			return '1 day ago';
		} else if($timestamp == "0" || $timestamp == NULL) { //The timestamp wasn't set which means it has never happened.
			return 'Never';
		} else { // Otherwise just return the basic date.
			return date('j F Y',(int)$timestamp);
		}
	}
	
};

 /* End of file General.php */
/* Location: application/libraries/General.php */