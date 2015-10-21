<?php
date_default_timezone_set("America/Argentina/Buenos_Aires");
$CONFIG=array(
	"DB_NAME"=>"",
	"DB_USER"=>"root",
	"DB_PASS"=>"",
	"DB_HOST"=>"localhost",
	"DB_DRIVER"=>"MySQLi",

	"BACKEND_USER_COOKIE"=>"back_user",
	"USER_COOKIE"=>"front_user",
		
	"FRONTEND_SESSION_VAR"=>"frontend",
	"BACKEND_SESSION_VAR"=>"usuario",
		
	"MOD_REWRITE"=>true,
	"SYSTEM_TITLE"=>"",
	"SYSTEM_DESCRIPTION"=>"",
	"BASE_DIR"=>"",
	"DEFAULT_THEME"=>"gemini",
	"TEMPLATE_DIR"=>"/templates",
    "TEMPLATE_C_DIR"=>"/templates_c",
		
	"SMTP_HOST"=>"",
	"SMTP_PORT"=>"25",
	"SMTP_USER"=>"",
	"SMTP_PASS"=>"",
		
	"SMTP_FROM_NAME"=>"",
	"SMTP_FROM_MAIL"=>"",

	"ADMIN_EMAIL"=>"",
	"EMAIL"=>"",
	"DB_VERSION"=>1.0,
	"DB_FORCE_REBUILD"=>false,
	"BYPASS_CAPTCHAS"=>false,
	"NOTIFICATIONS_METHOD"=>"pull",
		
	"COPYRIGHTER" => "Daniel Marjos IT Consulting",
		
	"EXTERNAL_RESOURCES"=>true,
		
	"FACEBOOK_APP_ID"=>"",
	"FACEBOOK_APP_SECRET"=>"",

	"TWITTER_APP_ID"=>"",
	"TWITTER_APP_SECRET"=>""
);

