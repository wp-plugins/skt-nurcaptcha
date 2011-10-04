(function($) {
	$(document).ready(function(){
		// pick initial references
		refkey1 = $('#sktnurc_publkey').val();
		refkey2 = $('#sktnurc_privtkey').val();
		refimg = $('#sktnurc_theme').val();
		reflang = $('#sktnurc_lang').val();
		
		presimg=refimg;preslang=reflang;preskey1=refkey1;preskey2=refkey2;
		
		$('#sktnurc_theme').change( function(){
			antimg = presimg;
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
			if ((preslang == reflang) && (presimg == refimg) && (preskey1==refkey1) && (preskey2==refkey2)) {
				$('.save-advert').css('visibility','hidden');
			} else {
				$('.save-advert').css('visibility','visible');
			}
		}
	});	
})(jQuery);
