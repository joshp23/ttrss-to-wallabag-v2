<?php
define( 'W_V2_DEBUG', false );
class Wallabag_v2 extends Plugin {
	private $host;
	function about() {
		return array("2.0.0",
			"Post articles to a Wallabag v 2.x instance",
			"joshu@unfettered.net",
			false,
			"https://github.com/joshp23/ttrss-to-wallabag-v2");
	}

	function init($host) {
		$this->host = $host;
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		$host->add_hook($host::HOOK_HOUSE_KEEPING, $this);
		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_HOTKEY_MAP, $this);
		$host->add_hook($host::HOOK_HOTKEY_INFO, $this);
		$host->add_hook($host::HOOK_ARTICLE_FILTER_ACTION, $this);
		$host->add_filter_action($this, "wallabag_v2_send_to_Wallabag", "Send to Wallabag");
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

	    $w_user = $this->host->get($this, "wallabag_username");
	    $w_pass = $this->host->get($this, "wallabag_password");
		$w_url = $this->host->get($this, "wallabag_url");
		$w_cid = $this->host->get($this, "wallabag_client_id");
		$w_csec = $this->host->get($this, "wallabag_client_secret");
		$w_access = $this->host->get($this, "wallabag_access_token");

		?>
		<div dojoType='dijit.layout.AccordionPane' 
			title="<i class='material-icons'>share</i> <?= __('Wallabag v2') ?>">
			<br/>
			<form dojoType='dijit.form.Form'>

				<?= \Controls\pluginhandler_tags($this, "save") ?>
				<script type="dojo/method" event="onSubmit" args="evt">
					evt.preventDefault();
					if (this.validate()) {
						Notify.progress('Saving wallabag configuration...', true);
						xhr.post("backend.php", this.getValues(), (reply) => {
							Notify.info(reply);
						})
					}
				</script>

		<table width='100%' class='prefPrefsList'>
			<tr>
				<td width='40%'> <?= __("Wallabag URL (No trailing slash!)") ?></td>
				<td class='prefValue'><input dojoType='dijit.form.ValidationTextBox' required='true' name='wallabag_url' regExp='^(http|https)://.*' value='<?= $w_url ?>'></td>
			</tr>
			<tr>
				<td width='40%'><?= __("Wallabag Username") ?></td>
				<td class='prefValue'><input dojoType='dijit.form.ValidationTextBox' name='wallabag_username' regExp='\w{0,64}' value='<?= $w_user ?>'></td>
			</tr>
			<tr>
				<td width='40%'><?= __("Wallabag Password") ?></td>
				<td class='prefValue'><input type='password' dojoType='dijit.form.ValidationTextBox' name='wallabag_password' regExp='.{0,64}' value='<?= $w_pass ?>'></td>
			</tr>
			<tr>
				<td width='40%'><?= __("Wallabag Client ID") ?></td>
				<td class='prefValue'><input dojoType='dijit.form.ValidationTextBox' name='wallabag_client_id' regExp='.{0,64}' value='<?= $w_cid ?>'></td>
			</tr>
			<tr>
				<td width='40%'><?= __("Wallabag Client Secret") ?></td>
				<td class='prefValue'><input dojoType='dijit.form.ValidationTextBox' name='wallabag_client_secret' regExp='.{0,64}' value='<?= $w_csec ?>'></td>
			</tr>
		</table>

		<?= \Controls\submit_tag(__("Save")) ?>
		<?php if($w_access == null || $w_access == false|| $w_access == "") { ?>
			<strong style='color:red;'>  Alert</strong>: No OAuth tokens in Database! Submit Username and Password to retrieve new tokens.
		<?php } else { ?>
			Submit this form without a Username and Password to reset the Wallabag OAuth Tokens to a null value.
		<?php } ?>
			</form>
		</div>
		<?php
	}

	function save() {
	    $w_url 	= $_POST['wallabag_url'];
	    $w_user = $_POST['wallabag_username'];
	    $w_pass = $_POST['wallabag_password'];
	    $w_cid 	= $_POST['wallabag_client_id'];
	    $w_cs 	= $_POST['wallabag_client_secret'];
		// got curl? Get the oAuth tokens TODO move this to _oauth()
		if (function_exists('curl_init')) {
			// prepare curl call
			$postfields = array(
				"client_id" 	=> $w_cid,
				"client_secret" => $w_cs,
				"username" 		=> $w_user,
				"password" 		=> $w_pass,
				"grant_type" 	=> "password"
			);
			// call curl
			$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $w_url . '/oauth/v2/token');
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
				curl_setopt($cURL, CURLOPT_POST, true);
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
			$result = curl_exec($cURL);
			$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
				curl_close($cURL);
			// prepare result data
			$watt 	= time() + 3600;		// 1 hour
			$wrtt 	= time() + 1123200;	// 13 days
			$result = json_decode($result,true);
			// store result data
			$this->host->set($this, "wallabag_access_token", $result['access_token']);
			$this->host->set($this, "wallabag_access_token_timeout", $watt );
			$this->host->set($this, "wallabag_refresh_token", $result['refresh_token']);
			$this->host->set($this, "wallabag_refresh_token_timeout", $wrtt);
		} else {
			// get curl!
			$status 		= 501;
			$w_error 		= "PEBCAK";
	    	$w_error_msg 	= "Please <strong>enable PHP extension CURL</strong>!";
		}
		// store pref data
	    $this->host->set($this, "wallabag_username", $w_user);
	    $this->host->set($this, "wallabag_password", $w_pass);
	    $this->host->set($this, "wallabag_url", $w_url);
	    $this->host->set($this, "wallabag_client_id", $w_cid);
	    $this->host->set($this, "wallabag_client_secret", $w_cs);
		// failing?
		if($status !== 200){ 
			if(W_V2_DEBUG) {
				// prepare debug data, write to file, print to console
				$reult = array(
							"wallabag_url" 	=> $w_url,
							"client_id" 	=> $w_cid,
							"client_secret" => $w_cs,
						 	"error" 		=> $result['error'],
							"error_msg" 	=> $result['error_description'],
							"refresh_token" => $result['refresh_token'],
							"access_token" 	=> $result['access_token'],
							"status" 		=> $status
							);
				$reult = json_encode($result);
				file_put_contents("plugins.local/wallabag_v2/debug.txt", date('Y-m-d H:i:s')."\r\n".$reult."\r\nPREFS\r\n", FILE_APPEND);
				print $reult;

			} else 
				print "Error Saving Prefs. Try again.";
		} else // success!
				print "Ready to send to Wallabag at $w_url";
	}

	function hook_house_keeping() {
		$this->_oauth();
	}

	function hook_article_button($line) {
		$article_id = $line['id'];

		$rv = "<img id=\"wallabagImgId\" src=\"plugins.local/wallabag_v2/wallabag.png\"
			class='tagsPic' style=\"cursor: pointer;\"
			onclick=\"postArticleToWallabag($article_id)\"
			title='".__('Wallabag v2')."'/>";

		return $rv;
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/wallabag_v2.js");
	}

	function hook_hotkey_map($hotkeys) {
		$hotkeys['a w'] = 'send_to_wallabag';
		return $hotkeys;
	}

	function hook_hotkey_info($hotkeys) {
		$hotkeys[__("Article")]['send_to_wallabag'] = __("Send Article to your Wallabag");
		return $hotkeys;
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

		$source = 'button';		
		$result = $this->_send( $title, $article_link, $source );

		print $result;
	}

	function hook_article_filter_action($article, $action) {
		if ( $action == 'wallabag_v2_send_to_Wallabag' ) {
			$tags = (is_array($article['tags'])) ? array_flip($article['tags']) : array();
			if ( !isset( $tags['w_v2'] ) ) {
				$source = 'filter';
				$title = truncate_string(strip_tags($article['title']), 100, '...');

				$this->_send( $title, $article['link'], $source );

				$tags = array_keys( $tags );
				$tags[] = 'w_v2';
				$article['tags'] = $tags;
			}
		}
		return $article;
	}

	private function _send( $title, $article_link, $source ) {
		// got curl?
		if (function_exists('curl_init')) {
			$result = $this->_oauth();
			// gather values
			$w_url 		= $this->host->get($this, "wallabag_url");
			$w_cid 		= $this->host->get($this, "wallabag_client_id");
			$w_cs 		= $this->host->get($this, "wallabag_client_secret");
			$w_access 	= $this->host->get($this, "wallabag_access_token");
			// prepare curl call
			$postfields = array(
				'access_token' => $w_access,
				'url'          => $article_link
			);
			// call curl
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
			// prepare result
			$result['wallabag_url'] = $w_url;
			$result['title'] 		= $title;
			$result['status'] 		= $status;
			// prepare debug data
			if($status !== 200){
				$debug 						= json_decode($apicall,true);
				$result['error'] 			= $debug['error'];
				$result['error_msg'] 		= $debug['error_description'];
			}
		} else {
			$result = array(
						"status" 	=> 501,
						"error"	 	=> "PEBCAK",
						"error_msg" => "Please <strong>enable PHP extension CURL</strong>!"
						);
		}
		// log data in dubug.txt
		if ( $result['status'] !== 200  && W_V2_DEBUG ) {
			$result['source'] = $source;
			file_put_contents("plugins.local/wallabag_v2/debug.txt", date('Y-m-d H:i:s')."\r\nSEND\r\n".json_encode($result)."\r\n", FILE_APPEND);
		}
		// return data for button submission
		if ($source === 'button')  
			return json_encode($result);
	}

	private function _oauth() {
		// got curl?
		if (function_exists('curl_init')) {
			$auth_type = "fresh";
			// gather value
			$ato = $this->host->get($this, "wallabag_access_token_timeout");
			$now = time();
			// check access token age
			if( $ato && $now >= $ato ) {
				// obtain new access token
				$auth_type = "refreshed";
				// gather value
				$r_ato 	= $this->host->get($this, "wallabag_refresh_token_timeout");
				// check refresh token age
				if( $now >= $r_ato ) {
					// obtain new access token utilizing passwords
					$auth_type 	= "upass";
					$postfields['username'] = $this->host->get($this, "wallabag_username");
					$postfields['password'] = $this->host->get($this, "wallabag_password");
					$postfields['grant_type'] = "password";
				} else {
					// obtain new access token utilizing refresh token
					$postfields['refresh_token'] 	= $this->host->get($this, "wallabag_refresh_token");
					$postfields['grant_type'] 		= "refresh_token";
				}
				// gather values
				$w_url 							= $this->host->get($this, "wallabag_url");
				$postfields['client_id'] 		= $this->host->get($this, "wallabag_client_id");
				$postfields['client_secret']	= $this->host->get($this, "wallabag_client_secret");
				// call curl
				$cURL = curl_init();
					curl_setopt($cURL, CURLOPT_URL, $w_url . '/oauth/v2/token');
					curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
					curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($cURL, CURLOPT_TIMEOUT, 30);
					curl_setopt($cURL, CURLOPT_POST, true);
					curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postfields));
				$result = curl_exec($cURL);
				$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
					curl_close($cURL);
				// prepare response data
				$result 	= json_decode($result,true);
				// success?
				if ($status == 200) {
					$watt = time() + 3600;		// 1 hour
					$wrtt = time() + 1123200;	// 13 days
					// store new tokens and values
					$this->host->set($this, "wallabag_access_token", $result['access_token']);
					$this->host->set($this, "wallabag_access_token_timeout", $watt);
					$this->host->set($this, "wallabag_refresh_token", $result['refresh_token']);
					$this->host->set($this, "wallabag_refresh_token_timeout", $wrtt);
				}

			} else {
				$status = 200;
			}

		} else {
			$status 				= 501;
			$result['error']		= "PEBCAK";
			$result['error_msg'] 	= "Please <strong>enable PHP extension CURL</strong>!";
		}

		$result['auth_type'] 		= $auth_type;
		$result['auth_status'] 		= $status;

		if( $status !== 200 && W_V2_DEBUG ){
			// prepare debug data and write to file
			$result['auth_error'] 		= $result['error'];
			$result['auth_error_msg'] 	= $result['error_description'];
			file_put_contents("plugins.local/wallabag_v2/debug.txt", date('Y-m-d H:i:s')."\r\nOAUTH\r\n".json_encode($result)."\r\n", FILE_APPEND);
		}

		return $result;
	}

	function api_version() {
		return 2;
	}

}
?>
