<?php

global $apache_request_headers;

function apache_request_headers()
{
	global $apache_request_headers;

	return $apache_request_headers;
}

function set_apache_request_headers($headers)
{
	global $apache_request_headers;

	$apache_request_headers = $headers;
}