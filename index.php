<?php
set_time_limit(0); #Means that script is going to run for infinte amount of time
ini_set('default_socket_timeout',300);

/*------ Instagram API Keys -------*/
define('clientID','**********'); //Client ID Grabbed from api.instagram.com
define('clientSecret','******');//Client Secret
define('redirectUrl','*******');//Redirect Url
define('imageDir','pics/');

#Connect to the instagram
function connect_to_instagram($url){
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_TIMEOUT        => 2000
	));
	$result = curl_exec($ch);
	
	return $result;
	
}

#Get the Instagram user id
function get_user_id($username, $a_token){
	
	$url = 'https://api.instagram.com/v1/users/search?q='.$username.'&access_token='.$a_token;
	$instagram_info = connect_to_instagram($url);
	$results = json_decode($instagram_info, true);
	#return $results['data'][0]['id'];
	echo '<br><pre>';
	print_r($results);
	echo '</pre><br>';
}

#Print the User images
function printImages($user_id,$a_token){
	$url = 'https://api.instagram.com/v1/users/'.$user_id.'/media/recent?access_token='.$a_token.'&count=-1';
	$instagram_info = connect_to_instagram($url);
	$results = json_decode($instagram_info, true);
	echo '<br><pre>';
	print_r($results);
	echo '</pre><br>';
	//Parse through results
	foreach($results['data'] as $items){
		$image_url = $items['images']['low_resolution']['url'];
		echo "<img src=".$image_url." /><br>";
		savePicture($image_url);
		
	}
	
}
#Save the Picture
function  savePicture($url){
	echo $url.'<br>';
	$file_name = basename($url);
	echo $file_name.'<br>';
	//Make sure that file doesnot exits in the database
	echo $dest = imageDir.$file_name;
	echo '<br>';
	file_put_contents($dest, file_get_contents($url));
}



if(isset($_GET['code'])){
	
	$code = $_GET['code'];
	$url = 'https://api.instagram.com/oauth/access_token';
	$access_token = array(
		'client_id'       =>  clientID,
		'client_secret'	  =>  clientSecret,
		'grant_type'	  =>  'authorization_code',
		'redirect_uri'	  =>  redirectUrl,
		'code'			  =>  $code
	);
	$curl = curl_init($url);#says to the the instagram that we are getting some data from you
	#setting options for the data transfer
	curl_setopt($curl, CURLOPT_POST, true);#means curl set option  CURLOPT_POST is a parameter for post request which is true
	curl_setopt($curl, CURLOPT_POSTFIELDS, $access_token);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);#return all the result as a string
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	
	$result = curl_exec($curl); #start the communication process
	curl_close();
	
	$results = json_decode($result, true);
	echo '<pre>';
	print_R($results);
	echo '</pre>';
	
	$username = $results['user']['username'];
	$a_token = $results['access_token'];
	get_user_id($username,$a_token);
	$user_id = $results['user']['id'];
	printImages($user_id,$a_token);
	
}else{?>
<!DOCTYPE html>
<html>
 <head>
	<title>Image Download From Instagram</title>
 </head>
 <body>
	 <a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID;?>&redirect_uri=<?php echo redirectUrl;?>&response_type=code">Login</a>
 </body>
</html>

<?php }?>
