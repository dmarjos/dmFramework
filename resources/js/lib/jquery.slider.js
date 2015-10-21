(function ($) {
    $.sliders = [];

    /*
     * armar uploader
     */
    $.selectUpload = function (object) {
        var sliderId = $(object).attr('sliderId');
        var uploadType = $(object).attr('format');

        $.createUploadForm(sliderId, uploadType);
    };

    /*
     * crear el formulario de upload
     */
    $.createUploadForm = function (sliderId, uploadType, currentData) {
        var settings = $.sliders[sliderId].settings;

        if (!$.sliders[sliderId].formData) {
            $.sliders[sliderId].formData = new FormData();
        }
        var html = '<form method="post" enctype="multipart/form-data" action="' + settings.galleryUrl + '">';
        html += '<input style="display:none" type="file" name="images" id="images"/>';
        html += '<button type="submit" style="display:none" id="btn">Guardar</button>';
        html += '</form>';

        var styles = {
            'position': 'relative',
            'margin-left': 'auto',
            'margin-right': 'auto',
            'background-color': '#ffffff',
            'top': '0px',
            'left': '0px',
            'z-index': '199',
            'opacity': '0.5',
            'filter': 'alpha(opacity=50)'
        };
        if (settings.width != null) {
            if (!isNaN(settings.width))
                styles.width = settings.width + 'px';
            else
                styles.width = settings.width;
        }
        if (settings.height != null) {
            if (!isNaN(settings.height))
                styles.height = settings.height + 'px';
            else
                styles.height = settings.height;
        }

        var modalLayer = $('<div/>',{"id":"modal"}).css(styles).appendTo('#' + sliderId);
        var uploadForm = $('<div/>',{"id":"form"}).css({
            'position': 'absolute',
            'margin-left': 'auto',
            'margin-right': 'auto',
            'width': '442px',
            'height': '80px',
            'border-radius': '10px',
            'border': '1px solid #414141',
            'background-color': '#ffffff',
            'z-index': '200'
        }).appendTo('#' + sliderId);
        if (uploadType == 'picture' && !currentData) {
            $(uploadForm).html(html);
        }
        var centerY = ($('#' + sliderId).height() - $(uploadForm).height()) / 2;
        var centerX = ($('#' + sliderId).width() - $(uploadForm).width()) / 2;
        $(uploadForm).css({'top': centerY + 'px', 'left': centerX + 'px'});
        // Button close
        $('<img />', {'src': settings.imagesPath + '/close-small.png', 'id': 'btn-close', 'title': 'Cerrar'})
                .css({
                    'position': 'absolute',
                    'top': '3px',
                    'right': '3px',
                    'cursor': 'pointer',
                    'opacity': '0.5',
                    'filter': 'alpha(opacity=50)'
                })
                .hover($.fullColor, $.fadedColor)
                .click(function () {
                    $('#' + sliderId + ' #form').remove();
                    $('#' + sliderId + ' #modal').remove();
                })
                .appendTo($(uploadForm));
        // Button save
        $('<img />', {
        	'src': settings.imagesPath + '/save.png', 
        	'id': 'btn-save', 'title': 'Guardar'
        }).css({
            'position': 'absolute',
            'top': '27px',
            'right': '3px',
            'cursor': 'pointer',
            'display': 'none',
            'opacity': '0.5',
            'filter': 'alpha(opacity=50)'
        }).hover($.fullColor, $.fadedColor).click(function () {
        	var title='';
            switch (uploadType) {
                case 'picture':
                    title = $.trim($('#' + sliderId + ' #form #title').val());
                    if (title == $('#' + sliderId + ' #form #title').attr('prompt'))
                        title = '';
                    break;
                case 'youtube':
                    title = $.trim($('#' + sliderId + ' #form #title').val());
                    if (title == $('#' + sliderId + ' #form #title').attr('prompt'))
                        title = '';
                    if (title == '') {
                        //alert(settings.requiredTitle);
                        $('#' + sliderId + ' #form #title').focus();
                        return;
                    }
                    if (description == '') {
                        //alert(settings.requiredCode);
                        $('#' + sliderId + ' #form #description').focus();
                        return;
                    }
            }
            if (!currentData)
                $.sliders[sliderId].formData.append('action', 'upload');
            else {
                $.sliders[sliderId].formData.append('action', 'update');
                $.sliders[sliderId].formData.append('file_id', currentData["id"]);
            }
            $.sliders[sliderId].formData.append('uploadType', uploadType);
            $.sliders[sliderId].formData.append('group', settings.resourceId);
            $.sliders[sliderId].formData.append('related', settings.relatedRecord);
            $.sliders[sliderId].formData.append('title', title);

            $('<div />',{
            	"id":"wait_" + sliderId
            }).css({
                'position': 'fixed',
                'z-index': '1000',
                'top': '0',
                'left': '0',
                'height': '100%',
                'width': '100%',
                'background-color': 'rgba( 255, 255, 255, .8 )',
                'background-image': "url('" + settings.imagesPath + "/wait.gif')",
                'background-position': '50% 50%',
                'background-repeat': 'no-repeat'
            }).appendTo('#' + sliderId);
            $.ajax({
                url: settings.sliderUrl,
                type: "POST",
                data: $.sliders[sliderId].formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#' + sliderId + ' #form').remove();
                    $('#' + sliderId + ' #modal').remove();
                    $('#' + sliderId + ' #wait_' + sliderId).remove();
                    $.showSlider(sliderId, data);
                }
            });
        }).appendTo($(uploadForm));

        var titlePrompt = '';
        var titleWidth = 0;
        switch (uploadType) {
            case 'picture':
                titlePrompt = '';
                if (!currentData) {
                    titleWidth = 270;
                    // Button explore
                    $('<img />',{
                    	"src":settings.imagesPath + '/explore.png'
                    }).css({
                        'position': 'absolute',
                        'top': '3px',
                        'right': '35px',
                        'cursor': 'pointer',
                        'opacity': '0.5',
                        'filter': 'alpha(opacity=50)'
                    })
                    .hover($.fullColor, $.fadedColor)
                    .click(function () {
                        $('#' + sliderId + ' #form #images').trigger('click');
                    })
                    .appendTo($(uploadForm));

                    $('#' + sliderId + ' #form #images').bind('change', function () {
                        $.sliders[sliderId].formData = new FormData();
                        var reader, file;

                        file = this.files[0];
                        if (!!file.type.match(/image.*/)) {
                            if (window.FileReader) {
                                reader = new FileReader();
                                reader.onloadend = function (e) {
                                    $.showItemPreview(sliderId, e.target.result, file.name);
                                    $('#' + sliderId + ' #form #btn-save').css('display', '');
                                };
                                reader.readAsDataURL(file);
                            }
                            if ($.sliders[sliderId].formData) {
                                $.sliders[sliderId].formData.append("images[]", file);
                            }
                        }
                    });
                } else {
                    titleWidth = 270;
                }
                break;
            case 'youtube':
                titlePrompt = 'YouTube video title (mandatory)';
                titleWidth = 295;
                break;
        }
        $('<input />').attr({
            'type': 'text',
            'id': 'title',
            'maxlength': '40',
            'prompt': titlePrompt
        }).css({
            'border': '2px solid #414141',
            'border-radius': '5px',
            'width': titleWidth + 'px',
            'height': '22px',
            'position': 'absolute',
            'top': '3px',
            'left': '3px'
        }).appendTo($(uploadForm));
        if (currentData) {
            $('#' + sliderId + ' #form #title').val(currentData.title);
            $('#' + sliderId + ' #form #btn-save').css('display', '');
        }
    };

    /*
     * preview de imagen
     */
    $.showItemPreview = function (sliderId, source, name) {
        $('<img />').attr({
            "src": source,
            "width": "75px",
            "height": "75px",
        }).css({
            'border-radius': '10px',
            'position': 'absolute',
            'left': '295px',
            'bottom': '3px'
        }).appendTo('#' + sliderId + ' #form');
        $('#' + sliderId + ' #form #title').val(name);
    };

    /*
     * Cargar galeria
     */
    $.loadSlider = function (sliderId) {
        var postData = {
            action: 'getFiles',
            related: $.sliders[sliderId].settings.relatedRecord,
            group: $.sliders[sliderId].settings.resourceId
        };
        $.ajax({
            url: $.sliders[sliderId].settings.sliderUrl,
            data: postData,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $.showSlider(sliderId, data);
            },
            error: function (data) {
                alert(data);
            }
        });
    };

    /*
     * Mostrar imagenes
     */
    $.showSlider = function (sliderId, data) {
        if (data.files.length == 0) {
            $('#' + sliderId + ' #browse').css('display', 'none');
            $('#' + sliderId + ' #actions').css('display', 'none');
            return;
        }
        $('#' + sliderId + ' #viewer').html('');
        $('#' + sliderId + ' #browse').css('display', '');
        $('#' + sliderId + ' #actions').css('display', '');
        var settings = $.sliders[sliderId].settings;
        var currentElement = settings.currentElement || 1;

        if (currentElement > data.files.length)
            currentElement = data.files.length;

        settings.currentElement = currentElement;
        settings.previousElement = null;
        settings.elements = data.files;

        // Mostrar las imagenes
        var fullWidth = 0;
        var newElement = null;
        for (var i = 0; i < settings.elements.length; i++) {
            var element = settings.elements[i];
            switch (element.type) {
                case "picture":
                    if (settings.thumbTemplate != null) {
                        var urlImg = element.name;
                        var img = settings.thumbTemplate.replace('[imgUrl]', urlImg);
                        newElement = $(img).appendTo($('#' + sliderId + ' #viewer'));
                        // Thumbnail
                        $(newElement).find('img').attr({
                        	"data-element-id": i + 1, 
                        	'data-file-id': element.id
                        }).css({
                        	'max-height':$('#' + sliderId).height()
                        }).fancybox({
                            'transitionIn': 'elastic',
                            'transitionOut': 'elastic',
                            'speedIn': 600,
                            'speedOut': 200,
                            'overlayShow': false,
                        });
//                                .click(function () {
//                                    $.showLargeImage(sliderId, this);
//                                });
                    } else {
                        var styles = {
                            'margin-left': settings.thumbMarginSides + 'px',
                            'margin-right': settings.thumbMarginSides + 'px',
                            float: 'left',
                            'cursor': 'pointer'
                        };
                        if (settings.thumbWidth != null) {
                            if (!isNaN(settings.thumbWidth))
                                styles.width = settings.thumbWidth + 'px';
                            else
                                styles.width = settings.thumbWidth;
                        }
                        if (settings.thumbHeight != null) {
                            if (!isNaN(settings.thumbHeight))
                                styles.height = settings.thumbHeight + 'px';
                            else
                                styles.height = settings.thumbHeight;
                        }
                        // Thumbnail
                        newElementAhref = $('<a />', {href: element.name})
                                .fancybox({
                                    'transitionIn': 'elastic',
                                    'transitionOut': 'elastic',
                                    'speedIn': 600,
                                    'speedOut': 200,
                                    'overlayShow': false
                                })
                        newElement = $('<img />', {src: element.name, "data-element-id": i + 1, 'data-file-id': element.id})
                                .css(styles)

//                                .click(function () {
//                                    $.showLargeImage(sliderId, this);
//                                })
                                .appendTo(newElementAhref);
                        newElementAhref.appendTo($('#' + sliderId + ' #viewer'))
                        if (settings.thumbClass != null)
                            $(newElement).addClass(settings.thumbClass);
                    }
                    fullWidth += $(newElement).width();// || !isNaN(settings.thumbWidth)?settings.thumbWidth:0;
            }
        }
        $('#' + sliderId + ' #viewer img[data-element-id=' + currentElement + ']').css('border', '1px solid #ff0000');
        if (fullWidth > $('#' + sliderId + ' #viewer').width())
            $('#' + sliderId + ' #viewer').css('width', fullWidth + 'px');
        if (settings.elements.length == settings.maxElements) {
            $('#' + sliderId + '-uploaderContainer').css('display', 'none');
        } else
            $('#' + sliderId + '-uploaderContainer').css('display', '');
        settings.fullWidth = fullWidth;
        $.sliders[sliderId].settings = settings;
    };

    /*
     * Mostrar imagen grande
     */
    $.showLargeImage = function (sliderId, obj) {
        var settings = $.sliders[sliderId].settings;
        $('<div />').attr("id", "wait_" + sliderId).css({
            'position': 'fixed',
            'z-index': '1000',
            'top': '0',
            'left': '0',
            'height': '100%',
            'width': '100%',
            'background-color': 'rgba( 255, 255, 255, .8 )',
            'background-repeat': 'no-repeat'
        }).appendTo(document.body);
        var styles = {
            'max-width': $(document.body).width() - 200,
            'height': 'auto',
            'position': 'absolute',
            //'margin-left':'auto',
            //'margin-right':'auto',
            'max-height': $(document.body).height() - 100,
        };
        var theImg = $('<img />', {'width': $(document.body).width() - 50, src: $(obj).attr("src")}).css(styles).appendTo("#wait_" + sliderId);
        var imgW = $(theImg).width();
        var imgH = $(theImg).height();
        var centerX = ($(document.body).width() - imgW) / 2;
        var centerY = ($(document.body).height() - imgH) / 2;
        $(theImg)
                .css({
                    top: centerY + 'px',
                    left: centerX + 'px',
                })
                .click(function () {
                    $("#wait_" + sliderId).remove();
                });
        $('<img />', {src: settings.imagesPath + '/close-large.png'})
                .css({
                    'position': 'absolute',
                    'top': (centerY - 16) + 'px',
                    'left': (centerX + imgW - 16) + 'px',
                    'cursor': 'pointer'
                })
                .click(function () {
                    $("#wait_" + sliderId).remove();
                })
                .appendTo($("#wait_" + sliderId));
    };
    /*
     * Navegar slider
     */
    $.nextElement = function (sliderId) {
        var settings = $.sliders[sliderId].settings;

        $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').css('border', '0px');


        settings.currentElement++;
        if (settings.currentElement > settings.elements.length) {
            settings.currentElement = 1;
            var elementPosition = $('#' + sliderId + ' #viewer').position();
            if (elementPosition.left < 0)
                $('#' + sliderId + ' #viewer').animate({'left': '0px'});
        }


        $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').css('border', '1px solid #ff0000');
        var elementPosition = $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').position();
        if (elementPosition.left + $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').width() > $('#' + sliderId).width()) {
            var viewerLeft = $('#' + sliderId).width() - (elementPosition.left + $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').width() + (settings.thumbMarginSides * 2));
            $('#' + sliderId + ' #viewer').animate({'left': viewerLeft + 'px'}, 300);
        }
        if (settings.currentElement == settings.elements.length)
            $('#' + sliderId + ' #browse #move-after').css('display', 'none');
        else
            $('#' + sliderId + ' #browse #move-after').css('display', '');
        if (settings.currentElement == 1)
            $('#' + sliderId + ' #browse #move-before').css('display', 'none');
        else
            $('#' + sliderId + ' #browse #move-before').css('display', '');
    };

    $.previousElement = function (sliderId) {
        var settings = $.sliders[sliderId].settings;

        $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').css('border', '0px');

        settings.currentElement--;
        if (settings.currentElement < 1) {
            var elementPosition = $('#' + sliderId + ' img[data-element-id=' + settings.elements.length + ']').position();
            if (elementPosition.left + $('#' + sliderId + ' img[data-element-id=' + settings.elements.length + ']').width() > $('#' + sliderId).width()) {
                var viewerLeft = $('#' + sliderId).width() - (elementPosition.left + $('#' + sliderId + ' img[data-element-id=' + settings.elements.length + ']').width() + (settings.thumbMarginSides * 2));
                $('#' + sliderId + ' #viewer').animate({'left': viewerLeft + 'px'}, 300);
            }
            settings.currentElement = settings.elements.length;
        }
        $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').css('border', '1px solid #ff0000');
        var elementPosition = $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').position();
        var elementWidth = $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').width() + (settings.thumbMarginSides * 2);
        var viewerPosition = $('#' + sliderId + ' #viewer').position();
        if ((viewerPosition.left + elementPosition.left) < 0) {
            var viewerLeft = viewerPosition.left + (elementWidth - (viewerPosition.left + elementPosition.left + elementWidth));
            $('#' + sliderId + ' #viewer').animate({'left': viewerLeft + 'px'}, 300);
        }
        if (settings.currentElement == settings.elements.length)
            $('#' + sliderId + ' #browse #move-after').css('display', 'none');
        else
            $('#' + sliderId + ' #browse #move-after').css('display', '');
        if (settings.currentElement == 1)
            $('#' + sliderId + ' #browse #move-before').css('display', 'none');
        else
            $('#' + sliderId + ' #browse #move-before').css('display', '');
    };

    /*
     * Acciones
     */
    $.performAction = function (sliderId, obj) {
        var action = $(obj).attr('action');
        var settings = $.sliders[sliderId].settings;
        var file_id = $('#' + sliderId + ' img[data-element-id=' + settings.currentElement + ']').attr("data-file-id");

        var formData = {
            action: action,
            elementId: file_id,
            related: settings.relatedRecord,
            group: settings.resourceId,
        };

        if (action == 'delete') {
            if (!confirm('Desea eliminar la imagen seleccionada?'))
                return;
            $.ajax({
                url: settings.sliderUrl,
                data: formData,
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $.showSlider(sliderId, data);
                }
            });
            return;
        }
        if (action == 'move-up') {
            $.ajax({
                url: settings.sliderUrl,
                data: formData,
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $.showSlider(sliderId, data);
                    $.previousElement(sliderId);
                }
            });
            return;
        }
        if (action == 'move-down') {
            $.ajax({
                url: settings.sliderUrl,
                data: formData,
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $.showSlider(sliderId, data);
                    $.nextElement(sliderId);
                }
            });
            return;
        } else if (action == "settings") {
            var elementToShow = settings.elements[settings.currentElement - 1];
            $.createUploadForm(sliderId, elementToShow['type'], elementToShow);
        }
    }
    /*
     * Inicializador de la libreria
     */

    $.fn.slider = function (options) {
        var sliderId = $(this).attr("id");
        if (sliderId == null || sliderId == undefined) {
            sliderId = $.uniqId();
            $(this).attr("id", sliderId);
        }
        $.sliders[sliderId] = {settings: {}};
        var settings = $.extend({
            imagesPath: '',
            sliderUrl: Application.getLink('/ajax/gallery'),
            resourceId: '',
            backgroundColor: '#f5f5f5',
            width: 564,
            height: 250,
            readOnly:false,
            thumbWidth: 113,
            thumbHeight: 84,
            thumbMarginSides: 10,
            thumbClass: null,
            thumbTemplate: null,
            fadeSpeed: 500,
            maxElements: 10,
            formats: 'picture',
            elements: [],
            requiredTitle: 'The TITLE field is required!',
            requiredCode: 'The CODE field is required!',
            confirmDeletion: 'Do you confirm that you want to delete this element?',
            submitByAjax: true
        }, options);
        $.sliders[sliderId].settings = settings;

        var styles = {
            'position': 'relative',
            'top': '0px',
            'left': '0px',
            'overflow': 'hidden',
            'background-color': settings.backgroundColor,
        };
        if (settings.width != null) {
            if (!isNaN(settings.width))
                styles.width = settings.width + 'px';
            else
                styles.width = settings.width;
        }
        if (settings.height != null) {
            if (!isNaN(settings.height))
                styles.height = settings.height + 'px';
            else
                styles.height = settings.height;
        }

        $(this).html('').css(styles);
        if (!settings.readOnly) {
	        var formats = settings.formats.split('|');
	        var cntWidth = (formats.length * 25) + 5;
	        var uploadersContainer = $('<div />', {'id': sliderId + '-uploaderContainer'}).appendTo($(this)).css({
	            'position': 'absolute',
	            'top': (settings.height - 25) + 'px',
	            'z-index': '100',
	            'width': cntWidth + 'px',
	            'right': '3px',
	            'height': '22px'
	        });
	        var cntWidth = 0;
	        for (var f = 0; f < formats.length; f++) {
	            var imagePath = settings.imagesPath + '/' + formats[f] + '.png';
	            var img = $('<img />',{
	            	'format':formats[f],
	            	'sliderId':sliderId,
	            	'src':imagePath
	            }).appendTo(uploadersContainer);
	            $(img).css({
	                'cursor': 'pointer',
	                'opacity': '0.5',
	                'filter': 'alpha(opacity=50)'
	            }).click(function () {
	                $.selectUpload(this);
	            }).hover($.fullColor, $.fadedColor);
	            cntWidth += $(img).width();
	        }
        }
        styles = {
            'position': 'absolute',
            'top': '0px',
            'left': '0px',
            'width': 10000,
            'z-index': '70',
        };
        /*
         if (settings.width!=null) {
         if (!isNaN(settings.width))
         styles.width=settings.width+'px';
         else
         styles.width=settings.width;
         }
         */
        if (settings.height != null) {
            if (!isNaN(settings.height))
                styles.height = settings.height + 'px';
            else
                styles.height = settings.height;
        }
        $('<div />').attr('id', 'viewer').appendTo($(this)).css(styles);
        var browseContainer = $('<div />').attr('id', 'browse').css({
            'position': 'absolute',
            'top': '10px',
            'left': '0px',
            //'display':'none',
            'z-index': '100',
            'height': '22px'
        }).appendTo($(this));
        var left;
        if (!settings.readOnly)
        	left = ($(this).width() - 138) / 2;
        else
        	left = ($(this).width() - 94) / 2;
        
        if (!settings.readOnly) {
	        var moveLeft = $('<img />', {
	        	'id': 'move-before', 
	        	'action': 'move-up', 
	        	'src': settings.imagesPath + '/move-before.png'
	        }).css({
                'position': 'absolute',
                'top': '0px',
                'left': left + 'px',
                'opacity': '0.5',
                'display': 'none',
                'filter': 'alpha(opacity=50)',
                'cursor': 'pointer'
            })
            .click(function () {
                $.performAction(sliderId, this);
            })
            .hover($.fullColor, $.fadedColor)
            .appendTo($(browseContainer));
	        left += $(moveLeft).width() || 22;
    	}
        
        var previous = $('<img />').attr('id', 'previous').attr('src', settings.imagesPath + '/previous.png')
                .css({
                    'position': 'absolute',
                    'top': '0px',
                    'left': left + 'px',
                    'opacity': '0.5',
                    'filter': 'alpha(opacity=50)',
                    'cursor': 'pointer'
                })
                .click(function () {
                    $.previousElement(sliderId, this);
                })
                .hover($.fullColor, $.fadedColor)
                .appendTo($(browseContainer));
        left += $(previous).width() || 22;

        var counter = $('<div />').attr('id', 'counter').css({
            'position': 'absolute',
            'top': '0px',
            'left': left + 'px',
            'width': '50px',
            'text-align': 'center'
        }).appendTo($(browseContainer));
        left += $(counter).width() || 50;

        var next = $('<img />').attr('id', 'next').attr('src', settings.imagesPath + '/next.png')
                .css({
                    'position': 'absolute',
                    'top': '0px',
                    'left': left + 'px',
                    'opacity': '0.5',
                    'filter': 'alpha(opacity=50)',
                    'cursor': 'pointer'
                })
                .click(function () {
                    $.nextElement(sliderId, this);
                })
                .hover($.fullColor, $.fadedColor)
                .appendTo($(browseContainer));
        left += $(next).width() || 22;
        if (!settings.readOnly) {
	        var moveAfter = $('<img />', {'id': 'move-after', 'action': 'move-down', 'src': settings.imagesPath + '/move-after.png'})
	                .css({
	                    'position': 'absolute',
	                    'top': '0px',
	                    'left': left + 'px',
	                    'opacity': '0.5',
	                    'filter': 'alpha(opacity=50)',
	                    'cursor': 'pointer'
	                })
	                .click(function () {
	                    $.performAction(sliderId, this);
	                })
	                .hover($.fullColor, $.fadedColor)
	                .appendTo($(browseContainer));
	        left += $(moveAfter).width() || 22;
        }
        
        if (!settings.readOnly) {
	        var actionsContainer = $('<div />').attr('id', 'actions').appendTo($(this)).css({
	            'position': 'absolute',
	            'top': (settings.height - 25) + 'px',
	            'z-index': '100',
	            'left': '3px'
	        });
	
	        var actions = ['delete', 'settings'];
	        var cntWidth = 0;
	        for (var a = 0; a < actions.length; a++) {
	            var imagePath = settings.imagesPath + '/' + actions[a] + '.png';
	            var img = $('<img />').attr('src', imagePath).appendTo(actionsContainer);
	            $(img)
	                    .css({
	                        'cursor': 'pointer',
	                        'margin-right': '5px',
	                        'opacity': '0.5',
	                        'filter': 'alpha(opacity=50)'
	                    })
	                    .attr({'id': actions[a], 'action': actions[a]})
	                    .click(function () {
	                        $.performAction(sliderId, this);
	                    })
	                    .hover($.fullColor, $.fadedColor);
	            cntWidth += $(img).width() + 5;
	        }
        }

        $.loadSlider(sliderId);

    };

}(jQuery));