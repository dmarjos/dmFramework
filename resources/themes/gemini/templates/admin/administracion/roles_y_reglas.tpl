<form id="TheForm" method="post" action="">
	<div class="page-toolbar">
	    
	    <div class="page-toolbar-block">
	        <div class="page-toolbar-title">Roles, reglas y acciones</div>
	        <div class="page-toolbar-subtitle">Control de ACL (Access Control List) del sistema</div>
	    </div>                                                
	    
	    <ul class="breadcrumb">
	        <li><a href="#">Inicio</a></li>
	        <li><a href="#">Administraci&oacute;n</a></li>
	        <li class="active">Roles y reglas</li>
	    </ul>                 
	    
	</div>                    
	
	<div class="row">            
	    <div class="col-md-6">
		    <div class="block">
		        <div class="block-content">
		            <h2><strong>Arbol de reglas</strong></h2>
		            <p>Reglas definidas en el sistema.</p>
		        </div>
		        <div class="block-content">
				    <table cellpadding="0" cellspacing="0" class="grid" style="width:auto;background:none;border-top:1px solid #aaa;border-bottom:1px solid #ddd" id="rule_table">
				    <tr>
				    <th style="vertical-align:bottom;text-align:left;padding:3px;width:300px" class="one">Reglas</th>
				    <th width="16px" style="text-align:center;padding:3px 0px"><img src="{Application::GetPath('resources/img/admin/rule-view.gif')}" height="73px" /></th>
				    <th width="16px" style="text-align:center;padding:3px 0px"><img src="{Application::GetPath('resources/img/admin/rule-insert.gif')}" height="73px" /></th>
				    <th width="16px" style="text-align:center;padding:3px 0px"><img src="{Application::GetPath('resources/img/admin/rule-update.gif')}" height="73px" /></th>
				    <th width="16px" style="text-align:center;padding:3px 0px"><img src="{Application::GetPath('resources/img/admin/rule-delete.gif')}" height="73px" /></th>
				    {if (Application::$page->meetRules('reglas',UserRules::VIEW))}<th style="vertical-align:bottom;text-align:left;padding:3px" class="fix">Clave</th>{/if}
				    </tr>
				    {foreach $rolvalues as $rowid=>$row}
				    <tr id="{$row["trid"]}" {if $row["classes"]|@count gt 0}class="{implode(" ",$row["classes"])}"{/if}>{foreach $row["children"] as $cell}{$cell}{/foreach}</tr>
				    {/foreach}
				    </table>
		        </div>
		    </div>
		</div>
	    <div class="col-md-6">
		    <div class="block">
		        <div class="block-content">
		            <div class="pull-left">
			            <h2><strong>Roles de usuario</strong></h2>
			            <p>Roles para ACL.</p>
		            </div>
		            <div class="pull-right">
		                <div class="widget-info widget-from">
		                    <button id="add_btn" class="btn btn-primary"><i class="fa"></i> Agregar</button>                            
		                    <button id="upd_btn" class="btn btn-primary"><i class="fa"></i> Modificar</button>                            
		                    <button id="del_btn" class="btn btn-primary"><i class="fa"></i> Eliminar</button>
		                </div>
		            </div>
		        </div>
		        <div class="block-content">
		        <table width="100%" boder="0" cellpadding="0" cellspacing="0">
		        	<tr>
		        		<td width="120px">Seleccione un Rol:</td>
		        		<td>Nombre</td>
		        	</tr>
		        	<tr>
		        		<td><select id="select" onchange="document.location='{Application::getLink(Application::get('SELF'))}?id2={$rul['rul_codigo']}&id='+this.value">
		        		<option value="0">----- Nuevo Rol -----</option>
		        		{foreach $roles as $option}
		        		<option value="{$option["value"]}"{if $option["selected"]=="1"} selected="selected"{/if}>{$option["text"]}</option>
		        		{/foreach}
		        		</select>
		        		</td>
		        		<td><input type="text" class="text" name="nombre" id="nombre" value="{Application::GetString($rol['rol_nombre'])}" style="width:300px" /></td>
		        	</tr>
		        </table>
		        </div>
		    </div>
		    {if (Application::$page->meetRules('reglas',UserRules::VIEW))}
		    <div class="block">
		        <div class="block-content">
		            <div class="pull-left">
			            <h2><strong>Reglas y Acciones</strong></h2>
			            <p>Reglas para ACL.</p>
		            </div>
		            <div class="pull-right">
		                <div class="widget-info widget-from">
		                    <button id="add_btn2" class="btn btn-primary"><i class="fa"></i> Agregar</button>                            
		                    <button id="upd_btn2" class="btn btn-primary"><i class="fa"></i> Modificar</button>                            
		                </div>
		            </div>
		        </div>
		        <div class="block-content">
			        <table width="100%" boder="0" cellpadding="0" cellspacing="0">
			        	<tr>
			        		<td width="50%">Seleccione una regla:</td>
			        		<td width="50%">Contenida en:</td>
			        	</tr>
			        	<tr>
			        		<td width="50%"><select id="select2" onchange="document.location='{Application::getLink(Application::get('SELF'))}?id={$rol['rol_codigo']}&id2='+this.value">
			        		<option value="0">----- Nueva Regla -----</option>
			        		{foreach $rules as $option}
			        		<option value="{$option["value"]}"{if $option["selected"]=="1"} selected="selected"{/if}>{$option["text"]}</option>
		    	    		{/foreach}
			        		</select></td>
			        		<td width="50%"><select id="parent" name="parent">
			        		<option value="0">----- Nodo Raiz -----</option>
			        		{foreach $parentRules as $option}
			        		<option value="{$option["value"]}"{if $option["selected"]=="1"} selected="selected"{/if}>{$option["text"]}</option>
		    	    		{/foreach}
							</select></td>
			        	</tr>
			        </table>
			        <table width="100%" boder="0" cellpadding="0" cellspacing="0">
			        	<tr>
			        		<td width="70%">Nombre:</td>
			        		<td width="30%">Clave:</td>
			        	</tr>
			        	<tr>
			        		<td><input type="text" class="text" name="nombre2" id="nombre2" value="{Application::GetString($rul['rul_descripcion'])}" style="width:100%" /></td>
			        		<td><input type="text" class="text{if intval($rul['rul_codigo']) != 0} disabled{/if}" name="clave" id="clave" value="{Application::GetString($rul['rul_nombre'])}" style="width:100px"{if intval($rul['rul_codigo']) != 0} readonly="readonly"{/if} /></td>
			        	</tr>
			        </table>
		        </div>
		        <div class="block-content">
		            <h3>Acciones</h3>
				    <table cellpadding="0" cellspacing="0" class="grid" style="width:100%;background:none" id="rule_table">
				    <tr>
				      <th style="text-align:center;border-left:1px solid #bbb">&nbsp;</th>
				      <th style="text-align:center">Activa</th>
				      <th style="text-align:center">Agregar</th>
				      <th style="text-align:center">Editar</th>
				      <th style="text-align:center">Eliminar</th>
				    </tr>
				    <tr id="actions">
				      <td style="border-left:1px solid #ddd">Soportadas</td>
				      <td style="text-align:center"><input type="checkbox" style="float:none" checked="checked" disabled="disabled" /></td>
				      <td style="text-align:center"><input type="checkbox" name="accion[insert]" value="1" style="float:none"{if ($rul['rul_rules'] & UserRules::INSERT) == UserRules::INSERT} checked="checked"{/if} onclick="cbClicked()" /></td>
				      <td style="text-align:center"><input type="checkbox" name="accion[update]" value="1" style="float:none"{if ($rul['rul_rules'] & UserRules::UPDATE) == UserRules::UPDATE} checked="checked"{/if} onclick="cbClicked()" /></td>
				      <td style="text-align:center"><input type="checkbox" name="accion[delete]" value="1" style="float:none"{if ($rul['rul_rules'] & UserRules::DELETE) == UserRules::DELETE} checked="checked"{/if} onclick="cbClicked()" /></td>
				    </tr>{if $rul['rul_codigo'] == 0}
				    <tr class="even" id="defaults">
				      <td style="border-left:1px solid #ddd">Default</td>
				      <td style="text-align:center"><input type="checkbox" name="default[view]" value="1" style="float:none" onclick="cbClicked()" /></td>
				      <td style="text-align:center"><input type="checkbox" name="default[insert]" value="1" style="float:none" disabled="disabled" /></td>
				      <td style="text-align:center"><input type="checkbox" name="default[update]" value="1" style="float:none" disabled="disabled" /></td>
				      <td style="text-align:center"><input type="checkbox" name="default[delete]" value="1" style="float:none" disabled="disabled" /></td>
				    </tr>
				    {/if}
				    </table>
		        </div>
		    </div>
		    {/if}
		</div>
	</div>
<input type="hidden" name="values" id="values" value="" />
<input type="hidden" name="submitted" id="submitted" value="0" />
</form>

<script type="text/javascript">
{if !empty($rule)}
var rules={json_encode($rule)};
{else}
var rules={};
{/if}

setMode('upd', {if intval($rol['rol_codigo']) != 0}true{else}false{/if});
setMode('add', {if intval($rol['rol_codigo']) == 0}true{else}false{/if});
setMode('del', {if intval($rol['rol_codigo']) != 0}true{else}false{/if});

setMode2('upd', {if intval($rul['rul_codigo']) != 0}true{else}false{/if});
setMode2('add', {if intval($rul['rul_codigo']) == 0}true{else}false{/if});
setMode2('del', {if intval($rul['rul_codigo']) != 0}true{else}false{/if});

function doNombreChange(e) {
	setMode('add', e.target.value != nombre || {if intval($rol['rol_codigo']) == 0}true{else}false{/if});
}

function doDelRul2(rul) {
	if (confirm("¿Esta seguro que desea eliminar la regla seleccionada?\n\nIMPORTANTE: si elimina una regla la misma sera quitada\nautomagicamente de todos los roles de usuario existentes\n\nADVERTENCIA: si elimina una regla compruebe que no ha sido\nutilizada en el codigo fuente, de lo contrario parte del codigo\npodria quedar inaccesible")) {
		$('#submitted').val('6');
	    $('#TheForm').action = '{Application::GetLink(Application::get('SELF'))}?id={intval($_GET['id'])}&id2='+rul;
	    doSubmit();
	}
}

</script>