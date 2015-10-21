/*
 *
 * Plugin para obtener selects multiseleccionables mediante checkboxes
 *
 */

(function( $ ) {
    $.uniqId = function (separator) {
        var delim = separator || "-";

        function S4() {
	        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	    }
	
	    return (S4() + S4() + delim + S4() + delim + S4() + delim + S4() + delim + S4() + S4() + S4());
	};
	
    $.fn.multiselect = function(options) {
    	var elementType=$(this).prop("tagName").toLowerCase();
    	if(elementType!='select') return;
    	var selectID=$(this).attr('id');
    	if (!selectID)
        	selectID=$(this).attr('name');
    	if (!selectID)
    		selectID=$.uniqId();
    	var settings = $.extend({
    		checkboxName:selectID,
    		height: 100,
    		onChange: null
        }, options );
    	this.settings=settings;

    	var selectParent=$(this).parent();
    	var theRealSelect=$(this);
    	var theDiv=$('<div />').addClass('multiselect form-control btn-group').css('height',settings.height+'px').appendTo(selectParent);
    	$(this).css('display','none');
    	var theList=$('<ul />').addClass('multiselect-container').css({
    		'list-style-type':'none',
    		'margin-left':'-20px'
    	}).appendTo(theDiv);
    	$(this).find('option').each(function() {
    		var isChecked=($(this).prop('selected'));
    		var theItem=$('<li>').css({
    			'margin-left':'-20px'
    		}).html('<label class="checkbox"><input type="checkbox" style="cursor: default !important;" disabled="disabled" name="multiselect"'+(isChecked?' checked="checked"':'')+' value="'+$(this).val()+'">'+$(this).html()+'</label>').appendTo(theList);
    		$(theItem).click(function(eventData) {
    			var checked=$(this).find('input[type=checkbox]').prop('checked');
    			var value=$(this).find('input[type=checkbox]').attr('value');
    			
    			$(this).find('input[type=checkbox]').prop('checked',!checked);
    			var theOption=$(theRealSelect).find('option[value='+value+']');
    			if (!checked) 
    				$(theOption).attr('selected','selected');
    			else
    				$(theOption).removeAttr('selected');
    			eventData.preventDefault();
    			//alert('OK!');
    		});
    	})
    	
        $(".multiselect").mCustomScrollbar({autoHideScrollbar: true, advanced: {autoScrollOnFocus: false}});

    	//alert(elementType);
    }

}( jQuery ));