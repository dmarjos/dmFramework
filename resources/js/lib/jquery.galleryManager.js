(function( $ ) {
	$.createUploadForm = function(galleryId,uploadType, currentData) {
		var settings=$.galleries[galleryId].settings;

		if (!$.galleries[galleryId].formData) {
			$.galleries[galleryId].formData=new FormData();
		}
		
		
		var html='<form method="post" enctype="multipart/form-data" action="'+settings.galleryUrl+'">';
		html+='<input style="display:none" type="file" name="images" id="images"/>';
		html+='<button type="submit" style="display:none" id="btn">Guardar</button>';
		html+='</form>';

		var modalLayer=$('<div/>').attr("id","modal").css({
			'position': 'relative',
			'margin-left':'auto',
			'margin-right':'auto',
			'width':settings.width+'px',
			'height':settings.height+'px',
			'background-color':'#ffffff',
			'top':'0px',
			'left':'0px',
			'z-index':'199',
			'opacity':'0.5',
			'filter':'alpha(opacity=50)'
		}).appendTo('#'+galleryId);
		
		var uploadForm=$('<div/>').attr("id","form").css({
			'position': 'absolute',
			'margin-left':'auto',
			'margin-right':'auto',
			'width':'342px',
			'height':'150px',
			'border-radius':'10px',
			'border':'1px solid #414141',
			'background-color':'#ffffff',
			'z-index':'200'
		}).appendTo('#'+galleryId);

		if (uploadType=='picture' && !currentData) {
			$(uploadForm).html(html);
		}
		
		var centerY=(settings.height-$(uploadForm).height())/2;
		var centerX=(settings.width-$(uploadForm).width())/2;
		$(uploadForm).css({'top':centerY+'px','left':centerX+'px'});

		$('<img />').attr("src",settings.imagesPath+'/save.png').css({
			'position':'absolute',
			'top':'3px',
			'right': '3px',
			'cursor':'pointer',
			'opacity':'0.5',
			'filter':'alpha(opacity=50)'
		}).hover($.fullColor,$.fadedColor).click(function() {
			switch(uploadType) {
				case 'picture':
					var title=$.trim($('#'+galleryId+' #form #title').val());
					if (title==$('#'+galleryId+' #form #title').attr('prompt')) title='';
					var description=$.trim($('#'+galleryId+' #form #description').val());
					if (description==$('#'+galleryId+' #form #description').attr('prompt')) description='';
					break;
				case 'youtube':
					var title=$.trim($('#'+galleryId+' #form #title').val());
					if (title==$('#'+galleryId+' #form #title').attr('prompt')) title='';
					var description=$.trim($('#'+galleryId+' #form #description').val());
					if (description==$('#'+galleryId+' #form #description').attr('prompt')) description='';
					if (title=='') {
						//alert(settings.requiredTitle);
						$('#'+galleryId+' #form #title').focus();
						return;
					}
					if (description=='') {
						//alert(settings.requiredCode);
						$('#'+galleryId+' #form #description').focus();
						return;
					}
			}
			if (!currentData)
				$.galleries[galleryId].formData.append('action','upload');
			else {
				$.galleries[galleryId].formData.append('action','update');
				$.galleries[galleryId].formData.append('file_id',currentData["id"]);
			}
			$.galleries[galleryId].formData.append('uploadType',uploadType);
			$.galleries[galleryId].formData.append('group',settings.resourceId);
			$.galleries[galleryId].formData.append('title',title);
			$.galleries[galleryId].formData.append('description',description);
			
			$('<div />').attr("id","wait_"+galleryId).css({
				'position':'fixed',
				'z-index':'1000',
				'top':'0',
				'left':'0',
				'height':'100%',
				'width':'100%',
				'background-color':'rgba( 255, 255, 255, .8 )', 
				'background-image':"url('"+settings.imagesPath+"/wait.gif')", 
                'background-position':'50% 50%', 
                'background-repeat':'no-repeat'
			}).appendTo('#'+galleryId);
			/**
.modal {
    		display:    none;
    		position:   fixed;
    		z-index:    1000;
    		top:        0;
    		left:       0;
    		height:     100%;
    		width:      100%;
    		background: rgba( 255, 255, 255, .8 ) 
                		url('http://i.stack.imgur.com/FhHRx.gif') 
                		50% 50% 
                		no-repeat;
}			 */
			
			$.ajax({
				url: settings.galleryUrl,
				type: "POST",
				data: $.galleries[galleryId].formData,
				processData: false,
				contentType: false,
				success: function (data) {
					$('#'+galleryId+' #form').remove();
					$('#'+galleryId+' #modal').remove();
					$('#'+galleryId+' #wait_'+galleryId).remove();
					$.showGallery(galleryId,data);
				}
			});
		}).appendTo($(uploadForm));
		$('<img />').attr("src",settings.imagesPath+'/close.png').css({
			'position':'absolute',
			'top':'3px',
			'right': '-31px',
			'cursor':'pointer',
			'opacity':'0.5',
			'filter':'alpha(opacity=50)'
		}).hover($.fullColor,$.fadedColor).click(function() {
			$('#'+galleryId+' #form').remove();
			$('#'+galleryId+' #modal').remove();
		}).appendTo($(uploadForm));
	
		/* */
		switch(uploadType) {
			case 'picture':
				var titlePrompt='';
				var descriptionPrompt='Picture description';
				if (!currentData) {
					titleWidth=270;
					descriptionWidth=216;
					$('<img />').attr("src",settings.imagesPath+'/explore.png').css({
						'position':'absolute',
						'top':'3px',
						'right': '35px',
						'cursor':'pointer',
						'opacity':'0.5',
						'filter':'alpha(opacity=50)'
					}).hover($.fullColor,$.fadedColor).click(function() {
						$('#'+galleryId+' #form #images').trigger('click');
					}).appendTo($(uploadForm));
					$('#'+galleryId+' #form #images').bind('change', function() {
						$.galleries[galleryId].formData = new FormData();
				 		var i = 0, len = this.files.length, img, reader, file;
	
						file = this.files[0];
						if (!!file.type.match(/image.*/)) {
							if ( window.FileReader ) {
								reader = new FileReader();
								reader.onloadend = function (e) { 
									$.showItemPreview(galleryId, e.target.result, file.name);
								};
								reader.readAsDataURL(file);
							}
							if ($.galleries[galleryId].formData) {
								$.galleries[galleryId].formData.append("images[]", file);
							}	
						}
					});
				} else {
					titleWidth=270;
					descriptionWidth=330;
				}
				break;
			case 'youtube':
				var titlePrompt='YouTube video title (mandatory)';
				var descriptionPrompt='YouTube video code (mandatory)';
				var titleWidth=295;
				var descriptionWidth=330;
				break;
		}
	
		$('<input />').attr({
			'type':'text',
			'id':'title',
			'maxlength':'40',
			'prompt':titlePrompt
		}).css({
			'border':'2px solid #414141',
			'border-radius':'5px',
			'width':titleWidth+'px',
			'height':'22px',
			'position':'absolute',
			'top':'3px',
			'left':'3px'
		}).appendTo($(uploadForm));
		
		$('<textarea>').attr({
			'type':'text',
			'id':'description',
			'prompt':descriptionPrompt
		}).css({
			'border':'2px solid #414141',
			'border-radius':'5px',
			'width':descriptionWidth+'px',
			'height':'103px',
			'position':'absolute',
			'top':'37px',
			'left':'3px'
		}).appendTo($(uploadForm));
		
		if (currentData) {
			$('#'+galleryId+' #form #title').val(currentData.title);
			$('#'+galleryId+' #form #description').val(currentData.description);
		}
		
		$('#'+galleryId+' #form input[type=text], #'+galleryId+' #form textarea').each(function() {
			var prompt=$(this).attr("prompt");
			$(this).val(prompt);
			$(this).focus(function(){
				if ($.trim($(this).val())==prompt) 
					$(this).val('');
			}).blur(function() {
				if ($.trim($(this).val())=='') 
					$(this).val(prompt);
			});
		})
		if (currentData) {
			$('#'+galleryId+' #form #title').val(currentData.title);
			$('#'+galleryId+' #form #description').val(currentData.description);
		}
	}
	
	$.selectUpload=function(object) {
		var galleryId=$(object).attr('galleryId');
		var settings=$.galleries[galleryId].settings;
		var uploadType=$(object).attr('format');
		
		$.createUploadForm(galleryId,uploadType);
	};

	$.showItemPreview = function(galleryId, source, name) {
		$('<img />').attr({
			"src":source,
			"width": "110px",
			"height": "110px",
		}).css({
			'border-radius':'10px',
			'position':'absolute',
			'right':'3px',
			'bottom':'3px'
		}).appendTo('#'+galleryId+' #form');
		$('#'+galleryId+' #form #title').val(name);
	};
	
	$.loadGallery = function(galleryId) {
		var postData={
			action: 'getFiles',
			group: $.galleries[galleryId].settings.resourceId
		};
		$.ajax({
			url: $.galleries[galleryId].settings.galleryUrl,
			data:postData,
			type:"POST",
			dataType:'json',
			success: function(data) {
				$.showGallery(galleryId,data);
			},
			error: function(data) {
				alert(data);
			}
		})
	};
	
	$.showGallery = function(galleryId,data) {
		var imageViewer=$('#'+galleryId+' #viewer');
		$(imageViewer).html('');
		var currentElement=$.galleries[galleryId].settings.currentElement || 1;
		
		if (currentElement>data.files.length)
			currentElement=data.files.length;
		
		$.galleries[galleryId].settings.currentElement = currentElement;
		$.galleries[galleryId].settings.previousElement = null;
		$.galleries[galleryId].settings.elements = data.files;
		
		$.showElement(galleryId);
    	currentGalleryLength=0;
    	try {
    		currentGalleryLength=$.galleries[galleryId].settings.elements.length;
    	} catch (e) {}
    	if (currentGalleryLength==$.galleries[galleryId].settings.maxElements) {
    		$('#'+galleryId+'-uploaderContainer').css('display','none');
    	} else
    		$('#'+galleryId+'-uploaderContainer').css('display','');
	};
	
	$.previousElement = function(galleryId) {
		var settings=$.galleries[galleryId].settings;

		settings.currentElement--;
		if (settings.currentElement==0)
			settings.currentElement=settings.elements.length;
		
		var imageViewer=$('#'+galleryId+' #viewer');
		settings.previousElement=$(imageViewer).children()[0];
		$.galleries[galleryId].settings=settings;
		$.showElement(galleryId);
	};
	
	$.nextElement = function(galleryId) {
		var settings=$.galleries[galleryId].settings;

		settings.currentElement++;
		if (settings.currentElement>settings.elements.length)
			settings.currentElement=1;
		
		var imageViewer=$('#'+galleryId+' #viewer');
		settings.previousElement=$(imageViewer).children()[0];
		$.galleries[galleryId].settings=settings;
		$.showElement(galleryId);
	};
	
	$.showElement = function(galleryId) {
		var imageViewer=$('#'+galleryId+' #viewer');
		var actions=$('#'+galleryId+' #actions');
		var browser=$('#'+galleryId+' #browse');
		
		$(actions).css('display','none');
		$(browser).css('display','none');
		var settings=$.galleries[galleryId].settings;
		if (settings.currentElement==0) return;
		$(actions).css('display','block');
		$(browser).css('display','block');

		var zIndex=100;
		var opacity=10;
		if (settings.previousElement) {
			opacity=2;
			zIndex=$(settings.previousElement).css('z-index')+1;
			$(settings.previousElement).fadeTo(settings.fadeSpeed,0.2,function() {
				$(settings.previousElement).remove();
			});
		}

		var elementToShow=settings.elements[settings.currentElement-1];
		var current
		switch (elementToShow['type']) {
			case 'picture': 
				current =$('<div />').css({
					'background-image':'url('+elementToShow['name']+')',
					'background-repeat':'repeat-none',
					'background-size':'cover',
					'width':settings.width+'px',
					'height':settings.height+'px',
					'z-index':zIndex,
		    		'position':'absolute',
		    		'top':'0px',
		    		'left':'0px',
					'opacity':(opacity/10),
					'filter':'alpha(opacity='+(opacity*10)+')'
				});
				break;
			case 'youtube':
				current =$('<div />').css({
					'background-image':'url('+elementToShow['name']+')',
					'background-repeat':'repeat-none',
					'background-size':'cover',
					'width':settings.width+'px',
					'height':settings.height+'px',
					'z-index':zIndex,
		    		'position':'absolute',
		    		'top':'0px',
		    		'left':'0px',
					'opacity':(opacity/10),
					'filter':'alpha(opacity='+(opacity*10)+')'
				});
				break;
		}
		$(current).appendTo($(imageViewer));
		
		$('#'+galleryId+' #delete').attr('file-id',elementToShow['id']);
		$('#'+galleryId+' #settings').attr('file-id',elementToShow['id']);
		
		$(current).fadeTo(settings.fadeSpeed,1,function() {
			zIndex=parseInt($(this).css('z-index'))-1;
			$(this).css({
				'opacity':'1',
				'filter':'alpha(opacity=100)',
				'z-index':zIndex
			});
		});
		var counter=$('#'+galleryId+' #counter');
		
		$(counter).html(settings.currentElement+' / '+settings.elements.length);
	};
	
	
	$.performAction = function (galleryId,object) {
		var file_id=$(object).attr('file-id');
		var action=$(object).attr('action');
		var settings=$.galleries[galleryId].settings;
		
		var formData={
			action:action,
			elementId:file_id,
			group: settings.resourceId,
		};
		if (action=='delete'){
			$.ajax({
				url: settings.galleryUrl,
				data: formData,
				type: "POST",
				dataType: 'json',
				success: function(data) {
					$.showGallery(galleryId,data);
				}
			});
			return;
		} else if (action=="settings") {
			var elementToShow=settings.elements[settings.currentElement-1];
			$.createUploadForm(galleryId,elementToShow['type'],elementToShow);
		}
		
	};
	$.galleries=[];
    $.fn.galleryEditor = function(options) {
    	var galleryId=$(this).attr("id");
    	if (galleryId==null || galleryId==undefined) {
    		galleryId=$.uniqId();
    		$(this).attr("id",galleryId);
    	}
    	$.galleries[galleryId]={settings:{}};
    	var settings = $.extend({
    		imagesPath:'',
            galleryUrl: null,
            resourceId: '',
            'background-color': '#f5f5f5',
            width: 564,
            height: 250,
            fadeSpeed: 500,
            maxElements: 10,
            formats: 'picture',
            requiredTitle: 'The TITLE field is required!',
            requiredCode: 'The CODE field is required!',
            confirmDeletion:'Do you confirm that you want to delete this element?',
            submitByAjax: true
        }, options );
    	
    	$.galleries[galleryId].settings=settings;
    	
    	$(this).html('').css({
    		'height':settings.height+'px',
    		'width':settings.width+'px',
    		'position':'relative',
    		'top':'0px',
    		'left':'0px',
            'background-color': settings['background-color'],
    	});

    	currentGalleryLength=0;
    	try {
    		currentGalleryLength=settings.elements.length;
    	} catch (e) {}
    	if (currentGalleryLength<settings.maxElements) {
        	var formats=settings.formats.split('|');
        	var cntWidth=(formats.length*25)+5;
        	var uploadersContainer=$('<div />',{'id':galleryId+'-uploaderContainer'}).appendTo($(this)).css({
        		'position':'absolute',
        		'top':(settings.height-25)+'px',
        		'z-index':'100',
        		'width':cntWidth+'px',
        		'right':'3px',
        		'height':'22px'
        	});
        	var cntWidth=0;
        	for(var f=0; f<formats.length; f++) {
        		var imagePath=settings.imagesPath+'/'+formats[f]+'.png';
        		var img=$('<img />').attr('src',imagePath).appendTo(uploadersContainer);
        		$(img).css({
        			'cursor':'pointer',
        			'opacity':'0.5',
        			'filter':'alpha(opacity=50)'
        		}).attr('format',formats[f]).attr('galleryId',galleryId).click(function() {
        			$.selectUpload(this);
        		}).hover($.fullColor, $.fadedColor);
        		cntWidth+=$(img).width();
        	}
    	}
    	/*
    	$(uploadersContainer).css({
    		'width':cntWidth+'px',
    		'right':'3px'
    	});
    	*/
    	
    	var imageViewer=$('<div />').attr('id','viewer').appendTo($(this)).css({
    		'position':'absolute',
    		'top':'0px',
    		'left':'0px',
    		'z-index':'70',
    		'width':settings.width+'px',
    		'height':settings.height+'px'
    	});
    	
    	if (settings.maxElements>1) {
	    	var browseContainer=$('<div />').attr('id','browse').appendTo($(this)).css({
	    		'position':'absolute',
	    		'top':'10px',
	    		'left':'0px',
	    		//'display':'none',
	    		'z-index':'100',
	    		'height':'22px'
	    	});
	    	var left=(settings.width-144)/2;
	    	var previous=$('<img />').attr('id','previous').attr('src',settings.imagesPath+'/previous.png').css({
	    		'position':'absolute',
	    		'top':'0px',
	    		'left':left+'px',
				'opacity':'0.5',
				'filter':'alpha(opacity=50)',
	    		'cursor':'pointer'
	    	}).click(function() {
	    		$.previousElement(galleryId,this);
	    	}).hover($.fullColor, $.fadedColor).appendTo($(browseContainer));
	    	left+=$(previous).width();
	
	    	var counter=$('<div />').attr('id','counter').css({
	    		'position':'absolute',
	    		'top':'0px',
	    		'left':left+'px',
	    		'width':'100px',
	    		'text-align':'center'
	    	}).appendTo($(browseContainer));
	    	left+=$(counter).width();
	
	    	var next=$('<img />').attr('id','next').attr('src',settings.imagesPath+'/next.png').css({
	    		'position':'absolute',
	    		'top':'0px',
	    		'left':left+'px',
				'opacity':'0.5',
				'filter':'alpha(opacity=50)',
	    		'cursor':'pointer'
	    	}).click(function() {
	    		$.nextElement(galleryId,this);
	    	}).hover($.fullColor, $.fadedColor).appendTo($(browseContainer));
	    	left+=$(next).width();
    	}
    	var actionsContainer=$('<div />').attr('id','actions').appendTo($(this)).css({
    		'position':'absolute',
    		'top':(settings.height-25)+'px',
    		'z-index':'100',
    		'left':'3px'
    	});
    	
    	var actions=['delete','settings'];
    	var cntWidth=0;
    	for(var a=0; a<actions.length; a++) {
    		var imagePath=settings.imagesPath+'/'+actions[a]+'.png';
    		var img=$('<img />').attr('src',imagePath).appendTo(actionsContainer);
    		$(img).css({
    			'cursor':'pointer',
    			'margin-right':'5px',
    			'opacity':'0.5',
    			'filter':'alpha(opacity=50)'
    		}).attr({'id':actions[a],'action':actions[a]}).click(function() {
    			$.performAction(galleryId,this);
    		}).hover($.fullColor, $.fadedColor);
    		cntWidth+=$(img).width()+5;
    	}
    	
    	$.loadGallery(galleryId);
    }
}( jQuery ));