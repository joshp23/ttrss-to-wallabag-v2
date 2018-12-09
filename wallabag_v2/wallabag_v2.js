function postArticleToWallabag(id) {
    try {
		Notify.progress("Saving to Wallabag …", true);
		new Ajax.Request("backend.php",	{
				parameters: {
					'op': 'pluginhandler',
					'plugin': 'wallabag_v2',
					'method': 'getwallabagInfo',
					'id': encodeURIComponent(id)
				},
				onSuccess: function(transport) {
					var ti = JSON.parse(transport.responseText);
						if (ti.status) {
								if (ti.status=="200") {
									Notify.info("Saved to Wallabag: <em>" + ti.title + "</em>");
								} else {
									Notify.error("<strong>Error saving to Wallabag!</strong>: ("+ti.status+": "+ti.error+") "+ti.error_msg+"");
								}
						}  else {
							Notify.error("The Wallabag_v2 plugin needs to be configured. See the README for help", true);
						}
				}
		});
    } catch (e) {
		exception_error("wallabagArticle", e);
    }
}

require(['dojo/_base/kernel', 'dojo/ready'], function (dojo, ready) {
	ready(function () {
		PluginHost.register(PluginHost.HOOK_INIT_COMPLETE, () => {
			App.hotkey_actions["send_to_wallabag"]  = function() {
			  if (Article.getActive()) {
				postArticleToWallabag(Article.getActive());
				return;
			  }
			};
		});
	});
});

