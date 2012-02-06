(function($) {
	$(document).ready(function(){
		
		$('#login_error').each(function(){
			new Effect.Shake('login_error');
			new Effect.Shake('nurc_form');
			return false;
		});
		$('#username-help-toggle').click( function(){
			$('#username-help').slideToggle();
			$('#user_login').focus();
		});
		$('#email-help-toggle').click( function(){
			$('#email-help').slideToggle();
			$('#user_email').focus();
		});
		$('#recaptcha-help-toggle').click( function(){
			$('#recaptcha-help').slideToggle();
		});
		
	});	
})(jQuery);
