<?php
/*
	Plugin Name: Skt NURCaptcha
	Plugin URI: http://skt-nurcaptcha.sanskritstore.com/
	Description: If your Blog allows new subscribers to register via the registration option at the Login page, this plugin may be useful to you. It includes a reCaptcha block to the register form, so you get rid of spambots. To use it you have to sign up for (free) public and private keys at <a href="https://www.google.com/recaptcha/admin#createsite" target="_blank">reCAPTCHA API Signup Page</a>. Version 3 added extra security by querying antispam databases for known ip and email of spammers, so you get rid of them even if they break the reCaptcha challenge by solving it as real persons.
	Version: 3.4.4
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


global $sktnurclog_db_version; $sktnurclog_db_version = "3.1";
load_plugin_textdomain( 'Skt_nurcaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
add_action( 'admin_menu', 'skt_nurc_admin_page' );
add_action( 'login_enqueue_scripts', 'skt_nurc_login_init' );
add_action( 'login_form_register', 'skt_nurCaptcha' );
add_action( 'bp_include', 'skt_nurc_bp_include_hook' ); // Please, do call me, but only if BuddyPress is active...
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'skt_nurc_settings_link', 30, 1 );
if((get_site_option('sktnurc_recaptcha_version')=="new") && (get_site_option('sktnurc_login_recaptcha')=="true")){
	//add_filter( 'authenticate', 'skt_nurc_login_checkout', 1, 3 );
	add_filter( 'wp_authenticate_user', 'skt_nurc_login_checkout',10,2 );
	add_action( 'login_form','skt_nurc_login_add_recaptcha' );
}
$temp = get_site_option('sktnurc_custom_page_list');
if (!empty($temp))
	add_action('wp_head', 'skt_nurc_enable_page_captcha');
if ( is_multisite() && (! is_admin())) {
	if(get_site_option('sktnurc_recaptcha_version')=="new"){
		add_action( 'signup_header', 'skt_nurc_MU_signup_enqueue' );
	}
	add_action( 'preprocess_signup_form', 'nurCaptchaMU_preprocess' );
	add_action( 'signup_extra_fields', 'nurCaptchaMU_extra',30,1 );
		// located @ wp-signup.php line 179 :: WP v 3.6
	add_filter( 'wpmu_validate_user_signup', 'skt_nurc_validate_captcha', 999, 1 );
		// located @ wp-includes/ms-functions.php line 509 :: WP v 3.6
}
/*
* Missing keys advert
*/
if (( get_site_option('sktnurc_publkey')== '') or ( get_site_option('sktnurc_privtkey')== '' )) {
	skt_nurc_keys_alert();
}
/*
* Login routines
*/
function skt_nurc_login_init() {
	if ( !is_multisite() ) {
		wp_register_script( 'NURCloginscript', plugins_url('/js/skt-nurc-login.js', __FILE__), array('jquery','scriptaculous') );
		wp_enqueue_script('NURCloginscript');
	}
	if(get_site_option('sktnurc_recaptcha_version')=="new"){
		if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
		$lang = '';
		if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang = "?hl=".get_site_option('sktnurc_recaptcha_language');
		wp_enqueue_script('NURCloginDisplay', "https://www.google.com/recaptcha/api.js".$lang);
    	wp_enqueue_style( 'NURCcustom-login', plugins_url('/skt-nurc-login-style.css', __FILE__) );
	}
}
function skt_nurc_login_add_recaptcha(){
	// check for Google's keys to enable reCAPTCHA on login form
	if(( get_site_option('sktnurc_publkey')!= '') and ( get_site_option('sktnurc_privtkey')!= '' )) {
		nurc_recaptcha_challenge(false);
	}
}
/*
* Login page and custom login page functions
*/
function skt_nurc_login_checkout($user, $password) {
	if ( is_wp_error($user) )
           return $user;
	$privtkey = get_site_option('sktnurc_privtkey');
	if (($privtkey == '')or(get_site_option('sktnurc_publkey')== '')) return $user; // disable checking if keys are not registered
	$user_ip_address =  $_SERVER['REMOTE_ADDR'];
	$response_string = $_POST['g-recaptcha-response'];	
	$query_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privtkey&response=$response_string&remoteip=$user_ip_address";
	$json_data = skt_nurc_get_page($query_url);
	$obj = json_decode($json_data);
	if (trim($obj->{"success"})==true)
		return $user;
	$result = implode(", ", $obj->{"error-codes"}); // this value is an array, so implode it!
	// $result is reserved for future use
	$errors = new WP_Error();
	$log_res = nurc_log_attempt('login-reCAPTCHA', $user); // log attemptive
	$errors->add('reCAPTCHA error', __('There was an error in your captcha response.', 'Skt_nurcaptcha'));
	return $errors;
}
/* ******************
* This function renders code to the <head> section every page in the site, as to enable reCAPTCHA sitewide
* to activate it place this code anywhere in the theme's functions.php file:
add_action('wp_head', 'skt_nurc_sitewide_enable_captcha');
**************** */
function skt_nurc_sitewide_enable_captcha(){ // enables captcha sitewide, avoiding duplicity on listed custom pages
	$form_pages = get_site_option('sktnurc_custom_page_list');
	if (is_page($form_pages)) return;
	skt_nurc_core_enable_captcha();
}
// ****************
function skt_nurc_enable_page_captcha() { // checks if currently displayed page is listed as a custom page 
	$form_pages = get_site_option('sktnurc_custom_page_list');
	if (is_page($form_pages)) skt_nurc_core_enable_captcha();
}
function skt_nurc_core_enable_captcha(){
	global $wp_version;
	if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
	$lang = "?ver=$wp_version";
	if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang .= "&hl=".get_site_option('sktnurc_recaptcha_language');
	echo "<script  type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js".$lang."\" async defer></script>";
}
/*
* Admin page functions
*/
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
function skt_nurc_admin() {
	include('skt-nurc-admin.php');
}
function skt_nurc_keys_alert() {
	add_action('admin_notices','skt_nurc_setup_alert');
}
/**
 * gets a URL where the user can sign up for reCAPTCHA. 
 */
function nurc_recaptcha_get_signup_url () {
	return "https://www.google.com/recaptcha/admin#createsite";
}

/*
* BuddyPress functions
*/
function skt_nurc_bp_include_hook() {
	define('SKTNURC_BP_ACTIVE',true);
	add_action( 'bp_signup_validate', 'skt_nurc_bp_signup_validate' ); 
		// located @ bp-members/bp-members-screens.php line 146 :: BP v 1.9.1
	add_action( 'bp_signup_profile_fields', 'skt_nurc_bp_signup_profile_fields' ); 
		// located @ bp_themes/bp_default/registration/register.php line 194 :: BP v 1.9.1
	add_action( 'wp_enqueue_scripts', 'skt_nurc_bp_register_script' );
}
function skt_nurc_bp_register_script () {
	if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
	if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang .= "?hl=".get_site_option('sktnurc_recaptcha_language');
	if (bp_is_register_page()) {
		wp_enqueue_script( 'NurcBPregisterDisplay', "https://www.google.com/recaptcha/api.js".$lang );
	}
}
function skt_nurc_bp_signup_validate() {
    global $bp;
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new nurc_ReCaptchaResponse();	
		$privtkey = get_site_option('sktnurc_privtkey');
		$user_ip_address =  $_SERVER['REMOTE_ADDR'];
		if(get_site_option('sktnurc_recaptcha_version')!="new"){
			$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		}else{
			$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['g-recaptcha-response'] );
		}
		if ($nurc_result->is_valid) {
			$usrx = ''; //$_POST['signup_username']
			$nurc_result = skt_nurc_antispam($_POST['signup_email'], $usrx, $nurc_result);
		}
		if (!$nurc_result->is_valid) {
			$processID = skt_nurc_select_procid($nurc_result);
			$log_res = nurc_log_attempt($processID); // log attemptive
			$temp = $nurc_result->error;
			$bp->signup->errors['skt_nurc_error'] = $temp;
		}	
	}	
	return;
}
function skt_nurc_bp_signup_profile_fields() {
	echo '<div class="register-section" >';
    global $bp; ?>
    <label><?php _e('Fill the Captcha below', 'Skt_nurcaptcha') ?></label>
    <?php
	if($temp = $bp->signup->errors['skt_nurc_error'])
		nurCaptchaMU_extra_output($temp);
	nurc_recaptcha_challenge(NULL,false);
	echo '</div>';
}
/*
* WPMU - Multisite functions
*/
function skt_nurc_MU_signup_enqueue(){
	wp_enqueue_script( 'NurcBPregisterDisplay', "https://www.google.com/recaptcha/api.js" );
}
function nurCaptchaMU_preprocess() {
	if ((get_site_option('sktnurc_publkey')=='')||(get_site_option('sktnurc_privtkey')=='')) {
		die('<p class="error" style="font-weight:300"><strong>Security issue detected:</strong> reCAPTCHA configuration incomplete.<br /> Sorry! Signup will not be allowed until this problem is fixed. <br />Please try again later.</p>');
	}
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
* Error box in WPMU - if reCAPTCHA is not correctly filled or if spam signature check is positive 
* 
****/
function nurCaptchaMU_extra($errors = array()) {
	nurc_recaptcha_challenge();
	if($temp = $errors->get_error_message('skt_nurc_error')) 
			nurCaptchaMU_extra_output($temp);
}
/****
*
* Error box output - Multisite (WPMU) *** 
* 
****/
function nurCaptchaMU_extra_output($error_msg = '') {
	echo '<div class="error" style="font-weight:300"><strong>';
	echo __('reCaptcha ERROR', 'Skt_nurcaptcha') .'</strong>:<br /> ';
	echo $error_msg . '</div>';	
}
/****
*
* Main routine - Multisite (WPMU) *** 
* 
****/
function skt_nurc_validate_captcha($result) { 
		/*  
			we start by checking if this function has been called by function validate_blog_signup() 
		 	-> located @ wp-signup.php line 454 :: WP v 3.6
		 	if call is from this function, it is a second check - NURCaptcha is not needed
		*/
		$callerfunc = skt_nurc_getCallingFunctionName(true);
		$pos = strpos($callerfunc, "validate_blog_signup");
		
		if($pos !== false) {
			return $result;
		} // this is a second check on username & email - so skip NURCaptcha
	// check if is there a BuddyPress installation active. If so, skip this routine.
	if(defined('SKTNURC_BP_ACTIVE')) return $result; 
	// now it's all OK! 
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new nurc_ReCaptchaResponse();	
		$privtkey = get_site_option('sktnurc_privtkey');
		$user_ip_address =  $_SERVER['REMOTE_ADDR'];
		if(get_site_option('sktnurc_recaptcha_version')!="new"){
			$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		}else{
			$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['g-recaptcha-response'] );
		}
		if ($nurc_result->is_valid) {
			$usrx = ''; //$user_name
			$user_email = $_POST['user_email']; 
			$nurc_result = skt_nurc_antispam($user_email, $usrx, $nurc_result);
		}
		if (!$nurc_result->is_valid) {
			$processID = skt_nurc_select_procid($nurc_result);
			$log_res = nurc_log_attempt($processID);
			$temp = $nurc_result->error;
			extract($result);
			if ( !is_wp_error($errors) )
				$errors = new WP_Error();
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
		wp_redirect( apply_filters( 'wp_signup_location', network_site_url('wp-signup.php') ) );
		exit();
	}
	
	if ( !get_site_option('users_can_register') ) {
		wp_redirect( site_url('wp-login.php?registration=disabled') );
		exit();
	}
		// Plugin is disabled if one or both reCaptcha keys are missing: 
	if ((get_site_option('sktnurc_publkey')=='')||(get_site_option('sktnurc_privtkey')=='')) {return false;}
	 
    $result = new nurc_ReCaptchaResponse(); // sets $result as a class variable
	$user_login = '';
	$user_email = '';
	if ( $http_post ) { // if we have a response, let's check it
		$user_login = $_POST['user_login'];
		$user_email = $_POST['user_email'];
		if (($user_login =='')||($user_email=='')){
			$result->is_valid = false;
			$result->error = __("username or email missing", 'Skt_nurcaptcha'); 
		}
		if ($result->is_valid) {
			$privtkey = get_site_option('sktnurc_privtkey');
			$user_ip_address =  $_SERVER['REMOTE_ADDR'];
			if(get_site_option('sktnurc_recaptcha_version')!="new"){
				$result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
			}else{
				$result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['g-recaptcha-response'] );
			}
		}
		// let's check antispammer databases, if reCAPTCHA is ok...
		if ($result->is_valid) { 
			$usrx = ''; //$user_login will not be checked.
			$result = skt_nurc_antispam($user_email, $usrx, $result); 
			}
		// hook for extra checks on username and user_email, if needed
		if ($result->is_valid) {
			do_action('sktnurc_before_register_new_user', $result, $user_login, $user_email);
		}
		  
		if ($result->is_valid) { // captcha and botscout passed, so let's try to register the new user...
			if ( !function_exists('sktnurc_register_new_user') ) { 
				$errors = register_new_user($user_login, $user_email);
			}else{
				// if you want to customize registration, create a function for that with this name:
				$errors = sktnurc_register_new_user($user_login, $user_email);				
			}
			if ( !is_wp_error($errors) ) {
				$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?checkemail=registered';
				wp_safe_redirect( $redirect_to );
				exit(); // end of all procedures - job done!
			} 
		}

	}
	$registration_redirect = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
	/**
	 * Filter the registration redirect URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $registration_redirect The redirect destination URL.
	 */
	$redirect_to = apply_filters( 'registration_redirect', $registration_redirect );
	login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
	
	if (get_site_option('sktnurc_theme')!="clean"){$form_width ='320';}else{$form_width ='448';}
	
	if ((!$result->is_valid)and($result->error != '')) {
		
		$processID = skt_nurc_select_procid($result);
		$log_res = nurc_log_attempt($processID); // register attemptive in log file
		echo '<div id="login_error"><strong>reCaptcha ERROR</strong>';
		echo ': '.sprintf( __("There is a problem with your response: %s", 'Skt_nurcaptcha'),$result->error);
		echo '<br></div>';
	}

	?> 
<form id="nurc_form" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>"  method="post" 
	<?php if (get_site_option('sktnurc_recaptcha_version')!="new"){ ?>
    	style="width:<?php echo $form_width; ?>px;" 
    <?php }else{ ?>
    	style="width:300px;" 
    <?php } ?> novalidate="novalidate">
	<p>
        <label><?php _e('Username', 'Skt_nurcaptcha'); ?><?php nurc_username_help(); ?>
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(wp_unslash($user_login)); ?>" size="20" />
        </label>
    </p>
	<p>
    	<label><?php _e('E-mail', 'Skt_nurcaptcha'); ?><?php nurc_email_help(); ?>
        <input type="email" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(wp_unslash($user_email)); ?>" size="25" />
        </label>
    </p>

	<?php 
	nurc_recaptcha_challenge(); 
	do_action('register_form'); 
	?>
    
	<p id="reg_passmail"><?php _e('A password will be e-mailed to you.', 'Skt_nurcaptcha'); ?></p>
	<br class="clear" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php 
	if (get_site_option('sktnurc_regbutton')==""){
		esc_attr_e('Register', 'Skt_nurcaptcha'); 
	} else {
		echo get_site_option('sktnurc_regbutton');
	}
	?>" /></p></form>

<p id="nav">
<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e('Log in', 'Skt_nurcaptcha'); ?></a> |
<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e('Password Lost and Found', 'Skt_nurcaptcha'); ?>"><?php _e('Lost your password?', 'Skt_nurcaptcha'); ?></a>
</p>
<?php
login_footer('user_login');
exit;

} 
/*****
*
* End main
*
**/

function nurc_username_help() {
	if (get_site_option('sktnurc_usrhlp_opt')=='true') return;
	?>
    <span id="username-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="username-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left; font-weight:normal;">
    		<?php 
			echo sktnurc_username_help_text();
			?>
        </p>
    </div>
    <?php 
}
function nurc_email_help() {
	if (get_site_option('sktnurc_emlhlp_opt')=='true') return;
	?>
    <span id="email-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="email-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left">
    		<?php 
			echo sktnurc_email_help_text();
			?>
        </p>
    </div>
    <?php 
}
function nurc_reCaptcha_help() {
	if (get_site_option('sktnurc_rechlp_opt')=='true') return;
	if ( is_multisite() ) return;
	if (defined('SKTNURC_BP_ACTIVE')) return;
	?>
    <span id="recaptcha-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="recaptcha-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left">
    		<?php 
			echo sktnurc_reCaptcha_help_text();
			?>
        </p>
    </div>
    <div style="clear:both"></div>
    <?php 
}
function nurc_make_path() {
		$nurc_pathinfo = pathinfo(realpath(__FILE__)); // get array of directory realpath on server 
		$npath = $nurc_pathinfo['dirname']."/"; // prepare realpath to base plugin directory
		return $npath;
}

/************ get help text *****
 *
 * Next three methods get help text that is displayed at the register form
 * They can be customized via Admin Panel
 * You need not change these strings 
 *
 *************/
function sktnurc_username_help_text(){
	$output = stripslashes(get_site_option('sktnurc_username_help'));
	if ($output == ""){
		$output = __('Use only non-accented alphanumeric characters plus these: _ [underscore], [space], . [dot], - [hyphen], * [asterisk], and @ [at]', 'Skt_nurcaptcha'); 
	}
	return $output;
}
function sktnurc_email_help_text(){
	$output = stripslashes(get_site_option('sktnurc_email_help'));
	if ($output == ""){
		$output = __('Use a functional email address, as your temporary password will be sent to that email', 'Skt_nurcaptcha'); 
	}
	return $output;
}
function sktnurc_reCaptcha_help_text(){
	if(get_site_option('sktnurc_recaptcha_version')=="old"){
		$output = stripslashes(get_site_option('sktnurc_reCaptcha_help'));
		if ($output == ""){
			$output = __('To get registered, just transcribe both the words, numbers and signs you see in the box below, to the small text field under it, no matter how absurd they look like, just to make clear you are a human being trying to register to this site. We welcome you, but we must keep out all spambots.', 'Skt_nurcaptcha'); 
		}
	}else{
		$output = stripslashes(get_site_option('sktnurc_v2_reCaptcha_help'));
		if ($output == ""){
			$output = __("To get registered, just click on the box below to confirm you're not a spam robot. If after that you're prompted with another challenge, just transcribe the words, numbers and signs you see in the image to the small text field over it, no matter how absurd they look like, just to make clear you are a human being trying to register to this site. We welcome you, but we must keep out all spambots.", 'Skt_nurcaptcha'); 
		}
	}
	return $output;
}

/************ for your custom code *****
 *
 * This function is used to display the reCAPTCHA challenge
 * You may call it from anywhere, including other plugins or theme pages
 * To check the results, you may use function nurcResponse() below 
 *
 *************/
function nurcRecaptcha(){
	nurc_recaptcha_challenge(false,false);
}
// *************
function nurc_recaptcha_challenge($use_help = true, $use_label = true) {
	if(get_site_option('sktnurc_recaptcha_version')!="new"){
		update_site_option('sktnurc_recaptcha_version', "old");
		if ($use_label) { ?>
			<p><label><?php _e('Fill the Captcha below', 'Skt_nurcaptcha') ?></label><?php if($use_help) nurc_reCaptcha_help(); ?></p>
		<?php } ?>
		<script type="text/javascript">
		 var RecaptchaOptions = {
			<?php 
				$temp = get_site_option('sktnurc_reclocales_lang');
				if ($temp == ''){
					include('skt-nurc-recaptcha-locales.php');
					update_site_option('sktnurc_reclocales_lang',$sktnurc_reclocales_strings);
					$sktnurc_cst_strings = $sktnurc_reclocales_strings[get_site_option('sktnurc_lang')];
				}else{
					$sktnurc_cst_strings = $temp[get_site_option('sktnurc_lang')];
				}
			?>
                custom_translations : {
                        visual_challenge : "<?php echo $sktnurc_cst_strings[0] ?>",
                        audio_challenge : "<?php echo $sktnurc_cst_strings[1] ?>",
                        refresh_btn : "<?php echo $sktnurc_cst_strings[2] ?>",
                        instructions_visual : "<?php echo $sktnurc_cst_strings[3] ?>",
                        instructions_context : "<?php echo $sktnurc_cst_strings[4] ?>",
                        instructions_audio : "<?php echo $sktnurc_cst_strings[5] ?>",
                        help_btn : "<?php echo $sktnurc_cst_strings[6] ?>",
                        play_again : "<?php echo $sktnurc_cst_strings[7] ?>",
                        cant_hear_this : "<?php echo $sktnurc_cst_strings[8] ?>",
                        incorrect_try_again : "<?php echo $sktnurc_cst_strings[9] ?>",
                        image_alt_text : "<?php echo $sktnurc_cst_strings[10] ?>",
                },
 		   		lang : '<?php echo get_site_option('sktnurc_lang') ?>',
 		   		theme : '<?php echo get_site_option('sktnurc_theme') ?>'
		 }
		</script>	
		<script type="text/javascript"
		     src="https://www.google.com/recaptcha/api/challenge?k=<?php echo get_site_option('sktnurc_publkey'); ?>">
		</script>
		<noscript>
			<iframe src="https://www.google.com/recaptcha/api/noscript?k=<?php echo get_site_option('sktnurc_publkey'); ?>" height="300" width="500" frameborder="0"></iframe><br />
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
		</noscript>
		<br />
        <?php 
	}else{ // if new recaptcha is used
		if ($use_label) { ?>
		<p><label><?php _e('Fill the Captcha below', 'Skt_nurcaptcha') ?></label><?php if($use_help) nurc_reCaptcha_help(); ?></p>
		<?php }
		if (get_site_option('sktnurc_data_theme') == '') {
			update_site_option('sktnurc_data_theme','light');
		}
		if (get_site_option('sktnurc_data_type') == '') {
			update_site_option('sktnurc_data_type','image');
		}
		?>
      	<div class="g-recaptcha" 
      		data-sitekey="<?php echo get_site_option('sktnurc_publkey'); ?>"
            data-theme="<?php echo get_site_option('sktnurc_data_theme'); ?>"
      		data-type="<?php echo get_site_option('sktnurc_data_type'); ?>"
            style="padding-bottom:12px;"
            ></div>
        <?php 
		
	}
}

/************ for your custom code *****
 * function nurcResponse()
 *
 * This function is used to get the results of a reCAPTCHA challenge posted
 * you may call it from another plugin or custom code on your template.
 * just place a code like this on the landing page to where the form data 
 * is sent after being posted:
 *		if ('POST' == $_SERVER['REQUEST_METHOD']){
 *         $result = nurcResponse();
 *         if ($result->is_valid){
 *             // answer is correct - so let's do something else...
 *         }else{
 *             // answer is incorrect - show error message and block the way out...
 *         }
 *		}
 ************/
function nurcResponse() {
	$check = new nurc_ReCaptchaResponse();
	if(get_site_option('sktnurc_recaptcha_version')!="new"){
		$check = nurc_recaptcha_check_answer(get_option('sktnurc_privtkey'), 
											$_SERVER['REMOTE_ADDR'], 
											$_POST['recaptcha_challenge_field'], 
											$_POST['recaptcha_response_field'] );
	}else{
		$check = nurc_recaptcha_check_answer(get_option('sktnurc_privtkey'), 
											$_SERVER['REMOTE_ADDR'], 
											$_POST['g-recaptcha-response'] );
	}
	return $check;
}

/**
 * Writes log into db table
 */
function nurc_log_attempt($processID = '', $user = NULL) {
		global $wpdb;
		$table_name = $wpdb->prefix . "sktnurclog";
		
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
		$logtime = current_time("mysql",0);
		if ($user != '') {
			$ue = $user->user_email;
			$ul = $user->user_login; 
			}
		//
		// ****  Insert data into database table
		$rows_affected = $wpdb->insert( $table_name, array( 'time' => $logtime, 'username' => $ul, 'email' => $ue, 'ip' => $_SERVER['REMOTE_ADDR'], 'procid' => $processID ) );
		// ***
		return $rows_affected;
}


/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function nurc_recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = nurc__recaptcha_qsencode ($data);

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
function nurc__recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}

/**
 * A nurc_ReCaptchaResponse is returned from nurc_recaptcha_check_answer()
 */
class nurc_ReCaptchaResponse {
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
  * @return nurc_ReCaptchaResponse
  */
function nurc_recaptcha_check_answer ($privkey, $remoteip, $challenge, $response = NULL, $extra_params = array(), $add_count = true){

	$recaptcha_response = new nurc_ReCaptchaResponse();
	if(get_site_option('sktnurc_recaptcha_version')!="new"){
		
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
		}
		if ($response == null || strlen($response) == 0) {
			$flag = true;
			$flagged_r = __('Response field was empty!', 'Skt_nurcaptcha');
		}
		if ($flag) {
					$recaptcha_response->is_valid = false;
					$recaptcha_response->error = $flagged_r;
					return $recaptcha_response;
		}
		$response = nurc_recaptcha_http_post ("www.google.com", "/recaptcha/api/verify",
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
		}
	}else{
		$response_string = $challenge; // the third variable will be the response string, with the new version 
		$query_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privkey&response=$response_string&remoteip=$remoteip";
		$json_data = skt_nurc_get_page($query_url);
		$obj = json_decode($json_data);
		$recaptcha_response->is_valid = false;
		if (trim($obj->{"success"})==true){
			$recaptcha_response->is_valid = true;
		}else{
			if(is_array($obj->{"error-codes"})){ // this value may be an array
				$recaptcha_response->error = implode(", ", $obj->{"error-codes"}); // so lets turn it into a string
			}else{
				$recaptcha_response->error = $obj->{"error-codes"};
			}
		}
	}
	return $recaptcha_response;
}
/**** 
*
* ***  StopForumSpam.com routine  *** 
* 
****/
function skt_nurc_check_stopforumspam($XMAIL = '', $XIP = '', $XNAME = '') {
    $stopforumspam_response = new nurc_ReCaptchaResponse();
	$returned_data=''; $botdata='';
	
	$XMAIL = urlencode($XMAIL); // make url compliant with urlencode()
	$test_string = "http://www.stopforumspam.com/api?ip=$XIP&email=$XMAIL&username=$XNAME";
	
	$xmlstr = skt_nurc_get_page($test_string);
	$xml = simplexml_load_string($xmlstr);
	
	if($xml->appears[0] == '' || $xml->appears[1] == '' || $xml->appears[2] == ''){ 
		$stopforumspam_response->is_valid = false;
		$stopforumspam_response->error = 'StopForumSpam '. __("error: No return data from query. Try again later.", 'Skt_nurcaptcha').'<strong>';
	}
	if($xml->appears[0] == 'yes' || $xml->appears[1] == 'yes' || $xml->appears[2] == 'yes'){ 
		$stopforumspam_response->is_valid = false;
		$stopforumspam_response->error = 'StopForumSpam '. __("says: 'Spammer signature found!' Registration will not be allowed for this user ", 'Skt_nurcaptcha').'<strong>';
		for ($i = 0; $i < 3; $i++) {
			if($xml->appears[$i] == 'yes'){
				$stopforumspam_response->error .= ':: '. $xml->type[$i] . ' ' . __("is suspect", 'Skt_nurcaptcha') . ' ';
				$stopforumspam_response->error .= '[' . $xml->frequency[$i] . ' ' . __("occurrences found", 'Skt_nurcaptcha') .'] ';
			}
		}
		$stopforumspam_response->error .= '</strong>';
	} else {
    	$stopforumspam_response->is_valid = true;
	}
	return $stopforumspam_response; 
		
} // here ends: function skt_nurc_check_stopforumspam()

/**** 
*
* ***  BotScout.com routine  *** 
* 
****/
function skt_nurc_check_botscout($XMAIL = '', $XIP = '', $XNAME = '') {
    $botscout_response = new nurc_ReCaptchaResponse();
	$returned_data=''; $botdata='';
	$APIKEY = get_site_option('sktnurc_botscoutKey');
	
	$XMAIL = urlencode($XMAIL); // make url compliant with urlencode()
	if ($XNAME == ''){
		$test_string = "http://botscout.com/test/?multi&mail=$XMAIL";
	}else{
		$test_string = "http://botscout.com/test/?multi&name=$XNAME&mail=$XMAIL";
	}
	if(get_site_option('sktnurc_botscoutTestMode')==false){$test_string .= "&ip=$XIP";}
	if($APIKEY != ''){   // append API key.
		$test_string .= "&key=$APIKEY";
	}
	
	$returned_data = skt_nurc_get_page($test_string);

	if($returned_data==''){ 
        $botscout_response->is_valid = false;
        $botscout_response->error = 'BotScout ' . __("error: No return data from API query.", 'Skt_nurcaptcha');
		return $botscout_response;
	} 
 
	if(substr($returned_data, 0,1) == '!'){
		// the first character is an exclamation mark, an error occurred!  
        $botscout_response->is_valid = false;
        $botscout_response->error = 'BotScout ' . sprintf(__("error: %s", 'Skt_nurcaptcha'),$returned_data);
		return $botscout_response;
	}
	$botdata = explode('|', $returned_data); 
	// $botdata[0] - 'Y' if found in database, 'N' if not found, '!' if an error occurred 
	// $botdata[1] - type of test (will be 'MAIL', 'IP', 'NAME', or 'MULTI') 
	// $botdata[2] - descriptor field for item (IP)
	// $botdata[3] - how many times the IP was found in the database 
	// $botdata[4] - descriptor field for item (MAIL)
	// $botdata[5] - how many times the EMAIL was found in the database 
	// $botdata[6] - descriptor field for item (NAME)
	// $botdata[7] - how many times the NAME was found in the database 
	
	//if($botdata[3] > 0 || $botdata[5] > 0 || $botdata[7] > 0){ 
	if($botdata[0] == 'Y'){ 
		$botscout_response->is_valid = false;
		$botscout_response->error = 'BotScout ' . __("says: 'Bot signature found!' Registration will not be allowed for this user.", 'Skt_nurcaptcha');
	} else {
    	$botscout_response->is_valid = true;
	}
	return $botscout_response;
		
} // here ends: function skt_nurc_check_botscout()

/**** 
*
* ***  check antispammer databases  *** 
* 
****/
function skt_nurc_antispam($user_email, $user_login, $result){

	// activate StopForumSpan queries by default
	if (get_site_option('sktnurc_stopforumspam_active')!='false') {
		update_site_option('sktnurc_stopforumspam_active','true');
		} 
	// captcha passed, now wanna check bot-databases all around
	if (get_site_option('sktnurc_stopforumspam_active')=='true') { 
		$result = skt_nurc_check_stopforumspam($user_email, $_SERVER['REMOTE_ADDR'], $user_login);
	}
	if(($result->is_valid)&&(get_site_option('sktnurc_botscout_active')=='true')){
		$result = skt_nurc_check_botscout($user_email, $_SERVER['REMOTE_ADDR'], $user_login);
	}
	return $result;
}
/**** 
*
* ***  select processID on error report  *** 
* 
****/
function skt_nurc_select_procid($result){
	switch(substr($result->error, 0,8)){
		case 'StopForu':
			$processID = 'StopForumSpam';
			break;
		case 'BotScout':
			$processID = 'BotScout';
			break;
		default:
			$processID = 'reCAPTCHA';
	}
	return $processID;
}

/**** 
*
* ***  Log db table installing and updating  *** 
* 
****/
function skt_nurc_install ($log_db_version) {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  email tinytext NOT NULL,
	  username tinytext NOT NULL,
	  ip tinytext NOT NULL,
	  procid tinytext NOT NULL,
	  UNIQUE KEY id (id)
	);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	update_site_option( "sktnurclog_db_version", $log_db_version );
}
function skt_nurc_update_db_check() {
    global $sktnurclog_db_version;
    if (get_site_option('sktnurclog_db_version') != $sktnurclog_db_version) {
        skt_nurc_install($sktnurclog_db_version);
    }
	delete_site_option('sktnurc_count');
}
add_action('plugins_loaded', 'skt_nurc_update_db_check');

/**** 
*
* ***  Log db table managing  *** 
* 
****/
function skt_nurc_listlog($limit = 20, $offset = 0) {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->get_results("SELECT * FROM ". $table_name ." ORDER BY id DESC LIMIT ". $offset . ", " . $limit .";");
	return $result;
}
function skt_nurc_countlog() {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->get_var("SELECT COUNT(*) FROM ". $table_name .";");
	return $result;
}
function nurc_clear_log_file() {
	global $wpdb;
	$target = skt_nurc_countlog();
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->query("TRUNCATE TABLE ". $table_name .";");
	if($result === false){
		return $result;
	}else{
		return __('Log table successfully deleted.', 'Skt_nurcaptcha');
	}
}
/************
* This method solves the problem of server restrictions on getting external url contents
* such as the responses given by antispam databases
* Thanks to Greg on (http://www.bugz.com.br/2011/09/file_get_contents/) [pt_BR]
*************/
function skt_nurc_get_page($url, $referer='', $timeout=30, $header=''){
		if ($referer=='') $referer='http://'.$_SERVER['HTTP_HOST'];
		if(!isset($timeout)) $timeout=30;
		$curl = curl_init();
		if(strstr($referer,"://")){
			curl_setopt ($curl, CURLOPT_REFERER, $referer);
		}
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt ($curl, CURLOPT_USERAGENT, sprintf("Mozilla/%d.0",rand(4,5)));
		curl_setopt ($curl, CURLOPT_HEADER, (int)$header);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$html = curl_exec ($curl);
		curl_close ($curl);
		return $html;
    }
/***************
* This method derived from one published by Manish Zope, as a comment at:
* http://stackoverflow.com/questions/190421/caller-function-in-php-5/12813039#12813039
****************/
function skt_nurc_getCallingFunctionName($complete=false)
    {
        $trace=debug_backtrace();
        if($complete)
        {
            $str = '';
            foreach($trace as $caller)
            {
                $str .= " - {$caller['function']}";
                if (isset($caller['class']))
                    $str .= " Class {$caller['class']}";
            }
        }
        else
        {
            $caller=$trace[2];
            $str = "Called by {$caller['function']}";
            if (isset($caller['class']))
                $str .= " Class {$caller['class']}";
        }
        return $str;
    }
function skt_nurc_pages_checkbox() {
	$custom = get_site_option('sktnurc_custom_page_list');
	if (is_array($custom)){
		$i = count($custom);
	}else{
		$i = 0;
	}
	$html = "<div id =\"sktnurc_pages_checkbox\" style=\"display:none;\">";
	$skt_pages = skt_nurc_get_pages_array();
	foreach ($skt_pages as $ID => $title){
		$html .= "<input type=\"checkbox\" name=\"sktnurc_custom_page_list[]\"  value=\"$ID\"";
		if ($i>0){
			for ($r=0;$r<$i;$r++){
				if ($custom[$r] == $ID) {
					$html .= " checked ";
					continue;	
				}
			}
		}
		$html .= ">$title<br />";
	}
	$html .= "</div>";
	return $html;
}
function skt_nurc_get_pages_array(){
	$args = array(
		'sort_order' => 'ASC',
		'sort_column' => 'post_title',
		'post_type' => 'page',
		'post_status' => 'publish'
	); 
	$pages = get_pages($args); 

	$skt_pages = array(); // lists all pages.
	foreach ($pages as $pages_list){
		$skt_pages[$pages_list->ID] = $pages_list->post_title;
	}
	//$skt_pages=array(0=> __("Choose a page", 'Skt_nurcaptcha'))+$skt_pages;
	return $skt_pages;
}