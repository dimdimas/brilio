<?php

/**
 *  ------------------------------------------------
 *
 *  Konfigurasi Database
 *
 *  ------------------------------------------------
 */
//determine from where acessed
$domain = explode('/', $_SERVER['REQUEST_URI']);

if ($domain[4] == 'en') {
	$config['BRILIO_CURRENT_LANG'] = 'en';
}else {
	$config['BRILIO_CURRENT_LANG'] = 'id';
}
//END OF determine from where acessed

$database["default"]["host"] = 'localhost';
$database["default"]["name"] = 'newshubid';
$database["default"]["username"] = 'newshubid';
$database["default"]["password"] = 'Bdx0_x!_^TbbVT';
$database["default"]["prefix"] = 'newshubid_';
$database["default"]['debug'] = false;
if($config['BRILIO_CURRENT_LANG'] == 'en'){
	$database["default"]["domain_id"] = '10';
	$database["master"]["domain_id"] = '10';
}else{
	$database["default"]["domain_id"] = '3';
	$database["master"]["domain_id"] = '3';
}

$database["master"]["host"] = 'localhost';
$database["master"]["name"] = 'newshubid';
$database["master"]["username"] = 'newshubid';
$database["master"]["password"] = 'Bdx0_x!_^TbbVT';
$database["master"]["prefix"] = 'newshubid_';
$database["master"]['debug'] = false;
?>
