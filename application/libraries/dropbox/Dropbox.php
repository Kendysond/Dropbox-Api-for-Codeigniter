<?php
//  error_reporting(-1);
// 		ini_set('display_errors', 1);
// enable_implicit_flush();
// set_time_limit(0);	//1.Stat Session
require_once("DropboxClient.php");
	 
class Dropbox {
	

	public function __construct() {
		$CI =& get_instance();
		
		// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
		$dropbox = new DropboxClient(array(
			'app_key' => "", 
			'app_secret' => "",
			'app_full_access' => false,
		),'en');

		$this->handle_dropbox_auth($dropbox); // see below
		//
		echo "<pre>";
		echo "<b>Account:</b>\r\n";
		print_r($dropbox->GetAccountInfo());

		// TO UPLOAD 
			$filepath = " Filepath for the file use FCPATH as base_url()";
			// Usage: $filepath = FCPATH.'portfolio.png';
			Usage: $filepath = FCPATH.'portfolio.png';
			/*   To upload   */
			$location = " Location path on your dropbox account , include the file extension too.";
			/* Usage: 
				---------------------------------------------
				$location = 'portfolio.png';
				THis is use to upload to the root folder of your dropbox
				---------------------------------------------
				$location = 'folder/portfolio.png';
				THis is use to upload to a folder of your dropbox
				

			*/
			// $meta = $dropbox->UploadFile($filepath, $location);
			// print_r($meta);

		//MORE FUNCTIONS
				
				// echo "\r\n\r\n<b>Meta data of <a href='".$dropbox->GetLink($file)."'>$filepath</a>:</b>\r\n";
				// print_r($dropbox->GetMetadata($filepath));
				
				// echo "\r\n\r\n<b>Downloading $filepath:</b>\r\n";
				// print_r($dropbox->DownloadFile($file, $test_file));
					
				// echo "\r\n\r\n<b>Uploading $test_file:</b>\r\n";
				// print_r($dropbox->UploadFile($test_file));
				// echo "\r\n done!";	
				
				// echo "\r\n\r\n<b>Revisions of $test_file:</b>\r\n";	
				// print_r($dropbox->GetRevisions($test_file));
			
	}	
		// ================================================================================
		// store_token, load_token, delete_token are SAMPLE functions! please replace with your own!
	function store_token($token, $name){
		
		file_put_contents(APPPATH."libraries/dropbox/tokens/$name.token", serialize($token));
	
	}

	function load_token($name){
		if(!file_exists(APPPATH."libraries/dropbox/tokens/$name.token")) return null;
		return @unserialize(@file_get_contents(APPPATH."libraries/dropbox/tokens/$name.token"));
	}

	function delete_token($name){
		
		@unlink(APPPATH."libraries/dropbox/tokens/$name.token");
	
	}
	// ================================================================================

	function handle_dropbox_auth($dropbox){
		// first try to load existing access token
		$access_token = $this->load_token("access");
		if(!empty($access_token)) {
			$dropbox->SetAccessToken($access_token);
			echo "loaded access token:";
			print_r($access_token);
		}
		elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
		{
			// then load our previosly created request token
			$request_token = $this->load_token($_GET['oauth_token']);
			if(empty($request_token)) die('Request token not found!');
			
			// get & store access token, the request token is not needed anymore
			$access_token = $dropbox->GetAccessToken($request_token);	
			$this->store_token($access_token, "access");
			$this->delete_token($_GET['oauth_token']);
		}

		// checks if access token is required
		if(!$dropbox->IsAuthorized())
		{
			// redirect user to dropbox auth page
			//Custom Page And Oauth2 On dropbox Accepts only https:// i think
			//$return_url = "https://admin.com/?auth_callback=1";
			
			$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
			$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
			$request_token = $dropbox->GetRequestToken();
			$this->store_token($request_token, $request_token['t']);
			die("Authentication required. <a href='$auth_url'>Click here.</a>");
		}
	}



}