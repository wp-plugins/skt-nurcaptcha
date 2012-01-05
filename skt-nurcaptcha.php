<?php
/*
	Plugin Name: Skt NURCaptcha
	Plugin URI: http://skt-nurcaptcha.sanskritstore.com/
	Description: If your Blog allows new subscribers to register via the registration option at the Login page, this plugin may be useful to you. It includes a reCaptcha block to the register form, so you get rid of spambots. To use it you have to sign up for (free) public and private keys at <a href="https://www.google.com/recaptcha/admin/create" target="_blank">reCAPTCHA API Signup Page</a>.
	Version: 2.4.3
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


load_plugin_textdomain('Skt_nurcaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
add_action('admin_menu', 'skt_nurc_admin_page');
add_action( 'login_enqueue_scripts', 'skt_nurc_login_init' );
add_action('login_form_register', 'skt_nurCaptcha');
add_action( 'bp_include', 'skt_nurc_bp_include_hook' ); // Please, do call me, but only if BuddyPress is active...
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'skt_nurc_settings_link', 30, 1);
if ( is_multisite() ) {
	add_action('preprocess_signup_form', 'nurCaptchaMU_preprocess');
	add_action('signup_extra_fields', 'nurCaptchaMU_extra');
	add_filter('wpmu_validate_user_signup', 'skt_nurc_validate_captcha', 999, 1);
}
if (( get_option('sktnurc_publkey')== '') or ( get_option('sktnurc_privtkey')== '' )) {
	skt_nurc_keys_alert();
}
function skt_nurc_bp_include_hook() {
	define('SKTNURC_BP_ACTIVE',true);
	add_action('bp_signup_validate', 'skt_nurc_bp_signup_validate');
	add_action('bp_before_registration_submit_buttons', 'skt_nurc_bp_before_registration_submit_buttons');
}

function skt_nurc_bp_signup_validate() {
    global $bp;
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new ReCaptchaResponse();	
		$nurc_result = recaptcha_check_answer(get_option('sktnurc_privtkey'), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		if (!$nurc_result->is_valid) {
			$log_res = nurc_log_attempt(); // log attemptive
			$temp = $nurc_result->error;
			$bp->signup->errors['skt_nurc_error'] = $temp;
		}	
	}	
	return;
}
function skt_nurc_bp_before_registration_submit_buttons() {
	echo '<div class="register-section"  id="profile-details-section">';
	nurCaptchaMU_extra();
	echo '</div>';
}
function nurCaptchaMU_preprocess() {
	if ((get_option('sktnurc_publkey')=='')||(get_option('sktnurc_privtkey')=='')) {
		die('<p class="error" style="font-weight:300"><strong>Security issue detected:</strong> reCAPTCHA configuration incomplete.<br /> Sorry! Signup will not be allowed until this problem is fixed. <br />Please try again later.</p>');
	}
}
function skt_nurc_settings_link($links) {
	$settings_link = "<a href='options-general.php?page=skt_nurcaptcha'>".__('Settings', 'Skt_nurcaptcha')."</a>";
	array_unshift($links,$settings_link);
	return $links;
}
function skt_nurc_admin_page() {
	$hook_suffix = add_options_page("Skt NURCaptcha", "Skt NURCaptcha", 'manage_options', "skt_nurcaptcha", "skt_nurc_admin");
	add_action( "admin_print_scripts-".$hook_suffix, 'skt_nurc_admin_init' );
}

function skt_nurc_admin_init() {
    wp_register_script( 'sktNURCScript', plugins_url('/js/skt-nurc-functions.js', __FILE__), array('jquery') );
	wp_enqueue_script('sktNURCScript');
}
function skt_nurc_login_init() {
	if ( !is_multisite() ) {
		wp_register_script( 'NURCloginscript', plugins_url('/js/skt-nurc-login.js', __FILE__), array('jquery','scriptaculous') );
		wp_enqueue_script('NURCloginscript');
	}
}
function skt_nurc_admin() {
	include('skt-nurc-admin.php');
}
function skt_nurc_keys_alert() {
	add_action('admin_notices','skt_nurc_setup_alert');
}
/****
*
* Alert in WPMU - if reCAPTCHA is not yet enabled by registering the free keys at the Settings Page 
* 
****/
function skt_nurc_setup_alert() {
	
	?><div id="setup_alert" class="updated"><p><strong><?php 
	_e('Skt NURCaptcha Warning', 'Skt_nurcaptcha' );
	?></strong><br /><?php
	_e('You must register your reCAPTCHA keys to have Skt NURCaptcha protection enabled.', 'Skt_nurcaptcha' );
	if (get_admin_page_title() != 'Skt NURCaptcha') {
		echo '<br />'.__('Go to', 'Skt_nurcaptcha')." <a href='options-general.php?page=skt_nurcaptcha'>".__('Skt NURCaptcha Settings', 'Skt_nurcaptcha')."</a> ". __( 'and save your keys to the appropriate fields', 'Skt_nurcaptcha' );
	} else {
		echo '<br />'. __( 'Be sure your keys are saved to the appropriate fields down here', 'Skt_nurcaptcha' );
	}
	?></strong></p></div><?php
}

/****
*
* Error box in WPMU - if reCAPTCHA is not correctly filled 
* 
****/
function nurCaptchaMU_extra() {  
	nurc_recaptcha_challenge();
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { 
		$nurc_result = new ReCaptchaResponse();	
		$nurc_result = recaptcha_check_answer(get_option('sktnurc_privtkey'), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'],array(), false );
		if (!$nurc_result->is_valid) { 
			$temp = $nurc_result->error;
			echo '<div class="error" style="font-weight:300"><strong>'. __('reCAPTCHA error', 'Skt_nurcaptcha') .'</strong>: '. $temp .'</div>';
		}
	}
}
/****
*
* Main routine - Multisite (WPMU) *** 
* 
****/
function skt_nurc_validate_captcha($result) {  
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new ReCaptchaResponse();	
		$nurc_result = recaptcha_check_answer(get_option('sktnurc_privtkey'), $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		if (!$nurc_result->is_valid) {
			$log_res = nurc_log_attempt();
			$temp = $nurc_result->error;
			extract($result);
			$errors->add('skt_nurc_error', $temp);
			$result = array('user_name' => $user_name, 'orig_username' => $orig_username, 'user_email' => $user_email, 'errors' => $errors);
		}
	}	
return $result;
}

/****
*
* Main routine - non-multisite *** 
* This code overrides entirely the 'register' case on main switch @ wp_login.php 
* we fetch the 'login-form-register' hook 
* You may experience some problems if you install another plugin that needs to customize those lines.
*
****/
function skt_nurCaptcha() {

	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( is_multisite() ) {
		// Multisite uses wp-signup.php 
		wp_redirect( apply_filters( 'wp_signup_location', site_url('wp-signup.php') ) );
		exit();
	}
	if ( !get_option('users_can_register') ) {
		wp_redirect( site_url('wp-login.php?registration=disabled') );
		exit();
	}
		// Plugin is disabled if one or both reCaptcha keys are missing: 
	if ((get_option('sktnurc_publkey')=='')||(get_option('sktnurc_privtkey')=='')) {return false;} 
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
		$log_res = nurc_log_attempt(); // register attemptive in log file
		echo '<div id="login_error"><strong>reCaptcha ERROR</strong>';
		echo ': '.sprintf( __("There is a problem with your response: %s", 'Skt_nurcaptcha'),$result->error);
		echo '<br></div>';
	}
		echo $redirect_to;
	?> 
<form id="nurc_form" method="post" style="width:<?php echo $form_width ?>px">
<p><label><?php _e('Username', 'Skt_nurcaptcha') ?><br /><input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($user_login)) ?>" size="20" tabindex="10" /></label></p>
<p><label><?php _e('E-mail', 'Skt_nurcaptcha') ?><br /><input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($user_email)) ?>" size="25" tabindex="20" /></label></p>

	<?php 
		nurc_recaptcha_challenge(); 
		do_action('register_form'); 
	?>
    
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
<script src="<?php echo $file_dir; ?>js/sktnurc-fn.js"></script>
<?php

	exit;

} 
/*****
*
* End main
*
**/

function nurc_make_path() {
		$nurc_pathinfo = pathinfo(realpath(__FILE__)); // get array of directory realpath on server 
		$npath = $nurc_pathinfo['dirname']."/"; // prepare realpath to base plugin directory
		return $npath;
}
function nurc_make_log_path() {
		$npath = (nurc_make_path())."nurcaptchalog.txt"; // prepare path to manage log file
		return $npath;
}
function nurc_recaptcha_challenge() {
		?>
		<p><label><?php _e('Fill the Captcha below', 'Skt_nurcaptcha') ?></label></p>

		<script type="text/javascript">
		 var RecaptchaOptions = {
 		   theme : "<?php echo get_option('sktnurc_theme') ?>",
 		   lang: "<?php echo get_option('sktnurc_lang') ?>"
		 };
		</script>	
		<script type="text/javascript"
		     src="http://www.google.com/recaptcha/api/challenge?k=<?php echo get_option('sktnurc_publkey'); ?>">
		</script>
		<noscript>
			<iframe src="http://www.google.com/recaptcha/api/noscript?k=<?php echo get_option('sktnurc_publkey'); ?>" height="300" width="500" frameborder="0"></iframe><br>
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
		</noscript>
		<br />
        <?php 
}
/**
 * Writes log on file
 */
function nurc_log_attempt() {
		$ue = $_POST['user_email']; 
		if ( is_multisite() ) {		
			$ul = $_POST['user_name'];
		}else{
			$ul = $_POST['user_login'];
		}
		if (defined('SKTNURC_BP_ACTIVE')) {
			$ue = $_POST['signup_email'];
			$ul = $_POST['signup_username'];
		}
		if ($ue == '') {$ue = '  ...  ';}
		if ($ul == '') {$ul = '  ...  ';}
		$npath = nurc_make_log_path();
		$logtime = current_time("mysql",0);
		$logline = $logtime . " &raquo;&emsp; email: &lt;<strong>". $ue ."</strong>&gt; &rarr; username: <strong>". $ul . "</strong>\r\n";
		$handle = fopen($npath, "a+t");
		if ($handle == false) { 
			$logline.= " - Unable to open file!";
			return $logline;
		}
		if (flock($handle, LOCK_EX)) {
			fputs($handle,$logline);
    		flock($handle, LOCK_UN);
			$resp = true;
		} else {
    		$resp = "Couldn't get the lock!";
		}
		fclose($handle);
		return $resp;
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
function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array(), $add_count = true)
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
				if ($add_count) {
					$count = intval(get_option('sktnurc_count'));
					update_option('sktnurc_count',$count+1);
				}
    }
	if ($response == null || strlen($response) == 0) {
		$flag = true;
		$flagged_r = __('Response field was empty!', 'Skt_nurcaptcha');
				if ($add_count) {
					$count = intval(get_option('sktnurc_count'));
					update_option('sktnurc_count',$count+1);
				}
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
        		$recaptcha_response->error = __("Incorrect Captcha solution - please try again.", 'Skt_nurcaptcha');
				if ($add_count) {
					$count = intval(get_option('sktnurc_count'));
					update_option('sktnurc_count',$count+1);
				}
        }
        return $recaptcha_response;

}


?>