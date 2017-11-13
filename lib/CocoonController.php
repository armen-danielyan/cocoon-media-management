<?php

class Cocoon {
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

	function getThumbInfo($fileId) {
		$subdomain = get_option('cn_domain');
		$url = 'https://' . $subdomain . '.use-cocoon.com';
		$thumbOrg = 'original';
		$thumbWeb = '400px';

		$aThumbTypes = $this->getThumbTypes();
		$thumbOrgPath = $aThumbTypes[$thumbOrg]['path'];
		$thumbWebPath = $aThumbTypes[$thumbWeb]['path'];

		$aFile = $this->getFile($fileId);
		$filename = $aFile['filename'];
		$extention = $aFile['extension'];

		$thumbTypeExtention = $thumbOrg == 'original' || $thumbOrg == 'gif' || $thumbOrg == 'png' ? $extention : 'jpg';

		return array(
			'path' => $url . $thumbOrgPath . '/' . $filename . '.' . $thumbTypeExtention,
			'web' => $url . $thumbWebPath . '/' . $filename . '.' . $thumbTypeExtention,
			'ext' => $thumbTypeExtention,
			'name' =>$filename
		);
	}

	public function getRequestId() {
		return (string)microtime(true);
	}

	function getVersion() {
		$output = '';
		try {
			$output = self::SoapClient($this->getRequestId())->getVersion();
		} catch(SoapFault $oSoapFault) {
			// reserved
		}

		return $output;
	}
}