(function($) {
	$(document).ready(function(){
		// pick initial references
		var refkey1 = $('#sktnurc_publkey').val();
		var refkey2 = $('#sktnurc_privtkey').val();
		var refimg = $('#sktnurc_theme').val();
		var reflang = $('#sktnurc_lang').val();
		var preslog = 'no'; 
		var presimg=refimg;var preslang=reflang;var preskey1=refkey1;var preskey2=refkey2;
		
		$('#sktnurc_theme').change( function(){
			var antimg = presimg;
			presimg = $('#sktnurc_theme').val();
			$('#sktnurc-display-' + antimg).fadeTo(300,0);
			$('#sktnurc-display-' + presimg).fadeTo(600,1);
			advert_check();
		});
		$('#sktnurc_lang').change( function(){
			preslang = $('#sktnurc_lang').val();
			advert_check();
		});
		$('#sktnurc_publkey').change( function(){
			preskey1 = $('#sktnurc_publkey').val();
			advert_check();
		});
		$('#sktnurc_privtkey').change( function(){
			preskey2 = $('#sktnurc_privtkey').val();
			advert_check();
		});
		window.advert_check	= function() {
			if ((preslang == reflang) && (presimg == refimg) && (preskey1==refkey1) && (preskey2==refkey2) && (preslog == 'no')) {
				$('.save-advert').fadeOut();
			} else {
				$('.save-advert').fadeIn();
			}
		}
		$('.log_button').click( function(){
			$('#log_entries').slideToggle();
			$('#log_button').fadeToggle();
			$('#no_log_button').fadeToggle();
			$('#unlink_log_button').fadeToggle();
		});
		$('#unlink_log_button').click(function(){
			var dialog = $('#confirm_dialog').val();
			logfile_manage(confirm(dialog));
		});
		$('#link_log_button').click(function(){
			logfile_manage(false);
		});
		window.logfile_manage = function(res){
			if (res == true) {
					preslog = "yes";
					$('#unlink_log_button').fadeOut();
					$('#link_log_button').fadeIn();
				} else {
					preslog = "no";
					$('#unlink_log_button').fadeIn();
					$('#link_log_button').fadeOut();
				}
			$('#log_clear').val(preslog);
			advert_check();
		}
	});	
})(jQuery);
