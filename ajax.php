<?php
include "lookup.php";
if ( isset($_GET['ip']) ) {
	$ip = htmlspecialchars($_GET['ip']);
	$check = new MailBlackList($ip);
	$check->getSessionToken();
	$check->getDatabases();
	$check->prepareQuery();
	echo $check->RespondToAjaxRequest();
}
?>