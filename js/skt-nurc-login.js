(function($) {
	$(document).ready(function(){
		
		$('#login_error').each(function(){
			new Effect.Shake('login_error');
			new Effect.Shake('nurc_form');
			return false;
		});
		
	});	
})(jQuery);
