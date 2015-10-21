var Application={
	getLink: function(url) {
		if (url.indexOf('://')!=-1)
			return url;
		var theURL=MAIN_URL+url;
		theURL=theURL.replace("//","/");
		return theURL;
	},
	Redirect: function(url) {
		location.href=this.getLink(url);
	},
	callAdminAjax: function (module, postData, dataType, successCallback, errorCallback) {
		var ajaxObj={
			url:this.getLink('admin/ajax/'+module),
			type:'post',
			data: postData,
			dataType:dataType || 'text',
			success:function(data,event) {
				if (successCallback)
					successCallback(data,event);
			},
			error:function(data,event) {
				if (errorCallback)
					errorCallback(data,event);
			}
		};
		$.ajax(ajaxObj);
	},
	callAjax: function (module, postData, dataType, successCallback, errorCallback) {
		var ajaxObj={
			url:this.getLink('ajax/'+module),
			type:'post',
			data: postData,
			dataType:dataType || 'text',
			success:function(data,event) {
				if (successCallback)
					successCallback(data,event);
			},
			error:function(data,event) {
				if (errorCallback)
					errorCallback(data,event);
			}
		};
		$.ajax(ajaxObj);
	},
	callAjaxAndWait: function (module, postData, dataType, successCallback, errorCallback) {
		var ajaxObj={
			url:this.getLink('ajax/'+module),
			type:'post',
			async: false,
			data: postData,
			dataType:dataType || 'text',
			success:function(data,event) {
				if (successCallback)
					successCallback(data,event);
			},
			error:function(data,event) {
				if (errorCallback)
					errorCallback(data,event);
			}
		};
		$.ajax(ajaxObj);
	}
};

