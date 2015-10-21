<?php
function smarty_function_html_recaptcha($params, $template) {
	Application::Uses("sys.tools.reCaptcha");
	return reCaptcha::recaptcha_get_html($params["public_key"]);
	
	$buttons=0;
	$wgParams=Application::getWidgetParameters($params["id"]);
	$scripts=Application::get("scripts");
	if (is_null($scripts)) $scripts=array();
	$output="";
	if (!in_array("/resources/js/admin/core.js",$scripts)) {
		Application::addScript('/resources/js/lib/core.js');
		$output.='<script type="text/javascript" src="'.Application::getPath("/resources/js/lib/core.js").'"></script>';
	}
	if (!in_array("/resources/js/plugins/datatables/jquery.dataTables.js",$scripts)) {
		Application::addScript('/resources/js/plugins/datatables/jquery.dataTables.js');
		$output.='<script type="text/javascript" src="'.Application::getPath("/resources/js/plugins/datatables/jquery.dataTables.js").'"></script>';
	}
	//Application::addScript('');
	$output.='<table id="'.$params["id"].'" data-url="'.'" cellpadding="0" cellspacing="0" width="100%" class="table table-bordered table-striped sortable">'."\n";
	$output.='<thead>'."\n";
	if (($wgParams["id_field"]&&($wgParams["form"] || $wgParams["custom_action"])) || ($wgParams["indexable"])) {
		if (($wgParams["id_field"]&&($wgParams["form"] || $wgParams["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::UPDATE)) $buttons++;
		if (($wgParams["id_field"]&&($wgParams["form"] || $wgParams["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::DELETE)) $buttons++;
		if ($wgParams["indexable"]) $buttons+=2;
		$width=(40*$buttons);
		$output.='<th width="'.$width.'px">&nbsp;</th>'."\n";
	}
	foreach($wgParams["columns"] as $column) { 
		$th='<th';
		if ($column["width"]) $th.=' width="'.$column["width"].'"';
		$th.=">".$column["header"].'</th>'."\n";
		$output.=$th;
	}
	$output.='</thead>'."\n";
	$output.='<tbody>'."\n";
	$output.='</tbody>'."\n";
	$output.='</table>'."\n";
	$output.='<script type="text/javascript">
	var totalRecords=0;
	$(document.body).ready(function() {
		$("#'.$params["id"].'").dataTable({
			"oLanguage":oLanguage,
			"bProcessing": true,
	        "bServerSide": true,
			"iDisplayLength": 5, 
			';
	if ($wgParams["noSort"])
			$output.='"bSort":false,';	
	$output.='"aLengthMenu": [5,10,20,50,100], 
			"sPaginationType": "full_numbers",
			"sAjaxSource": "'.$wgParams["url"].'",
	';
	
	$output.='
			"fnCreatedRow": function( nRow, aData, iDataIndex ) {
				var theRowHTML=$(nRow).html();
				var controlCell=\'\';
	';
	if (($wgParams["id_field"]&&($wgParams["form"] || $wgParams["custom_action"]))||$wgParams["indexable"]) {	
		$output.='
				controlCell=\'<td class="">';

		$doAction="doAction";
		if ($wgParams["custom_action"])
			$doAction=$wgParams["custom_action"];
		
		if (Application::$page->meetRules(Application::$page->rules,UserRules::UPDATE)) {
			$output.='<button title="Editar registro" class="grid-update glyphicon glyphicon-edit btn btn-primary btn-xs" data-action="upd" data-record-id="\'+aData[aData.length-1]+\'" onclick="'.$doAction.'(this)"></button>';
		}
		if (Application::$page->meetRules(Application::$page->rules,UserRules::DELETE)) {
			$output.='<button title="Eliminar registro" class="grid-delete glyphicon glyphicon-floppy-remove btn btn-danger btn-xs" data-action="del" data-record-id="\'+aData[aData.length-1]+\'" onclick="'.$doAction.'(this)"></button>';
		}
		if ($wgParams["indexable"]) {
			$output.='<button title="Subir" class="grid-update glyphicon glyphicon-arrow-up btn btn-primary btn-xs" data-action="iup" data-record-id="\'+aData[aData.length-1]+\'" onclick="'.$doAction.'(this)"></button>';
			$output.='<button title="Bajar" class="grid-update glyphicon glyphicon-arrow-down btn btn-primary btn-xs" data-action="idn" data-record-id="\'+aData[aData.length-1]+\'" onclick="'.$doAction.'(this)"></button>';
		}
		$output.='</td>\'';
	}
	if ($wgParams["id_field"]&&($wgParams["form"] || $wgParams["custom_action"])) {
	}
	$output.='
		theRowHTML=controlCell+theRowHTML;
		$(nRow).html(theRowHTML);
	';
	foreach($wgParams["columns"] as $idx=>$column) {
		if ($buttons==0)
			$baseColumn=$idx;
		else
			$baseColumn=$idx+1;
		if  ($column["formater"]) {
					if ($column["formater"]["class"]=="combo") {
	$output.='
				drawCombo($(\'td:eq('.($baseColumn).')\', nRow),'.json_encode($column).',aData['.$idx.'],aData[aData.length-1]);
	'."\n";
			}
			if ($column["formater"]["class"]=="custom") {
	$output.='
				'.$column["formater"]["function"].'($(\'td:eq('.($baseColumn).')\', nRow),'.json_encode($column).',aData['.$idx.'],aData[aData.length-1]);
	'."\n";
			}
		}
	}
	$output.='
		    },			
	';

	$doAction='
		var url="'.$wgParams["form"].'";
		if (url.indexOf("?")==-1)
			url+="?";
		else
			url+="&";
		url+="action=add";
		location.href=url;
	';
	if ($wgParams["custom_action"])
		$doAction=$wgParams["custom_action"]."(this)";
	
	$output.='
		    "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
			     oSettings.jqXHR =  $.ajax( {
			        "dataType": \'json\',
			        "type": "GET",
			        "url": sSource,
			        "data": aoData,
			        "success": function(data) { 
			        	totalRecords=data.iTotalRecords;
			        	fnCallback(data); 
			        	$(".page-content").mCustomScrollbar("update");
			        	';
						if (Application::$page->meetRules(Application::$page->rules,UserRules::INSERT)) {
							$output.='
						if ($(".block .datatable-header").length!=0) { 
				        	if ($(".block .datatable-header button").length==0) {
				        		$(".block .datatable-header h2").addClass("pull-left");
				        		$("<button/>").attr("data-action","add").addClass("btn btn-primary pull-right").css({
									"cursor": "pointer",
									"margin-right":"5px",
									"margin-top":"5px"						        			
				        		}).html("Agregar").click(function() { '.$doAction.' }).appendTo($(".block .datatable-header"));
							}
						}
						';
						}
					$output.='
					}
			      } );
		    },			
			"aoColumns": [
				{"bSortable": true },';
	$cols=array();
	for ($i=1; $i<count($wgParams["columns"]);$i++)
		$cols[]='			null';
	$output.=implode(",\n",$cols); 
	$output.='		]
		})
	});
	function doAction(obj) {
		var dataAction=$(obj).attr("data-action");
		var dataRecordId=$(obj).attr("data-record-id");
		var url="'.$wgParams["form"].'";
		if (url.indexOf("?")==-1)
			url+="?";
		else
			url+="&";
		url+="action="+dataAction+"&id="+dataRecordId;
		location.href=url;
	}
	
	</script>';
	return $output;
}