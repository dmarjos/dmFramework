function page_navigation(action,sidebar){
    //Hide sidebar if opened
    if($(".page-sidebar").hasClass("page-sidebar-opened") && !sidebar){
        navigation_state_was = $(".page-navigation").hasClass("page-navigation-closed");
        page_sidebar("close");        
        return false;
    }
    
    //Get width of navigation block
    var navigation_width  = $(".page-navigation").width();
    //Get navigation state
    var navigation_action = null != action ? action : 'auto';
    //Get navigation mode
    var navigation_mode   = $(".page-container").hasClass('page-layout-mobile');
        
    if(navigation_action == 'open')
        page_navigation_open(navigation_mode,navigation_width);
    
    if(navigation_action == 'close')
        page_navigation_close(navigation_mode,navigation_width);
    
    if(navigation_action == 'auto'){
        if(!navigation_mode){
            $(".page-navigation").hasClass("page-navigation-closed") 
            ? page_navigation_open(navigation_mode,navigation_width)
            : page_navigation_close(navigation_mode,navigation_width);
        }else{
            !$(".page-navigation").hasClass("page-navigation-opened")
            ? page_navigation_open(navigation_mode,navigation_width)
            : page_navigation_close(navigation_mode,navigation_width);
        }        
    }
    
    return false;
    //End of navigation controller
    
}

function page_navigation_open(mode,width){
    
    if(!mode){ //Desktop mode
        $(".page-content,.page-head").animate({'padding-left': width},0,function(){
            $(this).removeAttr("style");
         });                                
         $(".page-navigation").animate({left: 0},0,function(){
             $(this).removeClass("page-navigation-closed");
             
             if($.isFunction($.updateCharts)) $.updateCharts();
         });        
    }else{ //Mobile mode
        $(".page-container").css({"position":"absolute","width":$(".page-container").width()}).animate({left: width},0);
        $(".page-navigation").addClass("page-navigation-opened"); 
    }
    
    list_height();
    
    return false;
    
}

function page_navigation_close(mode,width){

    if(!mode){ //Desktop mode
        var speed = navigation_state ? 0 : 300;
        $(".page-content,.page-head").animate({"padding-left": 0},0);                
        $(".page-navigation").animate({left: -width},0,function(){
            $(this).addClass("page-navigation-closed");
            
            if($.isFunction($.updateCharts)) $.updateCharts();
        });
    }else{ //Mobile mode
        $(".page-container").animate({left: 0},0,function(){
               $(this).removeAttr("style");
            });
        $(".page-navigation").removeClass("page-navigation-opened");
    }
    
    list_height();
    
    return false;
    
}

function list_height(){
    $(".list .list-item").each(function(){
        $(this).height($(this).find(".list-item-content").height()+10);
    });    
}

