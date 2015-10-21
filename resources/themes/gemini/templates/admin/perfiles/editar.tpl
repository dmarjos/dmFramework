
<div class="page-toolbar">
    
    <div class="page-toolbar-block">
        <div class="page-toolbar-title">{$usr_nombre}</div>
        <div class="page-toolbar-subtitle">{$usr_rol}</div>
    </div>
    
    <div class="page-toolbar-block pull-right">
        <div class="widget-info widget-from" >
            <button class="btn btn-success" id="btn-editar-perfil"><i class="fa fa-pencil"></i> Editar Perfil</button>                                
            <button class="btn btn-success hidden" id="btn-ver-perfil"><i class="fa fa-floppy-o"></i> Ver Perfil</button>                                
            <button class="btn btn-success hidden" data-toggle="modal" data-target="#change-password" id="btn-change-password"><i class="fa fa-floppy-o"></i> Cambiar password</button>                                
            <button class="btn btn-success hidden" data-toggle="modal" data-target="#security-question" id="btn-security-question"><i class="fa fa-floppy-o"></i> Pregunta de seguridad</button>                                
        </div>
    </div>
    
</div>                    

<div class="row" id="perfil-datos">
    <div class="col-md-12">
        <div class="block">
            <div class="block-content user-profile">
                
                <div class="user-profile-block">                                        
                    <div class="user-profile-image">
                        <img src="{$perfil['per_imagen']}" class="img-circle img-thumbnail" width="100" height="100"/>
                    </div>                                        
                </div>
                <div class="user-profile-info">
                    <div class="user-profile-title">{$usr_nombre}</div>
                    <div class="user-profile-text">{$perfil.per_descripcion}</div>
                    <div class="user-profile-text text-success"><span class="fa fa-user"></span> {Application::getUserRole($usr_codigo)}</div>
                </div>
                <div class="user-profile-block user-profile-address pull-right">
                    <p><strong>Personales</strong></p>
                    <p><span class="fa fa-calendar"></span> {$perfil.per_fecha_nacimiento}</p>
                    <p><span class="fa fa-envelope"></span> {$usr_email}</p>
                </div>
                <div class="user-profile-block user-profile-address pull-right" style="margin-right: 10px;">
                    <p><strong>Redes sociales</strong></p>
                    <p><span class="fa fa-facebook"></span> {$perfil.per_facebook}</p>
                    <p><span class="fa fa-twitter"></span> {$perfil.per_twitter}</p>
                    <p><span class="fa fa-google-plus"></span> {$perfil.per_google_plus}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="perfil-edicion">
    <div class="col-md-12">
        <div class="block">
            <div class="block-content user-profile">
                
                <div class="user-profile-block">                                        
                    <div class="user-profile-image">
                        <img src="{$perfil['per_imagen']}" data-toggle="modal" data-target="#select-profile-picture" class="img-circle img-thumbnail" width="100" height="100" id="imagen-perfil-edicion"/>
                    </div>                                        
                </div>
                <div class="user-profile-info">
                    <div class="user-profile-title"><a href="#" id="usr_nombre" data-type="text" data-pk="1" data-placement="right" data-placeholder="Obligatorio" data-title="Ingresa tu nombre">{$usr_nombre}</a></div>
                    <div class="user-profile-text"><a href="#" id="per_descripcion" data-type="textarea" data-pk="1" data-placement="right" data-placeholder="Opcional" data-title="Ingresa tu nombre">{$perfil.per_descripcion}</a></div>
                    <div class="user-profile-text text-success"><span class="fa fa-user"></span> {Application::getUserRole($usr_codigo)}</div>
                </div>
                <div class="user-profile-block user-profile-address pull-right">
                    <p><strong>Personales</strong></p>
                    <p><span class="fa fa-calendar"></span> <a href="#" id="per_fecha_nacimiento" data-type="combodate" data-pk="1" data-placement="left" data-placeholder="Opcional" data-title="Ingresa tu fecha de nacimiento">{$perfil.per_fecha_nacimiento}</a></p>
                    <p><span class="fa fa-envelope"></span> <a href="#" id="usr_email" data-type="email" data-pk="1" data-placement="left" data-placeholder="Opcional" data-title="Ingresa tu direcci&oacute;n de email">{$usr_email}</a></p>
                </div>
                <div class="user-profile-block user-profile-address pull-right" style="margin-right: 10px;">
                    <p><strong>Redes sociales</strong></p>
                    <p><span class="fa fa-facebook"></span> <a href="#" id="per_facebook" data-type="text" data-pk="1" data-placement="left" data-placeholder="Opcional" data-title="Ingresa tu ID de Facebook">{$perfil.per_facebook}</a></p>
                    <p><span class="fa fa-twitter"></span> <a href="#" id="per_twitter" data-type="text" data-pk="1" data-placement="left" data-placeholder="Opcional" data-title="Ingresa tu ID de Twitter">{$perfil.per_twitter}</a></p>
                    <p><span class="fa fa-google-plus"></span> <a href="#" id="per_google_plus" data-type="text" data-pk="1" data-placement="left" data-placeholder="Opcional" data-title="Ingresa tu ID de Google+">{$perfil.per_google_plus}</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
{assign var="lastDate" value=""} 
{if $timeline}
<div class="row" id="perfil-timeline">
    <div class="col-md-12">
        
        
        <div class="timeline">
        	{foreach $timeline as $evento}
        	{if $evento.mesdia != $lastDate}
            <div class="timeline-event">
                <div class="timeline-date">
                    <div><span>{Application::$page->getDay($evento.fechahora)}</span> {Application::$page->getMonth($evento.fechahora)}</div>
                </div>
            </div>
            {assign var="lastDate" value=$evento.mesdia}
            {/if}
            <div class="timeline-event">
                <div class="timeline-event-icon"><i class="fa fa-{$evento.tipo}"></i></div>
                <div class="timeline-event-content">
                    <div class="event-title">{$evento.accion}</div>
                    <a>{$evento.titulo}</a>
                    <p>{$evento.resumen}</p>
                    <div class="event-date"><i class="fa fa-clock-o"></i> {Application::$page->getTime($evento.fechahora)}</div>                                        
                </div>
            </div>
            {/foreach}
        </div>
    </div>
</div>
{/if}

<div class="modal fade" id="select-profile-picture" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Seleccionar imagen de perfil</h4>
            </div>
            <div class="modal-body">
                <div class="img-circle img-thumbnail pull-left" style="width:100px;height:100px;background-color:#c0c0c0">&nbsp;</div>
                <div class="pull-right" style="width: 250px; height: 100px;">
                	<button style="width:100%" class="btn btn-primary use-profile-image" network="facebook">Usar Imagen de Perfil de Facebook</button>
                	<button style="display: none;width:100%" class="btn btn-primary use-profile-image" network="twitter">Usar Imagen de Perfil de Twitter</button>
                	<button style="width:100%" class="btn btn-primary upload-profile-image">Subir Imagen Perfil</button>
                	<button style="width:100%" class="btn btn-primary use-profile-image" network="none">Quitar Imagen Perfil</button>
					<form id="upload-form" method="post" enctype="multipart/form-data" action="{Application::getLink('admin/ajax/perfiles')}">
					<input style="display:none" type="file" name="upload-image" id="upload-image"/>
					<input type="hidden" name="method" value="uploadPicture" />
					<input type="hidden" name="type" value="json_text" />
					</form>
                </div>
                <div style="clear:both;"></div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="close-profile-picture">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="change-password" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Cambiar password</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Password Actual</label>
					<input type="password" class="form-control" id="current_password" name="current_password" value="" />
                </div>
                <div class="form-group">
                    <label>Password </label>
                    <div class="input-group">
					    <input type="password" class="form-control" id="password" name="password" value="" />
						<span id="password-meter" class="label-danger input-group-addon">Debil</span>
					</div>                  
                </div>
                <div class="form-group">
                    <label>Repetir Password </label>
					<input type="password" class="form-control" id="password2" name="password2" value="" />
                </div>
                <div style="clear:both;"></div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="close-change-password">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="validarPassword()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="security-question" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Pregunta de seguridad</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Ingresa tu contrase&ntilde;a</label>
					<input type="password" class="form-control" id="sq_password" name="sq_password" value="" />
                </div>
                <div class="form-group">
                    <label>Pregunta de seguridad preferida</label>
                    <select name="question" id="question"  class="form-control">
                    <option value="">Selecciona tu pregunta de seguridad</option>
                    {foreach $preguntas as $pregunta}
                    <option value="{$pregunta.pre_codigo}"{if $pregunta.pre_codigo == $perfil.pre_codigo} selected="selected"{/if}>{$pregunta.pre_texto}</option>
                    {/foreach}
                    </select>
                </div>
                <div class="form-group">
                    <label>Ingresa tu respuesta</label>
					<input type="text" class="form-control" id="answer" name="answer" value="{$perfil.per_respuesta}" />
                </div>
                <div style="clear:both;"></div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="close-security-question">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarPreguntaSeguridad()">Guardar</button>
            </div>
        </div>
    </div>
</div>
