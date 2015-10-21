<!DOCTYPE html>
<html lang="en">
    <head>        
        <title>{$title}</title>    
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />        
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        
        <link rel='shortcut icon' href="{Application::GetLink('/resources/img/favicon.ico')}" />
        <link href="{Application::GetCSSLink('/resources/css/styles.css')}" rel="stylesheet" type="text/css" />
        
        <!--[if lt IE 10]><link rel="stylesheet" type="text/css" href="/resources/css/ie.css"/><![endif]-->
        
        <script type="text/javascript" src="/resources/js/plugins/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="/resources/js/plugins/jquery/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/resources/js/plugins/bootstrap/bootstrap.min.js"></script>
        <script type="text/javascript" src="/resources/js/plugins/mcustomscrollbar/jquery.mCustomScrollbar.min.js"></script>        
        
{foreach $scripts as $script}
        <script type="text/javascript" src="{$script}"></script>
{/foreach}
        <script type="text/javascript" src="/resources/js/common-functions.js"></script>        
        <script type="text/javascript" src="/resources/js/init-frontend.js"></script>        
        <script type="text/javascript">
        var MAIN_URL='{Application::GetLink('/')}';
        </script>
        
    </head>
    <body>
        
        <div class="page-container">
            
            <div class="page-head">
                
            </div>
            
            <div class="page-navigation">
                
                <div class="page-navigation-info">
                    <a href="index.html" class="logo">Gemini</a>
                </div>
                
            </div>
            
            <div class="page-content">
            	<div class="container">{include file="$template"}</div>
            </div>
        </div>

    </body>
</html>
