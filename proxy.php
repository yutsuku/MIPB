<?php
define("URL", "http://multirbl.valli.org/json-lookup.php");
$ash=(isset($_POST['ash']) ? htmlspecialchars($_POST['ash']) : false);
$rid=(isset($_POST['rid']) ? htmlspecialchars($_POST['rid']) : false);
$lid=(isset($_POST['lid']) ? htmlspecialchars($_POST['lid']) : false);
$q=(isset($_POST['q']) ? htmlspecialchars($_POST['q']) : false);
if ( $ash && $rid && $lid && $q ) {
$postdata = http_build_query(
	array(
		"ash"	=> $ash,
		"rid"	=> $rid,
		"lid"	=> $lid,
		"q"		=> $q
	)
);
$opts = array(
	'http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
    )
);
$context  = stream_context_create($opts);
echo file_get_contents(URL, false, $context);
}
?>