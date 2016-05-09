<?php
class Wallabag_v2 extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Post articles to a Wallabag v 2.x instance",
			"joshu@unfettered.net");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
	}

	function save() {
	    $wallabag_url = db_escape_string($_POST["wallabag_url"]);
	    $wallabag_username = db_escape_string($_POST["wallabag_username"]);
	    $wallabag_password = db_escape_string($_POST["wallabag_password"]);
	    $wallabag_client_id = db_escape_string($_POST["wallabag_client_id"]);
	    $wallabag_client_secret = db_escape_string($_POST["wallabag_client_secret"]);
	    $this->host->set($this, "wallabag_url", $wallabag_url);
	    $this->host->set($this, "wallabag_username", $wallabag_username);
	    $this->host->set($this, "wallabag_password", $wallabag_password);
	    $this->host->set($this, "wallabag_client_id", $wallabag_client_id);
	    $this->host->set($this, "wallabag_client_secret", $wallabag_client_secret);
	    echo "Ready to send to Wallabag at $wallabag_url";
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/wallabag_v2.js");
	}

	function hook_prefs_tab($args) {
		 if ($args != "prefPrefs") return;

		 print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Wallabag v2")."\">";

		 print "<br/>";

		 $w_url = $this->host->get($this, "wallabag_url");
		 $w_user = $this->host->get($this, "wallabag_username");
		 $w_pass = $this->host->get($this, "wallabag_password");
		 $w_cid = $this->host->get($this, "wallabag_client_id");
		 $w_csec = $this->host->get($this, "wallabag_client_secret");
		 print "<form dojoType=\"dijit.form.Form\">";

		 print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
	   evt.preventDefault();
           if (this.validate()) {
               console.log(dojo.objectToQuery(this.getValues()));
               new Ajax.Request('backend.php', {
                                    parameters: dojo.objectToQuery(this.getValues()),
                                    onComplete: function(transport) {
                                         notify_info(transport.responseText);
                                    }
                                });
           }
           </script>";

		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"wallabag_v2\">";
		print "<table width=\"100%\" class=\"prefPrefsList\">";
		print "<tr><td width=\"40%\">".__("Wallabag URL - Note: Do not add a trailing slash.")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"true\" name=\"wallabag_url\" regExp='^(http|https)://.*' value=\"$w_url\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Username")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_username\" regExp='\w{0,64}' value=\"$w_user\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Password")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_password\" regExp='.{0,64}' value=\"$w_pass\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Cient ID")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_client_id\" regExp='.{0,64}' value=\"$w_cid\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Cient Secret")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_client_secret\" regExp='.{0,64}' value=\"$w_csec\"></td></tr>";
		print "</table>";
		print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

		print "</form>";

		print "</div>"; #pane

	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<img id=\"wallabagImgId\" src=\"plugins.local/wallabag_v2/wallabag.png\"
			class='tagsPic' style=\"cursor : pointer\"
			onclick=\"postArticleToWallabag($article_id)\"
			title='".__('Wallabag v2')."'>";

		return $rv;
	}

	function getwallabagInfo() {
		$id = db_escape_string($_REQUEST['id']);

		$result = db_query("SELECT title, link
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

		if (db_num_rows($result) != 0) {
			$title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
				100, '...');
			$article_link = db_fetch_result($result, 0, 'link');
		}
		$wallabag_url = $this->host->get($this, "wallabag_url");
		$wallabag_username = $this->host->get($this, "wallabag_username");
		$wallabag_password = $this->host->get($this, "wallabag_password");
	        $wallabag_client_id = $this->host->get($this, "wallabag_client_id");
	        $wallabag_client_secret = $this->host->get($this, "wallabag_client_secret");

		include 'oauth.php';

		if (function_exists('curl_init')) {
	 		 $postfields = array(
			 	'access_token' => $wallabag_access_token,
				'url'          => $article_link
				);
			 $cURL = curl_init();
			 curl_setopt($cURL, CURLOPT_URL, $wallabag_api);
			 curl_setopt($cURL, CURLOPT_HEADER, 1);
			 curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
			 curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
			 curl_setopt($cURL, CURLOPT_TIMEOUT, 5);
			 curl_setopt($cURL, CURLOPT_POST, 4);
			 curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
			 $apicall = curl_exec($cURL);
			 $status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
			 curl_close($cURL);
		} else {
			 $status = 'For the plugin to work you need to <strong>enable PHP extension CURL</strong>!';
			}

		print json_encode(array("wallabag_url" => $wallabag_url,
					"wallabag_username" => $wallabag_username,
					"wallabag_password" => $wallabag_password,
					"wallabag_client_id" => $wallabag_client_id,
					"wallabag_client_secret" => $wallabag_client_secret,
					"title" => $title,
					"status" => $status));
	}

	function api_version() {
		return 2;
	}

}
?>
