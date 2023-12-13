function postArticleToWallabag(id) {
    try {
		Notify.progress("Saving to Wallabag …", true);
		xhr.json("backend.php",	
				{
					'op': 'PluginHandler',
					'plugin': 'wallabag_v2',
					'method': 'getwallabagInfo',
					'id': encodeURIComponent(id)
				},
				(reply) => {
					if (reply.status) {
						if (reply.status=="200") {
							Notify.info("Saved to Wallabag: <em>" + reply.title + "</em>");
						} else {
							Notify.error("<strong>Error saving to Wallabag!</strong>: ("+reply.status+": "+reply.error+") "+reply.error_msg+"");
						}
					}  else {
						Notify.error("The Wallabag_v2 plugin needs to be configured. See the README for help", true);
					}
				});
    } catch (e) {
	Notify.error("wallabagArticle", e);
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

