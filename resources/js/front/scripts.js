function isEmail(email) {
	if (email.indexOf('@')==-1)
		return false;
	
	var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!filter.test(email))
		return false;

	return true;
};

function showError(errorMessage) {
	alert(errorMessage);
}

var severityLevel= {
	DANGER: 1,
	INFO: 2,
	WARNING: 3,
	READY: 4,
	CONFIRM: 5
};

function showInfo(mensaje,params) {
	var opciones={
		level: severityLevel.READY,
		buttons: [],
		title:false,
		timeout: 10,
		notimeout: false
	};

	for (var param in opciones)  {
		if (param) {
			if (typeof params[param] == "undefined") {
				params[param] = opciones[param];
			}
		}
	}
	
	var className='';
	var titulo='';
	switch (params.level) {
		case severityLevel.DANGER:
			className="danger";
			titulo='Peligro!';
			break;
		case severityLevel.INFO:
			className="info";
			titulo='Informaci&oacute;n';
			break;
		case severityLevel.WARNING:
			className="warning";
			titulo='Cuidado!';
			break;
		case severityLevel.READY:
			className="success";
			titulo='Listo!';
			break;
		case severityLevel.CONFIRM:
			className="warning";
			titulo='';
			break;
	}
	if (params.title) titulo=params.title;
	
	var html='<div class="alert alert-'+className+' alert-dismissible" role="alert">';
	html+='<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	if (titulo!='') 
		html+='<strong>'+titulo+':</strong> ';
	html+=mensaje;
	if (params.buttons.length>0) {
		for(var b=0; b<params.buttons.length; b++) {
			var button=params.buttons[b];
			if (button.text && (button.action || button.href)) {
				var boton='<a ';
				if (button.action)
					boton+=' onclick="'+button.action+'"';
				if (button.href)
					boton+=' href="'+button.href+'"';
				var buttonColor;
				if (!button.color)
					buttonColor='gris';
				else
					buttonColor=button.color;
				boton+=' class="btn btn-small btn-'+buttonColor+'">'+button.text+'</a>';
				html+=boton;
			}
		}
	}
	html+='</div>';
	$('header').after(html);
	if (params.notimeout) return;
	if (params.timeout>0) {
		setTimeout(closeLastInfo,params.timeout*1000);
	}
}

function closeLastInfo() {
	$('.alert.alert-dismissible').fadeOut(400,function() {
		$('.alert.alert-dismissible .close').click();
	}); 
}

function askConfirmation(message,confirmedCallBack,deniedCallBack) {
	showInfo(message,{
		level: severityLevel.CONFIRM,
		buttons: [
	  		{text: 'Si',color:'naranja',href:'#'},
			{text: 'No',color:'gris',href:'#'},
		],
		timeout: -1,
		notimeout: true
	});
	
	$('div.alert.alert-dismissible[role=alert] a.btn-naranja').click(function() {
		if (confirmedCallBack)
			confirmedCallBack();
		closeAlert();
	});
	$('div.alert.alert-dismissible[role=alert] a.btn-gris').click(function() {
		if (deniedCallBack)
			deniedCallBack();
		closeAlert();
	});
} 

function closeAlert() {
	$('div.alert.alert-dismissible[role=alert] button.close').click();
}

function zeroPad(num, places) {
	var zero = places - num.toString().length + 1;
	return Array(+(zero > 0 && zero)).join("0") + num;
}

function daysInMonth(month,year) {
    return new Date(year, month, 0).getDate();
}
