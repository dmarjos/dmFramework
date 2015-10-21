(function( $ ) {
	$.validate_mandatory = function(field) {
		var value=$(field).val();
		var prompt=$(field).attr("prompt");
		
		if (!prompt || prompt==null)
			prompt="";
		
		prompt=prompt.toUpperCase();
		value=value.replace(prompt,"");
		return ($.trim(value)!="");
	};
	
	$.validate_numericOnly=function(field) {
		var value=$(field).val();
		return !isNaN(parseFloat(value)) && isFinite(value);
	};

	$.validate_email=function(field) {
		if (!$.validate_mandatory(field))
			return false;

		var email=$(field).val();

		if (email.indexOf('@')==-1)
			return false;
		
		var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if (!filter.test(email))
			return false;

		return true;
	};
	
	$._forms=[];
    $.fn.createForm = function(options) {

    	this.checkField = function (field) {
    		
    		var validator=$(field).attr("validator");
    		if (validator==null || validator==undefined)
    			return true;

    		var theFunction=eval("$.validate_"+validator.toLowerCase());
    		if (typeof(theFunction)=="undefined" ) {
        		var theFunction=eval("this.settings.validators."+validator.toLowerCase());
        		if (typeof(theFunction)=="undefined" ) {
            		var theFunction=eval("this.settings.validators."+validator);
            		if (typeof(theFunction)=="undefined" )
            			return true;
        		}
    		} 
   				
    		
    			
    		if (!theFunction(field)) {
    			var errorMsg=$(field).attr("error");
    			if (errorMsg) {
    				alert('ERROR: '+errorMsg);
    			} else {
    				alert('Please check the information you entered.');
    			}
				$(field).focus();
				return false;
    		}
    		
    		return true;
    	};
    	
    	this.fields=[];
    	var settings = $.extend({
    		validators: {},
            formContainer: "#form",
            elements: "input[type=text], textarea",
            globalValidator: null,
            submitSuccessCallBack:null,
            submitSuccessMessage:'Form has been submited',
            submitErrorMessage:'Error on form submit',
            submitErrorCallBack:null,
            submitElement: null,
            submitURL: null,
            submitByAjax: true,
            usePrompt: true
        }, options );
    	this.settings=settings;
    	
    	$._forms[settings.formContainer]={postData:{}};
    	var _elements=settings.elements.split(',');
    	var elements=[];
    	var selectors=[];
    	for(var i=0; i<_elements.length; i++) {
    		elements.push($.trim(_elements[i]));
    		selectors.push(settings.formContainer+' '+$.trim(_elements[i]));
    	}
    	
    	var me=this;
    	for(var selector in selectors) {
    		if (selectors[selector]) {
    	    	$(selectors[selector]).each(function(){
    	    		me.fields.push(this);
    	    		var prompt=$(this).attr("prompt");
    	    		
    	    		if (!prompt || prompt==null || prompt==undefined)
    	    			prompt="";
    	    		if (me.settings.usePrompt)
    	    			$(this).val(prompt.toUpperCase());
    	    		$(this).focus(function(){
    	    			var prompt=$(this).attr("prompt");
    	    			if (prompt && prompt!=null && prompt!=undefined)
    	    				prompt=prompt.toUpperCase();
    	    			if ($(this).val()==prompt)
    	    				$(this).val('');
    	    		}).blur(function() {
    	    			var prompt=$(this).attr("prompt");
    	    			if (prompt && prompt!=null && prompt!=undefined)
    	    				prompt=prompt.toUpperCase();
    	    			if ($(this).val()=="")
    	    				$(this).val(prompt);
    	    		});
    	    		
    	    		var autoTab=$(this).attr("autotab");
    	    		if (autoTab!=null && autoTab!=undefined && autoTab!="") {
    	    			$(this).autotab({target:autoTab});
    	    		}
    	    		
    	    	});
    			
    		}
    	}
    	$(settings.submitElement).css('cursor','pointer');
    	if (settings.submitElement && settings.submitURL) {
    		$(settings.submitElement).click(function(){
    			var postData={};
    			var submittable=true;
    			for(var f=0; f<me.fields.length; f++) {
    				var field=me.fields[f];
    				var name=$(field).attr('table-field');
    				if (!name || name==null)
    					var name=$(field).attr('name');
    				if (!name || name==null)
        				var name=$(field).attr('id');
    				if (!name || name==null)
    					continue;

    				if (!me.checkField(field)) {
    					submittable=false;
    					return;
    				}
    				
    				postData[name]=$(field).val();
    			}
    			if (submittable) {
    				$._forms[me.settings.formContainer].postData=postData;
    				if (me.settings.globalValidator!=null) {
    					if (!me.settings.globalValidator())
    						return;
    				} 
    				if (me.settings.submitByAjax) {
        				$.ajax({
        					url:me.settings.submitURL,
        					type:'post',
        					data: postData,
        					dataType:'text',
        					success: function(data) {
        						if (me.settings.submitSuccessCallBack) {
        							me.settings.submitSuccessCallBack(data);
        						} else {
        							alert(me.settings.submitSuccessMessage);    							
        						}
        					},
        					error: function() {
        						if (me.settings.submitErrorCallBack) {
        							me.settings.submitErrorCallBack();
        						} else {
        							alert(me.settings.submitErrorMessage);    							
        						}
        					}
        				});
    				} else {
    					var theFunnyForm=$('<form />').appendTo(document.body);
    					$(theFunnyForm)
    						.attr("action",me.settings.submitURL)
    						.attr("method","post")
    						.css('display','none');
    					
    					var postData=$._forms[me.settings.formContainer].postData;
    					for (var fld in postData) {
    						if (postData[fld]!=undefined && postData[fld]!=null) {
    							$('<input />').attr({
    								"type":"text",
    								"name":fld,
    								"value":postData[fld]
    							}).appendTo(theFunnyForm);
    						}
    					}
    					
    					/*
    					for (var theField=0; theField<me.fields.length; theField++) {
    						var field=$(me.fields[theField]).clone();
    	    				var name=$(field).attr('table-field');
    	    				if (!name || name==null)
    	    					var name=$(field).attr('name');
    	    				if (!name || name==null)
    	    					var name=$(field).attr('id');

    	    				$(field).attr("name",name);
    						$(field).appendTo($(theFunnyForm));
    					}
    					*/
    					$(theFunnyForm).submit();
    				}
    				
    			}
    		});
    	}
    	
        return this;
 
    };
 
}( jQuery ));