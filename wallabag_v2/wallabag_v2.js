function postArticleToWallabag(id) {
    try {
		notify_progress("Saving to Wallabag …", true);
		new Ajax.Request("backend.php",	{
				parameters: {
					'op': 'pluginhandler',
					'plugin': 'wallabag_v2',
					'method': 'getwallabagInfo',
					'id': param_escape(id)
				},
				onSuccess: function(transport) {
					var ti = JSON.parse(transport.responseText);
						if (ti.status) {
								if (ti.status=="200") {
									notify_info("Saved to Wallabag: <em>" + ti.title + "</em>");
								} else {
									notify_error("<strong>Error saving to Wallabag!</strong>: ("+ti.status+": "+ti.error+") "+ti.error_msg+"");
								}
						}  else {
							notify_error("The Wallabag_v2 plugin needs to be configured. See the README for help", true);
						}
				}
		});
    } catch (e) {
		exception_error("wallabagArticle", e);
    }
}

hotkey_actions['send_to_wallabag'] = function() {
  if (getActiveArticleId()) {
    postArticleToWallabag(getActiveArticleId());
    return;
  }
};