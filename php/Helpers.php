<?php

if ( ! function_exists('dd')) {
	// Функция для дебага (взята из Laravel)
	function dd() {
		array_map(function($x) { var_dump($x); }, func_get_args()); die;
	}
}

if ( ! function_exists('url_exists')) {
	// Функция для проверки ссылки
	function url_exists($url) {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		
		curl_exec($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $http_code == 200;
	}
}