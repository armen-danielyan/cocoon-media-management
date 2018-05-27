<?php
class CMM_Cocoon {
	public static $domainName = 'use-cocoon.nl';

	public $thumbsPerPage = 24;

	public static function SoapClient($reqId) {
		$subDomain = get_option('cmm_stng_domain');
		$domainName = self::$domainName;
		$username = get_option('cmm_stng_username');
		$requestId = $reqId;
		$secretkey = get_option('cmm_stng_secret');
		$wsdl = "https://{$subDomain}.{$domainName}/webservice/wsdl";

		$hash = sha1($subDomain . $username . $requestId . $secretkey);

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
			$output = $oSoapFault;
		}
		return $output;
	}

	function getSets() {
		try {
			$output = self::SoapClient($this->getRequestId())->getSets();
		} catch(SoapFault $oSoapFault) {
			$output = $oSoapFault;
		}
		return $output;
	}

	function getFilesBySet($setId) {
		try {
			$output = self::SoapClient($this->getRequestId())->getFilesBySet($setId);
		} catch(SoapFault $oSoapFault) {
			$output = $oSoapFault;
		}
		return $output;
	}

	function getFile($fileId) {
		try {
			$output = self::SoapClient($this->getRequestId())->getFile($fileId);
		} catch(SoapFault $oSoapFault) {
			$output = $oSoapFault;
		}
		return $output;
	}

	function getThumbInfo($fileId) {
		$subDomain = get_option('cmm_stng_domain');
		$domainName = self::$domainName;
		$url = "https://{$subDomain}.{$domainName}";
		$thumbOrg = 'original';
		$thumbWeb = '400px';

		$noThumb = true;

		$aThumbTypes = $this->getThumbTypes();
		$thumbOrgPath = $aThumbTypes[$thumbOrg]['path'];
		$thumbWebPath = $aThumbTypes[$thumbWeb]['path'];

		$aFile = $this->getFile($fileId);
		$filename = $aFile['filename'];
		$extention = strtolower($aFile['extension']);

		if($extention === 'jpg' ||
		   $extention === 'png' ||
		   $extention === 'gif' ||
		   $extention === 'tiff' ||
		   $extention === 'bmp'
		) {
			$noThumb = false;
		}

		$fileDim = $aFile['width'] && $aFile['height'] ? $aFile['width'] . ' x ' . $aFile['height'] : '';
		$fileSize = $aFile['size'] ? round($aFile['size'] / 1024) . ' KB' : '';

		if($aFile['upload_date']) {
			$date = date_create($aFile['upload_date']);
			$fileUploaded = date_format($date, get_option( 'date_format' ));
		} else {
			$fileUploaded = '';
		}

		return array(
			'path' => $url . $thumbOrgPath . '/' . $filename . '.' . $extention,
			'web' => !$noThumb ? $url . $thumbWebPath . '/' . $filename . '.' . $extention : '',
			'ext' => $extention,
			'name' => $filename,
			'dim' => $fileDim,
			'size' => $fileSize,
			'uploaded' => $fileUploaded,
			'domain' => $url
		);
	}

	public function getRequestId() {
		return (string)microtime(true);
	}

	private function errorResponse($errMsg) {
		return json_encode( array( 'status' => 'error', 'statusMsg' => $errMsg ) );
	}

	function getVersion() {
		try {
			$output = self::SoapClient($this->getRequestId())->getVersion();
		} catch(SoapFault $oSoapFault) {
			$output = $oSoapFault;
		}
		return $output;
	}
}