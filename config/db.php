<?php
Application::Uses('sys.tools.passwordUtils');
$database=array(
	"tbl_usuario"=>array(
		"fields"=>array(
			"usr_codigo"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT"),		
			"usr_parent"=>array("type"=>"int","length"=>"11","extra"=>"DEFAULT NULL"),		
			"usr_codrol"=>array("type"=>"varchar","length"=>"254","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '0'"),		
			"usr_estado"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'"),		
			"usr_user"=>array("type"=>"varchar","length"=>"64","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_pass"=>array("type"=>"varchar","length"=>"40","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_nombre"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_apellido"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_pais"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_ciudad"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_direccion"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_telefono"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_email"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_empresa"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_hash"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '' "),		
			"usr_registrado"=>array("type"=>"datetime","length"=>"","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'"),		
			"usr_uvisita"=>array("type"=>"datetime","length"=>"","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'"),		
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"usr_codigo"),
			array("key_name"=>"buscador_usuario","primary"=>false,"fields"=>"usr_user,usr_nombre,usr_apellido","key_type"=>"FULLTEXT"),
		),
		"initial_records"=>array(
			array(
				"usr_codigo"=>1,
				"usr_parent"=>0,
				"usr_codrol"=>'1', 
				"usr_estado"=>1,
				"usr_user"=>"root",
				"usr_pass"=>passwordUtils::createHash('admin'),
				"usr_nombre"=>"Super Usuario",
				"usr_email"=>Application::Get('ADMIN_EMAIL'),
				"usr_registrado"=>date("Y-m-d H:i:s",time())			
			)
		)
	),
	"tbl_usuario_role"=>array(
		"fields"=>array(
			"rol_codigo"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT"),		
			"rol_front"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'"),		
			"rol_nombre"=>array("type"=>"varchar","length"=>"254","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '0'"),		
			"rol_rules"=>array("type"=>"text","length"=>"","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL")		
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"rol_codigo"),
		),
		"initial_records"=>array(
			array(
				"rol_codigo"=>1,
				"rol_nombre"=>'SUPER USUARIO',
				"rol_rules"=>'a:5:{s:7:"backend";i:1;s:14:"administracion";i:1;s:5:"roles";i:15;s:6:"reglas";i:1;s:8:"usuarios";i:15;}' 
			),
			array(
				"rol_codigo"=>2,
				"rol_nombre"=>'Administrador',
				"rol_rules"=>'a:6:{s:7:"backend";i:1;s:14:"administracion";i:1;s:8:"usuarios";i:15;s:7:"modulos";i:1;s:8:"frontend";i:15;s:13:"reclamaciones";i:13;}' 
			),
			array(
				"rol_codigo"=>3,
				"rol_nombre"=>'Usuario',
				"rol_rules"=>'a:1:{s:8:"frontend";i:1;}' 
			)
		)
	),
	"tbl_usuario_rule"=>array(
		"fields"=>array(
			"rul_codigo"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT"),		
			"rul_codrul"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'"),		
			"rul_rules"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'"),
			"rul_nombre"=>array("type"=>"varchar","length"=>"25","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT ''"),
			"rul_descripcion"=>array("type"=>"varchar","length"=>"255","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT ''")		
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"rul_codigo"),
		),
		"initial_records"=>array(
			array(
				"rul_codigo"=>1,
				"rul_codrul"=>0,
				"rul_rules"=>1,
				"rul_nombre"=>"backend",
				"rul_descripcion"=>"Herramientas Administrativas"	 
			),
			array(
				"rul_codigo"=>2,
				"rul_codrul"=>1,
				"rul_rules"=>1,
				"rul_nombre"=>administracion,
				"rul_descripcion"=>"Menu Administracion"	 
			),
			array(
				"rul_codigo"=>3,
				"rul_codrul"=>2,
				"rul_rules"=>15,
				"rul_nombre"=>"usuarios",
				"rul_descripcion"=>"Usuarios"	 
			),
			array(
				"rul_codigo"=>4,
				"rul_codrul"=>2,
				"rul_rules"=>15,
				"rul_nombre"=>"roles",
				"rul_descripcion"=>"Roles y Reglas"	 
			),
			array(
				"rul_codigo"=>5,
				"rul_codrul"=>4,
				"rul_rules"=>1,
				"rul_nombre"=>"reglas",
				"rul_descripcion"=>"Reglas"	 
			),
			array(
				"rul_codigo"=>6,
				"rul_codrul"=>1,
				"rul_rules"=>1,
				"rul_nombre"=>"modulos",
				"rul_descripcion"=>"Modulos"	 
			),
			array(
				"rul_codigo"=>7,
				"rul_codrul"=>6,
				"rul_rules"=>15,
				"rul_nombre"=>"frontend",
				"rul_descripcion"=>"FrontEnd"	 
			),
		)
	),
	"tbl_email_template"=>array(
		"fields"=>array(
			"tmp_codigo"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT"),
			"tmp_titulo"=>array("type"=>"varchar","length"=>"64","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '0'"),
			"tmp_nombre"=>array("type"=>"varchar","length"=>"32","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL DEFAULT '0'"),
			"tmp_contenido"=>array("type"=>"text","length"=>"","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL")
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"tmp_codigo"),
		),
		"initial_records"=>array(
			array(
				"tmp_titulo"=>"Tu cuenta de usuario ha sido creada!",
				"tmp_nombre"=>"validar_email",
				"tmp_contenido"=>"Bienvenido a [nombre_sitio].\n\nAntes que puedas utilizar tu nueva cuenta,  es necesario que valides tu direccion de email. Para esto, deberas hacer click en el link que figura a continuacion:\n\n<a href=\"[link]\">[link]</a>\n\nEn caso de que tu cliente de correo no muestre un link, por favor copia y pegue la direccion siguiente en tu navegador:\n\n[link]"
			),
			array(
				"tmp_titulo"=>'Solicitud de eliminacion de cuenta',
				"tmp_nombre"=>'borrar_cuenta',
				"tmp_contenido"=>"Estimado [nombre_usuario]\n\nHemos recibido una solicitud para eliminar tu cuenta de usuario de usuario. Si has sido tu quien envio esta solicitud, por favor, ingresa a tu cuenta mediante el link que figura abajo, e ingresa el codigo provisto cuando el sistema te lo solicite.\n\n<a href=\"[link_remocion]\">Haz click aqui</a>\n\nSi tu cliente de correo no te muestra el link, copia y pega lo siguiente en tu navegador:\n\n[link_remocion]"
			),
			array(
				"tmp_titulo"=>'Solicitud nuevo password', 
				"tmp_nombre"=>'recuperar_password',
				"tmp_contenido"=>"<h3>Hola [nombre_usuario]</h3>\n\n<p>Hemos recibido una solicitud de generaci�n de contrase�a. Si has sido t�, haz click en el link que figura abajo. Si no has sido t�, no debes realizar ninguna acci�n.</p>\n\n<p>Muchas gracias!</p>\n\n<a href=\"[link]\">Recuperar Password</a>\n\n<p>Si tu cliente de correo no muestra el enlace, por favor copia y pega el sguiente link en tu navegador:\n\n[link]</p>"
			),
		)
	),
	"tbl_perfil"=>array(
		"fields"=>array(
			"per_codigo"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT"),
			"usr_codigo"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'"),
			"per_contenido"=>array("type"=>"text","length"=>"","extra"=>"CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL")
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"per_codigo"),
		)
	),
	"tbl_galeria"=>array(
		"fields"=>array(
			"gal_codigo"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL AUTO_INCREMENT",),
			"gal_grupo"=>array("type"=>"varchar","length"=>"64","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_sesion"=>array("type"=>"varchar","length"=>"128","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_fecha"=>array("type"=>"datetime","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'",),
			"gal_mime"=>array("type"=>"varchar","length"=>"64","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_temp_id"=>array("type"=>"varchar","length"=>"128","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_relacionado"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT 0",),
			"gal_tipo"=>array("type"=>"enum","length"=>"'picture','youtube'","extra"=>"NOT NULL DEFAULT 'picture'",),
			"gal_archivo"=>array("type"=>"varchar","length"=>"128","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_titulo"=>array("type"=>"varchar","length"=>"128","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
			"gal_indice"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT 0"),
			"gal_descripcion"=>array("type"=>"text","length"=>"","extra"=>"COLLATE latin1_spanish_ci NOT NULL",),
		),
		"keys"=>array(
			array("key_name"=>"","primary"=>true,"fields"=>"gal_codigo",),
		),
		"initial_records"=>array(
		),
	),
);
