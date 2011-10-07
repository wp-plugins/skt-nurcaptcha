<?php

	if($_POST['sktnurc_hidden'] == 'Y') {
		//Form data sent
		$sktnurc_pubkey = $_POST['sktnurc_publkey'];
		update_option('sktnurc_publkey', $sktnurc_pubkey);

		$sktnurc_privkey = $_POST['sktnurc_privtkey'];	
		update_option('sktnurc_privtkey', $sktnurc_privkey);

		update_option('sktnurc_theme', $_POST['sktnurc_theme']);
		update_option('sktnurc_lang', $_POST['sktnurc_lang']);
		
		if ($_POST['log_clear']!= 'no') {
			if (nurc_clear_log_file()) {
			?>
				<div class="updated"><p><strong><?php _e('Log File has been deleted.', 'Skt_nurcaptcha' ); ?></strong></p></div>
			<?php
			}
		}
		?>
			<div class="updated"><p><strong><?php _e('Options saved.', 'Skt_nurcaptcha' ); ?></strong></p></div>
		<?php
	} else {
		//Normal page display
		$sktnurc_pubkey = get_option('sktnurc_publkey');
		$sktnurc_privkey = get_option('sktnurc_privtkey');
	}
		$npath = nurc_make_log_path();
?>
<div class="wrap">
	<?php 	
		$nurc_version = nurc_get_version();
		screen_icon();
		echo "<p><h2>" . __( 'Skt NURCaptcha Settings', 'Skt_nurcaptcha' ) . "</h2></p>"; 
	?>
	
	<div style="width:680px;padding:12px 0 12px 24px">
		<form name="sktnurc_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="sktnurc_hidden" value="Y" />
				<input id="log_clear" type="hidden" name="log_clear" value="no" />
				<input id="confirm_dialog" type="hidden" name="confirm_dialog" value="<?php _e( 'This option cannot be undone. Do you really want to erase all Log Data and restart Log File?', 'Skt_nurcaptcha' ) ?>" />
		<?php echo __('Version: ', 'Skt_nurcaptcha') . $nurc_version; ?>
		<p style="padding: .5em; background-color: #666666; color: #fff;position:relative">Skt NURCaptcha 
		<?php   if (get_option("sktnurc_count")!="") {
			echo __( 'has blocked', 'Skt_nurcaptcha' ) . " <strong> ".get_option("sktnurc_count")."</strong> ". __( 'suspect register attempts on this site.', 'Skt_nurcaptcha' ). "<span id='log_button' class=\"log_button button-primary\" style=\"position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:12px\">&nbsp;".__('See Log', 'Skt_nurcaptcha')."&nbsp;</span><span id='no_log_button' class=\"log_button button-primary\" style=\"display:none;position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:12px\">&nbsp;".__('Hide Log', 'Skt_nurcaptcha')."&nbsp;</span>";
			} ?>
        </p><br />
		<?php    echo  __( 'NURCaptcha stands for <strong>New User Register Captcha</strong>.', 'Skt_nurcaptcha' ) . "<br />"; ?>
		<?php    echo  __( 'It uses Google\'s reCaptcha tools to protect your site against spammer bots, ', 'Skt_nurcaptcha' ) ; ?>
		<?php    echo  __( 'adding security to the WP Register Form. You can learn more', 'Skt_nurcaptcha' ) ; ?>
		<?php    echo  '<a href="http://www.google.com/recaptcha/security" target="_blank"> '.__('here','Skt_nurcaptcha').' </a>'.__('about reCAPTCHA Security','Skt_nurcaptcha').'<br />'; ?>
		<?php    echo "<br />"; ?>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page! ','Skt_nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
        <?php 
		if (file_exists($npath)) {
			$logtext = file($npath);
			$logcheck = true;
		}else{
			$logtext[0] = __('Nothing found in Log File','Skt_nurcaptcha');
			$logcheck = false;
		}
		?>
        <div style="clear:both"></div>
		<?php
		echo "<div id='log_entries' style='position:relative;display:none'>"; 
		echo "<h3>". __('Last Blocked Attemptives','Skt_nurcaptcha')."</h3>";
		echo "<span id='unlink_log_button' class=\"button-primary\" style=\"display:none;position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:12px\">&nbsp;".__('Delete Log File', 'Skt_nurcaptcha')."&nbsp;</span><span id='link_log_button' class=\"button-primary\" style=\"display:none;position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#ff2000;text-decoration:none;top:2px;right:12px\">&nbsp;".__('Cancel Delete Log File', 'Skt_nurcaptcha')."&nbsp;</span> ";
		
			if ($logcheck == true) {
			} else { echo "<strong>";}
				 for($i=count($logtext);$i>=0;$i--){
					 if (trim($logtext[$i])=='') continue;
    				 echo $logtext[$i];
					 echo '<br />';
				 }
			if (!$logcheck) {echo "</strong>";}
		?>
		<?php    echo "</div>"; ?>
        <div style="clear:both"></div>
		<?php    echo "<br />"; ?>
		<?php    echo "<p style=\"padding: .5em; background-color: #666666; color: #fff;\">" . __( 'To enable this plugin\'s functionality, please enter your reCAPTCHA keys here:', 'Skt_nurcaptcha' ) . "</p><br />"; ?>
		<?php    echo "<small>[". __( 'You can sign up for reCaptcha <strong>free</strong> keys here: ', 'Skt_nurcaptcha' );
				 echo '<a href="'.recaptcha_get_signup_url().'"><strong>reCAPTCHA API Signup Page</strong></a>]</small>'; ?>
	</div>	
	
	<div style="width:680px;padding:12px 0 12px 24px">
				<p><?php _e("reCaptcha Public Key: ", 'Skt_nurcaptcha' ); ?><input type="text" id="sktnurc_publkey" name="sktnurc_publkey" value="<?php echo $sktnurc_pubkey; ?>" size="46"></p>
				<p><?php _e("reCaptcha Private Key: ", 'Skt_nurcaptcha' ); ?><input type="text" id="sktnurc_privtkey" name="sktnurc_privtkey" value="<?php echo $sktnurc_privkey; ?>" size="46"></p>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','Skt_nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>
	
	<div style="position:relative;width:680px;padding:12px 0 12px 24px">
		<p style="padding: .5em; background-color: #666666; color: #fff;"><?php echo __( 'Style your reCaptcha:', 'Skt_nurcaptcha' ) ?></p>
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
		<div class="captcha-img" style="position:relative;width:460px;padding-bottom:12px">
<?php
			foreach ($plugin_img_path as $k => $v) {
?>
			<div id="sktnurc-display-<?php echo $k; ?>" style="position:absolute;margin-left:232px<?php if ($k != $def_img) { echo ';display:none';} ?>">
				<img src="<?php echo $plugin_img_path[$k]; ?>" title="<?php _e('This is the look of your captcha','Skt_nurcaptcha'); ?>" />
			</div>
<?php
			}
?>
		</div>

		<div style="clear:both"></div>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','Skt_nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
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

function nurc_clear_log_file() {
		$npath = nurc_make_log_path();
		if (file_exists($npath)) {
			unlink($npath);
			return true;
		}
	return false;
}
function nurc_get_version() {
		$npath = nurc_make_path();
		$npath .= 'skt-nurcaptcha.php';
		$lines = file($npath);
		$l = count($lines);
		$version = 'versão errada';
		$i=0;
		while ($i < $l):
			if (trim($lines[$i])=='') {
				$i++;
				continue;
			}
			$r = strpos($lines[$i],'Version:');
			if ($r != false) {
				$version = trim(substr($lines[$i],$r+8)); 
				break;
			}
			$i++;
		endwhile;
		return $version;
		
}
?>