<?php
require_once __DIR__ . '/../config/push.php';
function sendPush($title, $message, $userId) {
	$content = array("en" => $message);
	$fields = array(
		'app_id' => ONESIGNAL_APP_ID,
		'include_external_user_ids' => array($userId),
		'headings' => array("en" => $title),
		'contents' => $content
	);
	$fields = json_encode($fields);
	$ch = curl_init("https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Authorization: Basic ' . ONESIGNAL_API_KEY
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
?>
