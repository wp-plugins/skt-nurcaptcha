<?php

	$sktnurc_ref_strings = array(
		__('Get a visual challenge','Skt_nurcaptcha'),
		__('Get an audio challenge','Skt_nurcaptcha'),
		__('Get a new challenge','Skt_nurcaptcha'),
		__('Type the two words:','Skt_nurcaptcha'),
		__('Type the words in the boxes:','Skt_nurcaptcha'),
		__('Type what you hear:','Skt_nurcaptcha'),
		__('Help','Skt_nurcaptcha'),
		__('Play sound again','Skt_nurcaptcha'),
		__('Download sound as MP3','Skt_nurcaptcha'),
		__('Incorrect. Try again.','Skt_nurcaptcha'),
		__('reCAPTCHA challenge image','Skt_nurcaptcha')
		); 

	include('skt-nurc-recaptcha-locales.php');
	if (get_option('sktnurc_reclocales_lang')=="") {
		update_option('sktnurc_reclocales_lang',$sktnurc_reclocales_strings);
	}else{
		$sktnurc_temp_reclocales_strings=get_option('sktnurc_reclocales_lang');
		foreach($sktnurc_reclocales_strings as $lg => $strlg){
			if($sktnurc_temp_reclocales_strings[$lg]==''){
				$sktnurc_temp_reclocales_strings[$lg]=$strlg;
			}
		}
		update_option('sktnurc_reclocales_lang',$sktnurc_temp_reclocales_strings);
		$sktnurc_reclocales_strings=$sktnurc_temp_reclocales_strings;
	}
	$sktnurc_en_strings = $sktnurc_reclocales_strings["en"];
	// activate StopForumSpan queries by default
	if (get_option('sktnurc_stopforumspam_active')=='') {
		update_option('sktnurc_stopforumspam_active',true);
		} 

	if($_POST['sktnurc_hidden'] == 'Y') {
		//Form data sent
		$sktnurc_pubkey = $_POST['sktnurc_publkey'];
		update_option('sktnurc_publkey', $sktnurc_pubkey);

		$sktnurc_privkey = $_POST['sktnurc_privtkey'];	
		update_option('sktnurc_privtkey', $sktnurc_privkey);
		
		$sktnurc_botscoutkey = $_POST['sktnurc_botscoutkey'];
		if($sktnurc_botscoutkey !== get_option('sktnurc_botscoutkey')){
			$botscoutkey_verified = skt_nurc_verify_botscoutkey($sktnurc_botscoutkey);	
		}
		update_option('sktnurc_botscoutkey', $sktnurc_botscoutkey);
		if($_POST['sktnurc_theme']=='true'){
			update_option('sktnurc_botscoutTestMode', true);
		}else{
			update_option('sktnurc_botscoutTestMode', false);
		}
		update_option('sktnurc_theme', $_POST['sktnurc_theme']);
		update_option('sktnurc_regbutton', $_POST['sktnurc_regbutton']);
		if('custom'== $_POST['sktnurc_lang_set']) {
			update_option('sktnurc_lang', $_POST['sktnurc_lang_set']);
		}else{
			update_option('sktnurc_lang', $_POST['sktnurc_lang']);
		}
		update_option('sktnurc_lang_set', $_POST['sktnurc_lang_set']);
		if($_POST['sktnurc_lang']==$_POST['sktnurc_hidden_lang']) {
			$temp = $sktnurc_reclocales_strings[ $_POST['sktnurc_lang']];
			$sktnurc_cst_strings = array(
				0 => $_POST['visual_challenge'],
				1 => $_POST['audio_challenge'],
				2 => $_POST['refresh_btn'],
				3 => $_POST['instructions_visual'],
				4 => $_POST['instructions_context'],
				5 => $_POST['instructions_audio'],
				6 => $_POST['help_btn'],
				7 => $_POST['play_again'],
				8 => $_POST['cant_hear_this'],
				9 => $_POST['incorrect_try_again'],
				10 => $_POST['image_alt_text'],
				11 => $temp[11]
				);
			for ($i=0; $i <= 11; $i++){
				if ($sktnurc_cst_strings[$i]=='') {
						$sktnurc_cst_strings[$i]=' ';
				}
			}
			$sktnurc_reclocales_strings[ $_POST['sktnurc_lang']]=$sktnurc_cst_strings;
			update_option('sktnurc_reclocales_lang',$sktnurc_reclocales_strings);
		}
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
		if ($botscoutkey_verified == false) {
			settings_errors( 'botscoutkey' );
			}
	} else {
		//Normal page display
		$sktnurc_pubkey = get_option('sktnurc_publkey');
		$sktnurc_privkey = get_option('sktnurc_privtkey');
		$sktnurc_botscoutkey = get_option('sktnurc_botscoutkey');
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
		<div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
		<div id="sktth" style="position:relative">
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
			<!-- reCAPTCHA images --> 
			<div class="captcha-img" style="float:left;width:460px; margin:-42px 0 110px 0; padding:0 0 12px 0">
				<?php
				foreach ($plugin_img_path as $k => $v) {
				?>
				<div id="sktnurc-display-<?php 
					echo $k; 
					?>" style="position:absolute;margin-left:232px<?php 
					if ($k != $def_img) { echo ';display:none';} ?>">
					<img src="<?php echo $plugin_img_path[$k]; ?>" title="<?php 
					_e('This is the look of your captcha','Skt_nurcaptcha'); ?>" />
				</div>
				<?php
				}
				?>
			</div>
			<!-- end of reCAPTCHA images --> 
		</div>
		<!-- end of reCAPTCHA style block -->
        </div>
		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->
        <div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
        <div style="padding:4px 0 4px 0;">
				<span><?php _e('reCAPTCHA language:', 'Skt_nurcaptcha') ?></span><br /><br />
        	<input class="sktlg_radio" type="radio" name="sktnurc_lang_set" id="sktlg_radio1" value="basic" 
				<?php if (((get_option('sktnurc_lang_set')=='') or (get_option('sktnurc_lang_set')=='basic')) and (get_option('sktnurc_lang')!='custom')) 
							{ ?>checked<?php } ?> />
				<?php _e('use reCAPTCHA native languages', 'Skt_nurcaptcha') ?>
                <span id="save-advert-lang1" style="display:none;color:#ff2200;margin-right:8px">
                        <strong>&nbsp;&nbsp;&nbsp;&nbsp;[<?php 
							_e('Choose a language on the selector below','Skt_nurcaptcha'); ?>]
                        </strong>
                </span>
                <br />
        	<input class="sktlg_radio" type="radio" name="sktnurc_lang_set" id="sktlg_radio2" value="custom" 
				<?php if ((get_option('sktnurc_lang_set')=='custom') or (get_option('sktnurc_lang')=='custom'))
							{ ?>checked<?php } ?> />
				<?php _e('use custom strings set', 'Skt_nurcaptcha') ?>
                <span id="save-advert-lang2" style="display:none;color:#ff2200;margin-right:8px">
                        <strong>&nbsp;&laquo;&nbsp;&laquo;&nbsp;&laquo;&nbsp;<?php 
							_e('Save this choice before making changes on the strings','Skt_nurcaptcha'); ?>
                        </strong>
                </span>
                <br />

			<!-- *****  languages dropbox ... -->
			<div id="sktlg" style="<?php if (get_option('sktnurc_lang_set')=='custom') 
							{ ?>display:none;<?php } ?>padding:12px 0 16px 0;">
				&nbsp;&nbsp;&nbsp;&nbsp;<select id="sktnurc_lang" name="sktnurc_lang">
					<?php
					$lang_display = array();
					foreach( $sktnurc_reclocales_strings as $k => $v ) {
						$lang_display[$v[11]]=$k;
					}
					ksort($lang_display);
					foreach( $lang_display as $v => $k ) {
						$selected = ( $k == get_option('sktnurc_lang') ) ? 'selected="selected"' : '';
						echo "<option value='$k' $selected>$v</option>";
					}
					?>
				</select>
                <span id="save-advert-lang" style="display:none;color:#ff2200;margin-right:8px">
                        <strong>&nbsp;&laquo;&nbsp;&laquo;&nbsp;&laquo;&nbsp;<?php 
							_e('Save this choice before making changes on the strings','Skt_nurcaptcha'); ?>
                        </strong>
                </span>
				<input type="hidden" name="sktnurc_hidden_lang" value="<?php echo get_option('sktnurc_lang'); ?>" />
			</div> <!--  *****  end of div "sktlg" (languages dropbox) -->
            
            <div id="sktcstlg" style="position:relative; float:left;padding:12px 0 16px 0;">
            <?php 
				$sktnurc_cst_strings = $sktnurc_reclocales_strings[get_option('sktnurc_lang')];  
			
			?>
            	
<span><strong>
<?php _e('Customize reCAPTCHA strings at will by changing the texts in the eleven fields below:','Skt_nurcaptcha'); ?> 
</strong></span><br /><br />
"<?php echo $sktnurc_ref_strings[0]; ?>" <input 
type="text" id ="visual_challenge" name="visual_challenge" value="<?php echo $sktnurc_cst_strings[0]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[1]; ?>" <input 
type="text" id ="audio_challenge" name="audio_challenge" value="<?php echo $sktnurc_cst_strings[1]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[2]; ?>" <input 
type="text" id ="refresh_btn" name="refresh_btn" value="<?php echo $sktnurc_cst_strings[2]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[3]; ?>" <input 
type="text" id ="instructions_visual" name="instructions_visual" value="<?php echo $sktnurc_cst_strings[3]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[4]; ?>" <input 
type="text" id ="instructions_context" name="instructions_context" value="<?php echo $sktnurc_cst_strings[4]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[5]; ?>" <input 
type="text" id ="instructions_audio" name="instructions_audio" value="<?php echo $sktnurc_cst_strings[5]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[6]; ?>" <input 
type="text" id ="help_btn" name="help_btn" value="<?php echo $sktnurc_cst_strings[6]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[7]; ?>" <input 
type="text" id ="play_again" name="play_again" value="<?php echo $sktnurc_cst_strings[7]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[8]; ?>" <input 
type="text" id ="cant_hear_this" name="cant_hear_this" value="<?php echo $sktnurc_cst_strings[8]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[9]; ?>" <input type="text" id ="incorrect_try_again" name="incorrect_try_again" value="<?php echo $sktnurc_cst_strings[9]; ?>" size="40" /><br />
"<?php echo $sktnurc_ref_strings[10]; ?>" <input 
type="text" id ="image_alt_text" name="image_alt_text" value="<?php echo $sktnurc_cst_strings[10]; ?>" size="40" /><br />


            </div> <!--  end of div "sktcstlg"  -->
        </div>
	</div>

		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div>
		<div style="float:left;width:180px;padding-left:24px;margin:12px 0 12px 0;">
				<p style="position:relative">
				<?php _e("Customize text to appear in Submit Button (register form): ", 'Skt_nurcaptcha' ); ?>
                <input type="text" id="sktnurc_regbutton" name="sktnurc_regbutton" 
                value="<?php echo get_option('sktnurc_regbutton'); ?>" size="26"><br />
				<?php echo "[ default: <strong><span id='sktnurc_regbutton_text' >". __('Register', 'Skt_nurcaptcha' )."</span></strong> ]"; ?>

    			</p>
        </div>
		<div class="regbutton-mock-up" style="margin-left:232px;position:absolute;width:312px;padding:24px 0 12px 0">
				<input style="float:left;margin-right:12px; border:1px solid #fff" type="submit" size="auto" class="button-primary" id="sktnurc-mockup-wp-submit" value="<?php 
					if (get_option('sktnurc_regbutton')==""){
						_e("Register", 'Skt_nurcaptcha'); 
					} else {
						echo get_option('sktnurc_regbutton');
					}
				?>" />
		</div>
		<div style="clear:both"></div>

		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','Skt_nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>

<?php

/********************* AntiSpamDatabases query config */
			
?>
	<div style="position:relative;width:680px;padding:8px 0 12px 24px">
		<p style="padding: .6em; background-color: #666; color: #fff;">
			<?php echo __( 'Configure Anti Spam Databases options:', 'Skt_nurcaptcha' ) ?>
		</p><br />
    <span>
    <?php _e('Skt NURCaptcha adds extra security by checking new user\'s username, email and ip against trustable databases all around. By default, a search in \'Stop Forum Spam\'s\' database is always done after reCAPTCHA challenge is correctly filled. You may also choose to search up in BotScout\'s, also.','Skt_nurcaptcha'); ?> 
    </span><br /><br />
    <a href="http://www.stopforumspam.com/" target="_blank" title="<?php _e("Visit Stop Forum Spam site", 'Skt_nurcaptcha' ); ?>">
    <img src="<?php echo plugin_dir_url(dirname(__FILE__).'/skt-nurcaptcha.php'); ?>img/sfs_banner.jpg" /></a><br />
	<input class="sktSpam_check" type="checkbox" name="sktnurc_stopforumspam_active" id="sktSpam_check0" value="true" 
	<?php if (get_option('sktnurc_stopforumspam_active')==true) { ?>checked<?php } ?> /> <?php _e('Activate StopForumSpam check for spammer signature (maximum of 20,000 lookups per day)','Skt_nurcaptcha'); ?><br /><br />
    <a href="http://www.botscout.com/getkey.htm" target="_blank" title="<?php _e("Get an API Key at BotScout", 'Skt_nurcaptcha' ); ?>">
    <img src="<?php echo plugin_dir_url(dirname(__FILE__).'/skt-nurcaptcha.php'); ?>img/bs_logo_msmall.gif" /></a><br />
	<input class="sktSpam_check" type="checkbox" name="sktnurc_botscout_active" id="sktSpam_check1" value="true" 
	<?php if (get_option('sktnurc_botscout_active')==true) { ?>checked<?php } ?> /> <?php _e('Activate BotScout (maximum of 20 lookups per day without a free BotScout API Key, or 300 with it)','Skt_nurcaptcha'); ?><br /><br />
<?php                
 	if(get_option('sktnurc_botscoutTestMode')==''){update_option('sktnurc_botscoutTestMode',true);}
?>               
	<input class="sktSpam_check" type="checkbox" name="sktnurc_botscoutTestMode" id="sktSpam_check2" value="true" 
	<?php if (get_option('sktnurc_botscoutTestMode')==true) { ?>checked<?php } ?> /> <?php _e('Leave BotScout in Test Mode (it will not check user\'s IP).','Skt_nurcaptcha'); ?> <span style="font-style:italic;"><?php _e('If you uncheck this box, BotScout will understand that any query you submit that matches an email or username in its database is not only a search, but also a submission. This means that even if you are just testing a suspect email, your own IP will be added to the database as a spammer if any record of that email exists in the BotScout files.','Skt_nurcaptcha'); ?></span>
                <br />
				<p><?php _e("BotScout API Key: ", 'Skt_nurcaptcha' ); ?><input type="text" id="sktnurc_botscoutkey" name="sktnurc_botscoutkey" value="<?php echo $sktnurc_botscoutkey; ?>" size="46"></p>
		<div style="clear:both"></div>

		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'Skt_nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','Skt_nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>


	</form>

<?php

/********************* PayPal Donation Button */
			
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
		update_option("sktnurc_count","");
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
		$version = 'wrong version';
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

function skt_nurc_verify_botscoutkey($sktnurc_botscoutkey){
	$test_string = "http://botscout.com/test/?mail=jayzers16@aol.com&key=" . $sktnurc_botscoutkey;
	if(function_exists('file_get_contents')){
		$returned_data = file_get_contents($test_string);
	}else{
		$ch = curl_init($test_string);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$returned_data = curl_exec($ch);
		curl_close($ch);
	}
	if(substr($returned_data, 0,1) == '!'){
		$warning_message = __('Skt NURCaptcha Warning :: BotScout key seems to be incorrect. Retype it and try again.', 'Skt_nurcaptcha' );
		add_settings_error( "botscoutkey", "botscoutkey", $warning_message );
		return false;
	}else{
		return true;
	}
}

?>