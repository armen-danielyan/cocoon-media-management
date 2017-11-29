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
		try {
			$output = self::SoapClient($this->getRequestId())->getThumbtypes();
		} catch(SoapFault $oSoapFault) {
			$output = $this->errorResponse($oSoapFault->faultstring);
		}
		return $output;
	}

	function getSets() {
		try {
			$output = self::SoapClient($this->getRequestId())->getSets();
		} catch(SoapFault $oSoapFault) {
			$output = $this->errorResponse($oSoapFault->faultstring);
		}
		return $output;
	}

	function getFilesBySet($setId) {
		try {
			$output = self::SoapClient($this->getRequestId())->getFilesBySet($setId);
		} catch(SoapFault $oSoapFault) {
			$output = $this->errorResponse($oSoapFault->faultstring);
		}
		return $output;
	}

	function getFile($fileId) {
		try {
			$output = self::SoapClient($this->getRequestId())->getFile($fileId);
		} catch(SoapFault $oSoapFault) {
			$output = $this->errorResponse($oSoapFault->faultstring);
		}
		return $output;
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
		$fileDim = $aFile['width'] && $aFile['height'] ? $aFile['width'] . ' x ' . $aFile['height'] : '';
		$fileSize = $aFile['size'] ? round($aFile['size'] / 1024) . ' KB' : '';

		if($aFile['upload_date']) {
			$date = date_create($aFile['upload_date']);
			$fileUploaded = date_format($date, get_option( 'date_format' ));
		} else {
			$fileUploaded = '';
		}

		return array(
			'path' => $url . $thumbOrgPath . '/' . $filename . '.' . $thumbTypeExtention,
			'web' => $url . $thumbWebPath . '/' . $filename . '.' . $thumbTypeExtention,
			'ext' => $thumbTypeExtention,
			'name' => $filename,
			'dim' => $fileDim,
			'size' => $fileSize,
			'uploaded' => $fileUploaded
		);
	}

	public function getRequestId() {
		return (string)microtime(true);
	}

	private function errorResponse($errMsg) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => $errMsg ) );
	}

	function getVersion() {
		$output = '';
		try {
			$output = self::SoapClient($this->getRequestId())->getVersion();
		} catch(SoapFault $oSoapFault) {
			$output = $this->errorResponse($oSoapFault->faultstring);
		}

		return $output;
	}
}