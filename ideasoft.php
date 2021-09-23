<?php

$client_id = "XXX";
$client_secret = "XXX";
$site_name = "xxx.myideasoft.com";

if($_POST["authorization_code"]) {
	$access_token = getAccessToken($_POST["authorization_code"],$client_id, $client_secret,$site_name);
	$resource = getResource($access_token,$client_id, $client_secret,$site_name);
	// echo "<pre>";
	// print_r( $resource );
	// echo "</pre>";
}else if($_GET["code"]) {
	$access_token = getAccessToken($_GET["code"],$client_id, $client_secret,$site_name);
	$resource = getResource($access_token,$client_id, $client_secret,$site_name);
	// echo "<pre>";
	// print_r( $resource );
	// echo "</pre>";
}else {
	getAuthorizationCode($client_id,$site_name);
}


function getAuthorizationCode($client_id,$site_name) {
	$authorization_redirect_url = "http://".$site_name."/admin/user/auth?response_type=code&client_id=" . $client_id . "&redirect_uri=https://pazaryeri.miposteknoloji.com/cron/guncelle_IS_cron.php&scope=openid&state=fd4sa56fds4a56fsfdas456";

	header("Location: " . $authorization_redirect_url);
}

function getAccessToken($authorization_code,$client_id, $client_secret,$site_name) {
	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=https://XXXXXX";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://".$site_name."/oauth/v2/token",
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
	curl_close($curl);

	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}

	return json_decode($response)->access_token;
}
function getResource($access_token,$client_id, $client_secret,$site_name) {
	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Authorization: Bearer {$access_token}","Content-Type: application/json");
	
	date_default_timezone_set('Europe/Istanbul');
	$startDate = date("Y-m-d", strtotime('-7 days'));
	$endDate=date('Y-m-d');
	
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://".$site_name."/api/orders?sort=-id&limit=100&startDate=".$startDate."&endDate=".$endDate."&page=1",
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);

	return json_decode($response);
}

?>