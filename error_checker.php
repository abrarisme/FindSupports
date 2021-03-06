<?php
/** This file is used to determine if there is an error 
with the summoner given in the search bar by the user. */

include_once('playersystem/php-riot-api.php');

//Get information from request.
$summoner_name = $_REQUEST["summoner"];
$region = $_REQUEST["region"];

//Take split up names and combine them.
$summoner_name = explode('+', $summoner_name);
$summoner_name = implode($summoner_name);
$summoner_name = str_replace(' ', '', $summoner_name);

//Check for valid input and send back relevant errors.
//The name entered cannot be empty.
try {
	if ($summoner_name == "") {
		throw new Exception("empty_name");
	} 
}catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
	return;
}

//Get summoner stats from League API.
$api = new riotapi($region);
$lower_name = strtolower($summoner_name); 
$summoner_api = $api->getSummonerByName($summoner_name);
$summoner = json_decode($summoner_api, true);

//Rate limit has been reached.
try {
	if (array_key_exists('status', $summoner)) {
		throw new Exception('rate_limit');
	} 
}catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
	return;
}


//Store relevant information: ID, name, level. 
$summoner_api_name = $summoner[$lower_name]['name'];
$id = $summoner[$lower_name]['id'];
$level = $summoner[$lower_name]['summonerLevel'];


//Summoner must exist on League of Legends.
try {
	if ($summoner_api_name == "" or $id == "") {
		throw new Exception('no_summoner');
	}
} catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
	return;
}

//Player must be level 30.
try {
	if ($level != '30') {
		throw new Exception("under30");
	} 
}catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
	return;
}

//Champion is valid.
echo "summoner=" . $summoner_name . "&region=" . $region;

?>
