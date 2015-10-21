$(document.body).ready(function() {
    navigation_state = navigation_state_was = $(".page-navigation").hasClass('page-navigation-closed');
    $(".page-toolbar-tabs a").click(function(){
        var pli = $(this).parent("li");
        var act = $($(this).attr("href"));
        
        $(".page-toolbar-tabs li,.page-toolbar-tab").removeClass("active");
        pli.addClass("active");
        act.addClass("active");
    });
    /* mCustomScrollbar */    
    $(".page-content,.page-navigation").mCustomScrollbar({autoHideScrollbar: true,scrollInertia: 20, advanced: {autoScrollOnFocus: false}});

    if($(".scroll").length > 0) 
        $(".scroll").mCustomScrollbar({autoHideScrollbar: true, advanced: {autoScrollOnFocus: false}});
     
    $(".page-head-elements .dropdown").on("click",function(event){
        var popup = $(this).next(".popup");

        if(!popup.hasClass('open')){
            popup.addClass('open');
            list_height();
            $(".scroll").mCustomScrollbar("update");                        
        }else{
            popup.removeClass('open');
        }        
    });
    
    $(".page-head .search").on("click",function(){        
        $(this).find("input").focus();        
    });

    // navigation sublevels
    $(".navigation > li").each(function(){
        if($(this).children("ul").length > 0){
            $(this).addClass("openable");
        }
    });
    
    $(".navigation li > a").on("click",function(){
        var pli = $(this).parent("li");
        var sub = pli.children("ul");        
        if(sub.length > 0){
            sub.is(":visible") ? sub.slideUp(200,function(){
                pli.removeClass("open");
                $(".page-navigation").mCustomScrollbar("update");
            }) : sub.slideDown(200,function(){
                pli.addClass("open");                
                $(".page-navigation").mCustomScrollbar("update");
            });
            
        }        
    });

    $(".page-navigation-toggle").on("click",function(){        
        page_navigation();
    });
    
    if($(".knob").length > 0) $(".knob input").knob();

    if($(".sparkline").length > 0)
        $(".sparkline").sparkline('html', { enableTagOptions: true,disableHiddenCheck: true});    
    

});

function showError(message) {
	alert(message);
}