<?php
define( 'W_V2_DEBUG', false );
class Wallabag_v2 extends Plugin {
	private $host;

	function about() {
		return array("1.5.0",
			"Post articles to a Wallabag v 2.x instance",
			"joshu@unfettered.net");
	}

	function init($host) {
		$this->host = $host;
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
	}

	function save() {
	    $w_url = $_POST["wallabag_url"];
	    $w_user = $_POST["wallabag_username"];
	    $w_pass = $_POST["wallabag_password"];
	    $w_cid = $_POST["wallabag_client_id"];
	    $w_cs = $_POST["wallabag_client_secret"];

		if (function_exists('curl_init')) {
			$postfields = array(
				"client_id" 	=> $w_cid,
				"client_secret" => $w_cs,
				"username" 		=> $w_user,
				"password" 		=> $w_pass,
				"grant_type" 	=> "password"
			);
			$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $w_url . '/oauth/v2/token');
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
				curl_setopt($cURL, CURLOPT_POST, true);
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
			$result = curl_exec($cURL);
			$aTimeout =  time() + 3600;
			$rTimeout =  time() + 1209600;
			$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
				curl_close($cURL);
			$result = json_decode($result,true);

			$w_access = $result["access_token"];
			$w_refresh = $result["refresh_token"];
			$w_error = $result["error"];
			$w_error_msg = $result["error_description"];

			$this->host->set($this, "wallabag_access_token", $w_access);
			$this->host->set($this, "wallabag_access_token_timeout", $aTimeout);
			$this->host->set($this, "wallabag_refresh_token", $w_refresh);
			$this->host->set($this, "wallabag_refresh_token_timeout", $rTimeout);
		} else {
			$status = 501;
			$w_error = "PEBCAK";
	    	$w_error_msg = "Please <strong>enable PHP extension CURL</strong>!";
		}

	    $this->host->set($this, "wallabag_username", $w_user);
	    $this->host->set($this, "wallabag_password", $w_pass);
	    $this->host->set($this, "wallabag_url", $w_url);
	    $this->host->set($this, "wallabag_client_id", $w_cid);
	    $this->host->set($this, "wallabag_client_secret", $w_cs);

		if($status !== 200){ 
			if(W_V2_DEBUG) {
				$reult = array(
							"wallabag_url" 	=> $w_url,
							"client_id" 	=> $w_cid,
							"client_secret" => $w_cs,
						 	"error" 		=> $w_error,
							"error_msg" 	=> $w_error_msg,
							"refresh_token" => $w_refresh,
							"access_token" 	=> $w_access,
							"status" 		=> $status
							);
				$debug_result = json_encode($result);
				file_put_contents("plugins.local/wallabag_v2/debug.txt", date('Y-m-d H:i:s')."\r\n".$debug_result."\r\n", FILE_APPEND);
				print $debug_result;
			} else 
				print "Error Saving Prefs. Try again.";
		} else 
				print "Ready to send to Wallabag at $w_url";
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/wallabag_v2.js");
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

	    $w_user = $this->host->get($this, "wallabag_username");
	    $w_pass = $this->host->get($this, "wallabag_password");
		$w_url = $this->host->get($this, "wallabag_url");
		$w_cid = $this->host->get($this, "wallabag_client_id");
		$w_csec = $this->host->get($this, "wallabag_client_secret");
		$w_access = $this->host->get($this, "wallabag_access_token");

		 print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Wallabag v2")."\">";
		 print "<br/>";
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
		print "<tr><td width=\"40%\">".__("Wallabag URL (No trailing slash!)")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"true\" name=\"wallabag_url\" regExp='^(http|https)://.*' value=\"$w_url\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Username")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_username\" regExp='\w{0,64}' value=\"$w_user\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Password")."</td>";
		print "<td class=\"prefValue\"><input type=\"password\" dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_password\" regExp='.{0,64}' value=\"$w_pass\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Client ID")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_client_id\" regExp='.{0,64}' value=\"$w_cid\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Wallabag Client Secret")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" name=\"wallabag_client_secret\" regExp='.{0,64}' value=\"$w_csec\"></td></tr>";
		print "</table>";
		print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>"; 
		if($w_access == null || $w_access == false|| $w_access == "") {
			print "<strong style=\"color:red;\">  Alert</strong>: No OAuth tokens in Database! Submit Username and Password to retrieve new tokens.";
		} else {
			print "  Submit this form without a Username and Password to reset the Wallabag OAuth Tokens to a null value.";
		}
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
		$id = $_REQUEST['id'];
		$sth = $this->pdo->prepare("SELECT title, link 
									FROM ttrss_entries, ttrss_user_entries 
									WHERE id = ? AND ref_id = id  AND owner_uid = ?");
		$sth->execute([$id, $_SESSION['uid']]);
		if ($row = $sth->fetch()) {
			$title = truncate_string(strip_tags($row['title']), 100, '...');
			$article_link = $row['link'];
		}

		$w_url = $this->host->get($this, "wallabag_url");
		$w_cid = $this->host->get($this, "wallabag_client_id");
		$w_cs = $this->host->get($this, "wallabag_client_secret");

		if (function_exists('curl_init')) {
			$w_access = $this->host->get($this, "wallabag_access_token");
			$old_timeout = $this->host->get($this, "wallabag_access_token_timeout");
			$now = time();
			if(W_V2_DEBUG) $token_type = "old";
			if( $old_timeout < $now ) {
				if(W_V2_DEBUG) $token_type = "refreshed";
				$w_refresh = $this->host->get($this, "wallabag_refresh_token");
				$old_rTimeout = $this->host->get($this, "wallabag_access_token_timeout");
				if( $old_rTimeout < $now ) {
					if(W_V2_DEBUG) $token_type = "renewed";
					$w_user = $this->host->get($this, "wallabag_username");
					$w_pass = $this->host->get($this, "wallabag_password");
					$postfields = array(
						"client_id" 	=> $w_cid,
						"client_secret" => $w_cs,
						"username" 		=> $w_user,
						"password" 		=> $w_pass,
						"grant_type" 	=> "password"
					);
					$OAcURL = curl_init();
						curl_setopt($OAcURL, CURLOPT_URL, $w_url . '/oauth/v2/token');
						curl_setopt($OAcURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
						curl_setopt($OAcURL, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($OAcURL, CURLOPT_TIMEOUT, 30);
						curl_setopt($OAcURL, CURLOPT_POST, true);
						curl_setopt($OAcURL, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($OAcURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
					$OAresult = curl_exec($OAcURL);
					$aTimeout =  time() + 3600;
					$rTimeout =  time() + 1209600;
					$OAstatus = curl_getinfo($OAcURL, CURLINFO_HTTP_CODE);
						curl_close($OAcURL);
					$OAresult = json_decode($OAresult,true);

					$w_access = $OAresult["access_token"];
					$w_refresh = $OAresult["refresh_token"];
					if(W_V2_DEBUG) { 
						$w_auth_error 		= $OAresult["error"];
						$w_auth_error_msg 	= $OAresult["error_description"];
					}

					if ($OAstatus == 200) {
						$this->host->set($this, "wallabag_access_token", $w_access);
						$this->host->set($this, "wallabag_access_token_timeout", $aTimeout);
						$this->host->set($this, "wallabag_refresh_token", $w_refresh);
						$this->host->set($this, "wallabag_refresh_token_timeout", $rTimeout);
					}

				} else {
					$postfields = array(
						"client_id" 	=> $w_cid,
						"client_secret" => $w_cs,
						"refresh_token" => $w_refresh,
						"grant_type" 	=> "refresh_token"
					);
					$OAcURL = curl_init();
						curl_setopt($OAcURL, CURLOPT_URL, $w_url . '/oauth/v2/token');
						curl_setopt($OAcURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
						curl_setopt($OAcURL, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($OAcURL, CURLOPT_TIMEOUT, 30);
						curl_setopt($OAcURL, CURLOPT_POST, true);
						curl_setopt($OAcURL, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($OAcURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
					$OAresult = curl_exec($OAcURL);
					$OAstatus = curl_getinfo($OAcURL, CURLINFO_HTTP_CODE);
					$new_timeout =  time() + 3600;
						curl_close($OAcURL);

					$OAresult = json_decode($OAresult,true);

					$w_access = $OAresult["access_token"];
					$w_refresh = $OAresult["refresh_token"];
					if(W_V2_DEBUG) {
						$w_auth_error = $OAresult["error"];
						$w_auth_error_msg = $OAresult["error_description"];
					}

					if ($OAstatus == 200) {

						$this->host->set($this, "wallabag_access_token", $w_access);
						$this->host->set($this, "wallabag_access_token_timeout", $new_timeout);
						$this->host->set($this, "wallabag_refresh_token", $w_refresh);

					}
				}
			}

			$postfields = array(
				'access_token' => $w_access,
				'url'          => $article_link
			);

			$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $w_url.'/api/entries.json');
				curl_setopt($cURL, CURLOPT_HEADER, 1);
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
				curl_setopt($cURL, CURLOPT_POST, 4);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
			$apicall = curl_exec($cURL);
			$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
				curl_close($cURL);

			$result = array("wallabag_url" 	=> $w_url,
							"title" 		=> $title,
							"status" 		=> $status
							);
			if(W_V2_DEBUG) $result['auth_type'] = $token_type;

			if($status !== 200 && W_V2_DEBUG){
				
				$w_debug 			= json_decode($apicall,true);
				$w_debug_error 		= $w_debug["error"];
				$w_debug_error_msg 	= $w_debug["error_description"];

				$result['auth_status'] 		= $OAstatus;
				$result['auth_error'] 		= $w_auth_error;
				$result['auth_error_msg'] 	= $w_auth_error_msg;
				$result['error'] 			= $w_debug_error;
				$result['error_msg'] 		= $w_debug_error_msg;
				$result['refresh_token']	= $w_refresh;
				$result['access_token'] 	= $w_access;

				$debug_result = json_encode($result);
				file_put_contents("plugins.local/wallabag_v2/debug.txt", date('Y-m-d H:i:s')."\r\n".$debug_result."\r\n", FILE_APPEND);
			}

		} else {
			$result = array(
						"status" 	=> 501,
						"error"	 	=> "PEBCAK",
						"error_msg" => "Please <strong>enable PHP extension CURL</strong>!"
						);
		}

		print json_encode($result);
	}

	function api_version() {
		return 2;
	}

}
?>
