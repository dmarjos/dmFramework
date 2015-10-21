<!DOCTYPE html>
<html lang="en">
    <head>        
        <title>{$title}</title>    
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />        
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel='shortcut icon' href="{$favicon}" />
        <link href="{Application::GetCSSLink('/resources/css/styles.css')}" rel="stylesheet" type="text/css" />
        
        <!--[if lt IE 10]><link rel="stylesheet" type="text/css" href="{Application::GetLink('/resources/css/ie.css')}"/><![endif]-->
        
{if Application::$page->isLoggedIn()}
{foreach $scripts as $script}
        <script type="text/javascript" src="{$script}"></script>
{/foreach}
{/if}
        <script type="text/javascript">
        var MAIN_URL='{Application::GetLink('/')}';
        </script>
    </head>
    <body>
        
        <div class="page-container">
            
            <div class="page-head">
                
                <ul class="page-head-elements">
                    <li><a href="#" class="page-navigation-toggle"><span class="fa fa-bars"></span></a></li>
                    <li class="search">
                        <input type="text" class="form-control" placeholder="Search..."/>
                    </li>
                </ul>

                <ul class="page-head-elements pull-right">
                    <li>
                        {if intval($message_count)>0}<div class="informer informer-pulsate">{$message_count}</div>{/if}
                        <a href="#" class="dropdown"><span class="fa fa-comments"></span></a> 
                        {if !empty($messages)}                       
                        <div class="popup">
                            <div class="list no-controls scroll">
                            	{foreach $messages as $message}
								{if $message["type"]=="comentarios"}
                                <a href="{Application::getLink("/admin/blog/comentarios")}" class="list-item">
                                    <div class="list-item-content">
                                        <h4>Hay {$message["number"]} comentario{if $message["number"]>1}s{/if} pendiente{if $message["number"]>1}s{/if}</h4>
                                    </div>                                
                                </a>
                                {/if}
								{if $message["type"]=="ticket_count"}
                                <a href="{Application::getLink("/admin/soporte/tickets")}" class="list-item">
                                    <div class="list-item-content">
                                        <h4>Hay {$message["number"]} ticket{if $message["number"]>1}s{/if} abierto{if $message["number"]>1}s{/if}</h4>
                                    </div>                                
                                </a>
                                {/if}
                                {/foreach}
                            </div>
                            <div class="popup-block tac"><a href="/resources/pages-mailbox-inbox.html">Show all messages</a></div>
                        </div>
                        {if intval($message_count)>0}<div class="informer informer-pulsate">{$message_count}</div>{/if}
                        {/if}
                    </li>
                    
                    <!--li><a href="#" class="page-sidebar-toggle"><span class="fa fa-tasks"></span></a></li-->
                </ul>
                
            </div>
            
            <div class="page-navigation">
                
                <div class="page-navigation-info">
                    <a href="{Application::GetLink("/admin")}" {if $logo}style="background: url('{$logo}') left top no-repeat !important; background-size: contain !important;" {/if}class="logo">Gemini</a>
                </div>
                {if Application::get("page")->meetRules('backend',UserRules::VIEW)}
                <div class="profile">                    
                    <div class="profile-info">
                        <a href="#" class="profile-title">{Application::getUserName()}</a>
                        <span class="profile-subtitle">{Application::getUserRole()}</span>
                        <div class="profile-buttons">
                            <div class="btn-group">                                
                                <a class="but dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{Application::getLink('/admin/perfiles/editar')}">Perfil</a></li>
                                    <li><a href="{Application::getLink('/admin/logout')}">Cerrar sesi&oacute;n</a></li>
                                </ul>
                            </div>
                        </div>                        
                    </div>
                </div>
                {/if}
                <ul class="navigation">
                    <li><a href="{Application::GetLink("/admin")}"><i class="fa fa-dashboard"></i> Inicio</a></li>
                    {foreach $menu as $key => $options}
					{if Application::get("page")->meetRules($options["rules"], UserRules::VIEW)}
                    <li {if $options["status"]=="open"}class="open"{/if}><a href="{if $options["link"]}{$options["link"]}{else}#{/if}">{$options["text"]}</a>
                    {if is_array($options["options"])}
                        <ul>
                        	{foreach $options["options"] as $suboption}
							{if Application::get("page")->meetRules($suboption["rules"], UserRules::VIEW)}<li><a href="{if $suboption["link"]}{$suboption["link"]}{else}#{/if}">{if is_array($suboption["options"])}<i class="fa fa-caret-right"></i>{/if}{$suboption["text"]}</a>
		                    {if is_array($suboption["options"])}
		                        <ul>
		                        	{foreach $suboption["options"] as $suboption2}
									{if Application::get("page")->meetRules($suboption2["rules"], UserRules::VIEW)}<li><a href="{$suboption2["link"]}">{$suboption2["text"]}</a></li>{/if}
									{/foreach}
		                        </ul>
		                    {/if}
		                    </li>
		                    {/if}
							{/foreach}
							
                        </ul>
                    {/if}
                    </li>
                    {/if}
                    {/foreach}                    
                </ul>
            </div>
            <div class="page-content">
            	<div class="container">{include file="$template"}</div>
            </div>
            <script type="text/javascript">
            </script>
        </div>

    </body>
</html>
