<?php

class Cocoon {
	function __construct() {

	}

	public static function SoapClient($reqId) {
		$wsdl = 'https://wordpress.use-cocoon.nl/webservice/wsdl';
		$domain = get_option('cn_domain');
		$username = get_option('cn_username');
		$requestId = $reqId;
		$secretkey = get_option('cn_secret');

		$hash = sha1($domain . $username . $requestId . $secretkey);

		$oAuth = new stdClass;
		$oAuth->username = $username;
		$oAuth->requestId = $requestId;
		$oAuth->hash = $hash;

		$oSoapClient = new SoapClient($wsdl);
		$SoapHeader = new SoapHeader('auth','authenticate', $oAuth);
		$oSoapClient->__setSoapHeaders($SoapHeader);

		return $oSoapClient;
	}

	function getThumbTypes() {
		return self::SoapClient($this->getRequestId())->getThumbtypes();
	}

	function getSets() {
		return self::SoapClient($this->getRequestId())->getSets();
	}

	function getFilesBySet($setId) {
		return self::SoapClient($this->getRequestId())->getFilesBySet($setId);
	}

	function getFile($fileId) {
		return self::SoapClient($this->getRequestId())->getFile($fileId);
	}

	function getThumbUrl($fileId) {
		$url = 'https://wordpress.use-cocoon.com';
		$thumbType = 'original';

		$aThumbTypes = $this->getThumbTypes();
		$thumbTypePath = $aThumbTypes[$thumbType]['path'];

		$aFile = $this->getFile($fileId);
		$filename = $aFile['filename'];
		$extention = $aFile['extension'];

		$thumbTypeExtention = $thumbType == 'original' || $thumbType == 'gif' || $thumbType == 'png' ? $extention : 'jpg';

		return $fileUrl = $url . $thumbTypePath . '/' . $filename . '.' . $thumbTypeExtention;
	}

	public function getRequestId() {
		return (string)microtime(true);
	}
}