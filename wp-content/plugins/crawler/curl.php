<?php 

function curl($strURL = '', $strCookiePath = '', $arrHeader = array(), $arrData = array()) {
	if (empty($strURL)) {
		return false;
		//throw new \Zend\Http\Exception('URL cannot be empty');
	}
	$crawler = curl_init($strURL);

	if ($arrHeader) {
		curl_setopt($crawler, CURLOPT_HTTPHEADER, $arrHeader);
	}

	if ($strCookiePath) {
		curl_setopt($crawler, CURLOPT_COOKIEJAR, $strCookiePath);
		curl_setopt($crawler, CURLOPT_COOKIEFILE, $strCookiePath);
		curl_setopt($crawler, CURLOPT_COOKIE, session_name() . '=' . session_id());
	}
	curl_setopt($crawler, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($crawler, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($crawler, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($crawler, CURLOPT_DNS_CACHE_TIMEOUT, 0);
	curl_setopt($crawler, CURLOPT_MAXREDIRS, 5);
	curl_setopt($crawler, CURLOPT_FRESH_CONNECT, TRUE);
	curl_setopt($crawler, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');
	curl_setopt($crawler, CURLOPT_RETURNTRANSFER, TRUE);
	$arrData ? curl_setopt($crawler, CURLOPT_POSTFIELDS, $arrData) : '';
	$data = curl_exec($crawler);
	curl_close($crawler);
	return $data;
}

function getSslPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}