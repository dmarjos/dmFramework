(function( $ ) {
	/*
	 * Helper functions
	 */
	$.fadedColor = function() {
		$(this).css({
			'cursor':'pointer',
			'opacity':'0.5',
			'filter':'alpha(opacity=50)'
		});
	};
	
	$.fullColor= function() {
		$(this).css({
			'cursor':'pointer',
			'opacity':'1',
			'filter':'alpha(opacity=100)'
		});
	};

	$.uniqId = function (separator) {
		var delim = separator || "-";

		function S4() {
	        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	    }
		
	    return (S4() + S4() + delim + S4() + delim + S4() + delim + S4() + delim + S4() + S4() + S4());
	};
}( jQuery ));