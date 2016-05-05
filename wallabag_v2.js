function postArticleTowallabag(id) {
    var postApi = function(ti) {
	var xhr = new XMLHttpRequest();
	xhr.open('HEAD', ti.wallabag_url + '/api/entries.json', true);
	xhr.onreadystatechange = function() {
	    if (xhr.readyState == 4) {
		if (xhr.status == 200) {
		    new Ajax.Request(ti.wallabag_url + "api/entries.json" + "Authorization:Bearer" + ti.access_token + "url" + btoa(ti.link.strp()), {
			method: 'post',
			// here comes the CORS fix
			// courtesy of: http://stackoverflow.com/questions/13814739/prototype-ajax-request-being-sent-as-options-rather-than-get-results-in-501-err/15300045#15300045
			onCreate: function(response) { 
			    var t = response.transport; 
			    t.setRequestHeader = t.setRequestHeader.wrap(function(original, k, v) { 
				if (/^(accept|accept-language|content-language)$/i.test(k)) 
				    return original(k, v); 
				if (/^content-type$/i.test(k) && 
				    /^(application\/x-www-form-urlencoded|multipart\/form-data|text\/plain)(;.+)?$/i.test(v)) 
				    return original(k, v); 
				return; 
			    }); 
			},
			// END CORS FIX
			onSuccess: function (response) {
			    var rObj = JSON.parse(response.responseText);
			    if (rObj.status == 0) {
				notify_info("Shared to Wallabag" +
					    rObj.message, false);
			    } else {
				notify_error("Wallabag post failed" +
					     rObj.message, true);
			    }
			},
		    });
		}
	    }
	};
	xhr.send();
    };
    try {
	new Ajax.Request("backend.php",	{
	    parameters: {
		'op': 'pluginhandler',
		'plugin': 'wallabag_v2',
		'method': 'getwallabagInfo',
		'id': param_escape(id)
	    },
	    onComplete: function(transport) {
		var ti = JSON.parse(transport.responseText);
		
		if (ti.wallabag_url && ti.wallabag_url.length &&
		    ti.wallabag_username && ti.wallabag_username.length &&
		    ti.wallabag_password && ti.wallabag_password.length &&
		    ti.wallabag_client_id && ti.wallabag_client_id.length &&
		    ti.wallabag_client_secret && ti.wallabag.client.secret.length) {
		    postApi(ti);
		    }  else {
		    notify_error("Wallabag configuration is incomplete.", true);
		}
	    } });
    } catch (e) {
	exception_error("wallabagArticle", e);
    }
}
