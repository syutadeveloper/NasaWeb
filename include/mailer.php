<?php
function sendMail($to, $subject, $message) {
	$headers = "From: info@nasaweb.local\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	return mail($to, $subject, $message, $headers);
}
?>
