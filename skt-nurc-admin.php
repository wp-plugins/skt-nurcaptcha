<?php

	if($_POST['sktnurc_hidden'] == 'Y') {
		//Form data sent
	$sktnurc_pubkey = $_POST['sktnurc_publkey'];
	update_option('sktnurc_publkey', $sktnurc_pubkey);

	$sktnurc_privkey = $_POST['sktnurc_privtkey'];	
	update_option('sktnurc_privtkey', $sktnurc_privkey);

	update_option('sktnurc_theme', $_POST['sktnurc_theme']);
	update_option('sktnurc_lang', $_POST['sktnurc_lang']);

			?>
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
			<?php
	} else {
			//Normal page display
			$sktnurc_pubkey = get_option('sktnurc_publkey');
			$sktnurc_privkey = get_option('sktnurc_privtkey');
	}
?>
<div class="wrap">
	<?php 	
		screen_icon();
		echo "<p><h2>" . __( 'Skt NURCaptcha Settings', 'Skt_nurcaptcha' ) . "</h2></p><br />"; 
	?>
	
	<div style="width:680px;padding:12px 0 12px 24px">
		<?php    if (get_option("sktnurc_count")!="") {echo '<p style="padding: .5em; background-color: #666666; color: #fff;">'. __( 'NURCaptcha has blocked', 'Skt_nurcaptcha' ) . " <strong>".get_option("sktnurc_count")."</strong> ". __( 'suspect register attempts on this site.', 'Skt_nurcaptcha' ). "</p><br />";} ?>
		<?php    echo  __( 'NURCaptcha stands for <strong>New User Register Captcha</strong>.', 'Skt_nurcaptcha' ) . "<br />"; ?>
		<?php    echo  __( 'It uses Google\'s reCaptcha tools to protect your site against spammer bots, ', 'Skt_nurcaptcha' ) ; ?>
		<?php    echo  __( 'adding security to the WP Register Form. You can learn more', 'Skt_nurcaptcha' ) ; ?>
		<?php    echo  '<a href="http://www.google.com/recaptcha/security" target="_blank"> '.__('here','Skt_nurcaptcha').' </a>'.__('about reCAPTCHA Security','Skt_nurcaptcha').'<br />'; ?>
		<?php    echo "<br />"; ?>
		<?php    echo "<p style=\"padding: .5em; background-color: #666666; color: #fff;\">" . __( 'To enable this plugin\'s functionality, please enter your reCAPTCHA keys here:', 'Skt_nurcaptcha' ) . "</p><br />"; ?>
		<?php    echo "<small>[". __( 'You can sign up for reCaptcha <strong>free</strong> keys here: ', 'Skt_nurcaptcha' );
				 echo '<a href="'.recaptcha_get_signup_url().'"><strong>reCAPTCHA API Signup Page</strong></a>]</small>'; ?>
	</div>	
	
	<div style="width:680px;padding:12px 0 12px 24px">
		<form name="sktnurc_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="sktnurc_hidden" value="Y">
				
				<p><?php _e("reCaptcha Public Key: ", 'Skt_nurcaptcha' ); ?><input type="text" name="sktnurc_publkey" value="<?php echo $sktnurc_pubkey; ?>" size="46"></p>
				<p><?php _e("reCaptcha Private Key: ", 'Skt_nurcaptcha' ); ?><input type="text" name="sktnurc_privtkey" value="<?php echo $sktnurc_privkey; ?>" size="46"></p>
		<p class="submit" ><input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" /></p>
	</div>
	
	<div style="position:relative;width:680px;padding:12px 0 12px 24px">
		<p style="padding: .5em; background-color: #666666; color: #fff;"><?php echo __( 'Style your reCaptcha:', 'Skt_nurcaptcha' ) ?><span style="float:right;padding-right:18px"><?php echo " (". __('Click', 'Skt_nurcaptcha'). sprintf('&nbsp;&nbsp;&nbsp;<span class="button-primary" style="background:#4086aa"><a href="%s" target="_blank" style="color:#fff;font-weight:bold;background:#4086aa;text-decoration:none">&nbsp;', 'http://code.google.com/apis/recaptcha/docs/customization.html').__('here', 'Skt_nurcaptcha').' </a></span>&nbsp;&nbsp;'.__('for examples', 'Skt_nurcaptcha').')'; ?></span></p>
		<div style="float:left;width:180px;padding-left:24px;margin:12px 0 12px 0">
		<div id="sktth">
			<span><?php  _e('reCAPTCHA theme:', 'Skt_nurcaptcha'); ?></span><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<select id="sktnurc_theme" name="sktnurc_theme">
				<?php
				$plugin_img_path = array();
				$rc_themes = array('red' => 'Red (default)', 'white' => 'White', 'blackglass' => 'Blackglass', 'clean' => 'Clean');
				foreach( $rc_themes as $k => $v ) {
					$selected = ( $k == get_option('sktnurc_theme') ) ? 'selected="selected"' : '';
					echo "<option value='$k' $selected>$v</option>";
					$plugin_img_path[$k] = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
					$plugin_img_path[$k] .= 'img/'.$k.'.png';
				}
				$def_img = ( "" == get_option('sktnurc_theme') ) ? 'red' : get_option('sktnurc_theme');
				?>
			</select><br />
		</div>
		<div id="sktlg" style="padding-top:12px;">
			<span><?php _e('reCAPTCHA language:', 'Skt_nurcaptcha') ?></span><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<select id="sktnurc_lang" name="sktnurc_lang">
				<?php
				$rc_langs = array('en' => 'English (default)', 'nl' => 'Dutch', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese', 'ru' => 'Russian', 'es' => 'Spanish', 'tr' => 'Turkish');
				foreach( $rc_langs as $k => $v ) {
					$selected = ( $k == get_option('sktnurc_lang') ) ? 'selected="selected"' : '';
					echo "<option value='$k' $selected>$v</option>";
				}
				?>
			</select>
		</div>
	</div>
<?php
			foreach ($plugin_img_path as $k => $v) {
?>
		<div id="sktnurc-display-<?php echo $k; ?>" style="float:right;width:460px;padding-top:8px<?php if ($k != $def_img) { echo ';display:none';} ?>">
			<img src="<?php echo $plugin_img_path[$k]; ?>" title="<?php _e('This is the look of your captcha','Skt_nurcaptcha') ?>" />
		</div>
<?php
			}
?>

		<div style="clear:both"></div>
		<p class="submit" ><input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" /></p>
	</div>

	</form>
<?php
			/* PayPal Donation Button */
?>
	<div style="position:relative;width:680px;padding:8px 0 12px 24px">
		<p style="padding: .6em; background-color: #666; color: #fff;">
				<?php echo __( 'Make me happy:', 'Skt_nurcaptcha' ) ?>
				</p>
		<div style="margin-left:32px;padding-bottom:12px">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="SKNS7K7L5BFLL">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
	
</div>	<?php /* end of div.wrap */ ?>

<?php
/**
 * gets a URL where the user can sign up for reCAPTCHA. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function recaptcha_get_signup_url ($domain = null, $appname = null) {
	return "https://www.google.com/recaptcha/admin/create?" .  _recaptcha_qsencode (array ('domains' => $domain, 'app' => $appname));
}

?>