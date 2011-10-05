<?php
/*
	Plugin Name: Skt NURCaptcha
	Plugin URI: http://skt-nurcaptcha.sanskritstore.com/
	Description: If your Blog allows new subscribers to register via the registration option at the Login page, this plugin may be useful to you. It includes a reCaptcha block to the register form, so you get rid of spambots. To use it you have to sign up for (free) public and private keys at <a href="https://www.google.com/recaptcha/admin/create" target="_blank">reCAPTCHA API Signup Page</a>.
	Version: 1.1
	Author: Carlos E. G. Barbosa
	Author URI: http://www.yogaforum.org
	Text Domain: Skt_nurcaptcha
	License: GPL2
*/

// *******************************************************************

/*  Skt NURCaptcha - Copyright (c) 2011  Carlos E. G. Barbosa  
	(email : carlos.eduardo@mais.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
    
*/


load_plugin_textdomain('Skt_nurcaptcha', false, basename( dirname( __FILE__ ) ) . '/languages' );
add_action('admin_menu', 'skt_nurc_admin_page');
add_action( 'login_enqueue_scripts', 'skt_nurc_login_init' );
add_action ('login_form_register', 'skt_nurCaptcha');


function skt_nurc_admin_page() {
	$hook_suffix = add_options_page("Skt NURCaptcha", "Skt NURCaptcha", 'manage_options', "skt_nurcaptcha", "skt_nurc_admin");
	add_action( "admin_print_scripts-".$hook_suffix, 'skt_nurc_admin_init' );
}

function skt_nurc_admin_init() {
    wp_register_script( 'sktNURCScript', plugins_url('/js/skt-nurc-functions.js', __FILE__), array('jquery') );
	wp_enqueue_script('sktNURCScript');
}
function skt_nurc_login_init() {
    wp_register_script( 'NURCloginscript', plugins_url('/js/skt-nurc-login.js', __FILE__), array('jquery','scriptaculous') );
	wp_enqueue_script('NURCloginscript');
}
function skt_nurc_admin() {
	include('skt-nurc-admin.php');
}

function skt_nurCaptcha() {

	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( is_multisite() ) {
		// Multisite uses wp-signup.php - current version of this plugin doesn't go there...
		wp_redirect( apply_filters( 'wp_signup_location', site_url('wp-signup.php') ) );
		exit();
	}
	if ( !get_option('users_can_register') ) {
		wp_redirect( site_url('wp-login.php?registration=disabled') );
		exit();
	}
	if ((get_option('sktnurc_publkey')=='')||(get_option('sktnurc_privtkey')=='')) {return false;} // Plugin is disabled if no key is found
    $result = new ReCaptchaResponse(); // sets $result as a class variable
	$user_login = '';
	$user_email = '';
	if ( $http_post ) { // if we have a response, let's check it
		$user_login = $_POST['user_login'];
		$user_email = $_POST['user_email'];

		$result = recaptcha_check_answer(get_option('sktnurc_privtkey'), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );

	}
		if ($result->is_valid) { // captcha passed, so let's see the rest...
			$errors = register_new_user($user_login, $user_email);
			if ( !is_wp_error($errors) ) {
				$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?checkemail=registered';
				wp_safe_redirect( $redirect_to );
				exit(); // end of all procedures - job done!
			} 
		}

	$redirect_to = apply_filters( 'registration_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
	login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
	if (get_option('sktnurc_theme')!="clean"){$form_width ='320';}else{$form_width ='448';}
	
	if ((!$result->is_valid)and($result->error != '')) {
		
		echo '<div id="login_error"><strong>reCaptcha ERROR</strong>';
		echo ': '.sprintf( __("There is a problem with your response: %s", 'Skt_nurcaptcha'),$result->error);
		echo '<br></div>';
	}
		echo $redirect_to;
?> 
<form id="nurc_form" method="post" style="width:<?php echo $form_width ?>px">
<p><label><?php _e('Username', 'Skt_nurcaptcha') ?><br /><input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($user_login)) ?>" size="20" tabindex="10" /></label></p>
<p><label><?php _e('E-mail', 'Skt_nurcaptcha') ?><br /><input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($user_email)) ?>" size="25" tabindex="20" /></label></p>
<p><label><?php _e('Fill the Captcha below', 'Skt_nurcaptcha') ?></label></p>

<script type="text/javascript">
 var RecaptchaOptions = {
    theme : "<?php echo get_option('sktnurc_theme') ?>",
    lang: "<?php echo get_option('sktnurc_lang') ?>"
 };
</script>	
<script type="text/javascript"
     src="http://www.google.com/recaptcha/api/challenge?k=<?php echo get_option('sktnurc_publkey') ?>">
</script>
<noscript>
	<iframe src="http://www.google.com/recaptcha/api/noscript?k=<?php echo get_option('sktnurc_publkey') ?>" height="300" width="500" frameborder="0"></iframe><br>
	<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
</noscript>
<br />
<?php do_action('register_form'); ?>
	<p id="reg_passmail"><?php _e('A password will be e-mailed to you.', 'Skt_nurcaptcha') ?></p>
	<br class="clear" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
<p class="submit"><input class="button-primary" type="submit" id="wp-submit" value="<?php _e("Subscribe me", 'Skt_nurcaptcha') ?>" tabindex="100" /></p></form>

	<p id="nav">
		<a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in', 'Skt_nurcaptcha') ?></a> |
		<a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found', 'Skt_nurcaptcha') ?>"><?php _e('Lost your password?', 'Skt_nurcaptcha') ?></a>
	</p>
<?php
	login_footer('user_login');
	$file_dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
?>
<script src="<?php echo $file_dir; ?>/sktnurc-fn.js"></script>
<?php

	exit;

} 

/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                return array(false, "false \n".__('Could not open socket - server communication failed - try again later.', 'Skt_nurcaptcha'));
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}
/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}


/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
        var $is_valid;
        var $error;
}


/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return ReCaptchaResponse
  */
function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array())
{
    $recaptcha_response = new ReCaptchaResponse();
	
	$flag = false;
	if ($privkey == null || $privkey == '') {
		$flag = true;
		$flagged_r = sprintf(__("To use reCAPTCHA you must get an API key from %s", 'Skt_nurcaptcha'),"<a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
	}

	if ($remoteip == null || $remoteip == '') {
		$flag = true;
		$flagged_r = __("For security reasons, you must pass the remote ip to reCAPTCHA", 'Skt_nurcaptcha');
	}
        //discard spam submissions
	if ($challenge == null || strlen($challenge) == 0) {
		$flag = true;
		$flagged_r = __('Inconsistency detected - try again later.', 'Skt_nurcaptcha');
				$count = intval(get_option('sktnurc_count'));
				update_option('sktnurc_count',$count+1);
    }
	if ($response == null || strlen($response) == 0) {
		$flag = true;
		$flagged_r = __('Response field was empty!', 'Skt_nurcaptcha');
				$count = intval(get_option('sktnurc_count'));
				update_option('sktnurc_count',$count+1);
    }
	if ($flag) {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $flagged_r;
                return $recaptcha_response;
	}
	

        $response = _recaptcha_http_post ("www.google.com", "/recaptcha/api/verify",
                                          array (
                                                 'privatekey' => $privkey,
                                                 'remoteip' => $remoteip,
                                                 'challenge' => $challenge,
                                                 'response' => $response
                                                 ) + $extra_params
                                          );
	
        $answers = explode ("\n", $response [1]);

        if (trim ($answers [0]) == 'true') {
                $recaptcha_response->is_valid = true;
        }
        else {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $answers [1];
        }
        if ($recaptcha_response->error == 'incorrect-captcha-sol') {
        		$recaptcha_response->error = "Incorrect Captcha solution - please try again.";
				$count = intval(get_option('sktnurc_count'));
				update_option('sktnurc_count',$count+1);
        }
        return $recaptcha_response;

}



?>