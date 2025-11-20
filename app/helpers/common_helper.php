<?php
if ( ! function_exists('xss_clean'))
{
	function xss_clean($string)
	{
		$LAVA = lava_instance();
		$LAVA->call->library('antixss');
		return $LAVA->antixss->xss_clean($string);
	}
}

if ( ! function_exists('set_flash_alert'))
{
	function set_flash_alert($alert, $message) {
		$LAVA = lava_instance();
		$LAVA->session->set_flashdata(array('alert' => $alert, 'message' => $message));
	}
}

if ( ! function_exists('flash_alert'))
{
	function flash_alert()
	{
		$LAVA = lava_instance();
		if($LAVA->session->flashdata('alert') !== NULL) {
			echo '
	        <div class="alert alert-' . $LAVA->session->flashdata('alert') . '">
	            <i class="icon-remove close" data-dismiss="alert"></i>
	            ' . $LAVA->session->flashdata('message') . '
	        </div>';
		}
			
	}
}
if ( ! function_exists('logged_in'))
{
	function logged_in() {
		$LAVA = lava_instance();
		$LAVA->call->library('lauth');
		return $LAVA->lauth->is_logged_in() ? true : false;
	}
}

if ( ! function_exists('get_user_id'))
{
	function get_user_id() {
		$LAVA = lava_instance();
		$LAVA->call->library('lauth');
		return $LAVA->lauth->get_user_id();
	}
}

if ( ! function_exists('get_username'))
{
	function get_username($user_id) {
		$LAVA = lava_instance();
		$LAVA->call->library('lauth');
		return $LAVA->lauth->get_username($user_id);
	}
}

if ( ! function_exists('get_role'))
{
	function get_role() {
		$LAVA = lava_instance();
		$LAVA->call->library('session');
		$role = $LAVA->session->userdata('role');
		return !empty($role) ? $role : 'user';
	}
}

if ( ! function_exists('user_is_admin'))
{
	function user_is_admin() {
		return logged_in() && get_role() === 'admin';
	}

	if (!function_exists('current_user_first_name')) {
		function current_user_first_name() {
			$LAVA = lava_instance();
			$full = $LAVA->session->userdata('user_name') ?? '';
			if (empty($full)) {
				return '';
			}
			$parts = preg_split('/\s+/', trim($full));
			return $parts[0] ?? $full;
		}
	}
}
if ( ! function_exists('email_exist'))
{
	function email_exist($email) {
		$LAVA = lava_instance();
		// normalize before checking to prevent false negatives/positives for gmail variants
		if (function_exists('normalize_email')) {
			$email = normalize_email($email);
		}
		$row = $LAVA->db->table('users')->where('email', $email)->get();
		return !empty($row);
	}
}
if (!function_exists('csrf_field')) {
    function csrf_field() {
	$CI = lava_instance();
        $name = $CI->security->get_csrf_token_name();
        $hash = $CI->security->get_csrf_hash();
        echo '<input type="hidden" name="'.$name.'" value="'.$hash.'">';
    }
}

if (!function_exists('normalize_email')) {
	/**
	 * Normalize email addresses to prevent duplicate Gmail variants.
	 * - Lowercases the address
	 * - For gmail/googlemail addresses, strips dots and +tags from the local part
	 */
	function normalize_email(string $email): string {
		$email = trim(strtolower($email));
		if ($email === '') return $email;
		if (strpos($email, '@') === false) return $email;
		list($local, $domain) = explode('@', $email, 2);
		if (in_array($domain, ['gmail.com','googlemail.com'], true)) {
			// remove +tag
			$plusPos = strpos($local, '+');
			if ($plusPos !== false) {
				$local = substr($local, 0, $plusPos);
			}
			// remove dots
			$local = str_replace('.', '', $local);
			return $local . '@' . $domain;
		}
		return $email;
	}
}
?>

