<?php
/**
 * GaiaEHR (Electronic Health Records)
 * Copyright (C) 2013 Certun, LLC.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if (!isset($_SESSION)) {
	session_cache_limiter('private');
	session_name('mdTimeLine');
	session_start();
}

if (!isset($_REQUEST['id'])){
	die('');
}

if (strlen($_REQUEST['id']) > 0 && ($_REQUEST['id'][0] == '-' || $_REQUEST['id'] == '0') ){
	die('');
}

if (!isset($_REQUEST['token']) || str_replace(' ', '+', $_REQUEST['token']) != $_SESSION['user']['token']) {
	die('Not Authorized!');
}

if (!defined('_GaiaEXEC')) {
	define('_GaiaEXEC', 1);
}

if (!defined('ROOT')) {
	define('ROOT', str_replace('\\', '/', dirname(dirname(__FILE__))));
}

if (isset($_REQUEST['site'])) {
	if (!defined('SITE')) {
		define('SITE', $_REQUEST['site']);
	}
}

require_once(ROOT . '/dataProvider/Site.php');
\Site::setAllowSiteSwitch(true);

require_once(str_replace('\\', '/', dirname(__FILE__)) . '/../registry.php');

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 5);

if (
	isset($_SESSION['user']) && (
		(isset($_SESSION['user']['auth']) && $_SESSION['user']['auth'] == true) ||
		(isset($_SESSION['user']['portal_authorized']) && $_SESSION['user']['portal_authorized'] == true)
	)
) {
	/**
	 * init Matcha
	 */
	require_once(ROOT . '/classes/MatchaHelper.php');
	require_once(ROOT . '/dataProvider/TransactionLog.php');
	require_once(ROOT . '/dataProvider/Globals.php');
	new MatchaHelper();
	$TransactionLog = new TransactionLog();

	function get_mime_type($file)
	{
		$mime_types = [
			"pdf" => "application/pdf",
			"exe" => "application/octet-stream",
			"zip" => "application/zip",
			"xml" => "application/xml",
			"docx" => "application/msword",
			"doc" => "application/msword",
			"xls" => "application/vnd.ms-excel",
			"ppt" => "application/vnd.ms-powerpoint",
			"gif" => "image/gif",
			"png" => "image/png",
			"jpeg" => "image/jpg",
			"jpg" => "image/jpg",
			"bmp" => "image/bmp",
			"mp3" => "audio/mpeg",
			"wav" => "audio/x-wav",
			"mpeg" => "video/mpeg",
			"mpg" => "video/mpeg",
			"mpe" => "video/mpeg",
			"mov" => "video/quicktime",
			"avi" => "video/x-msvideo",
			"3gp" => "video/3gpp",
			"css" => "text/css",
			"jsc" => "application/javascript",
			"js" => "application/javascript",
			"php" => "text/html",
			"htm" => "text/html",
			"html" => "text/html",

			"txt" => "text/plain"
		];

		$foo = explode('.', $file);
		$extension = strtolower(end($foo));
		return isset($mime_types[$extension]) ? $mime_types[$extension] : '';
	}

	function base64ToBinary($document, $encrypted, $is_image){
		// handle binary documents
		if(preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $document)){
			return base64_decode($document);
		}
		return $document;
	}

	function isBinary($document){
		if (function_exists('is_binary') && is_binary($document)) {
			return true;
		} elseif (preg_match('~[^\x20-\x7E\t\r\n]~', $document) > 0) {
			return true;
		}
		return false;
	}

	$isTemp = isset($_REQUEST['temp']);

	if ($isTemp) {
		$d = MatchaModel::setSenchaModel('App.model.patient.PatientDocumentsTemp');

		$doc = $d->load($_REQUEST['id'])->one();
		if ($doc === false) {
			error_log('No Document Found, Please contact Support Desk. Thank You!');
			die('No Document Found, Please contact Support Desk. Thank You!');
		}
		//$doc = (object)$doc;
		$doc['name'] = isset($doc['document_name']) && $doc['document_name'] != '' ? $doc['document_name'] : 'temp.pdf';
		$doc['is_temp'] = 'true';
		$mineType = get_mime_type($doc['name']);
		$is_image = preg_match('/^image/', $mineType);
		$document = base64ToBinary($doc['document'], false, $is_image);
		$TransactionLog->saveTransactionLog([
			'event' => 'EXPORT',
			'data' => 'Generated a PDF'
		]);
	} else {
		$d = MatchaModel::setSenchaModel('App.model.patient.PatientDocuments');
		$doc = $d->load($_REQUEST['id'])->one();
		if ($doc === false) {
			error_log('No Document Found for id ' . $_REQUEST['id']);
			die();
		}
		$doc['is_temp'] = 'false';
		$filesystem_path = '';

		if(isset($doc['filesystem_id']) && $doc['filesystem_id'] > 0){
			require_once(ROOT . '/dataProvider/FileSystem.php');
			$FileSystem = new FileSystem();
			$filesystem_path = $FileSystem->getFileSystemPath($doc['filesystem_id']);
		}

		$file_path = rtrim($filesystem_path, '/') . '/' . trim($doc['path'], '/') . '/' . ltrim($doc['name'],'/');
		$is_file = isset($doc['path']) && $doc['path'] != '' && file_exists($file_path);

		if ($is_file) {
			$mineType = mime_content_type($file_path);
			$is_image = preg_match('/^image/', $mineType);

			$document = file_get_contents($file_path);
			$TransactionLog->saveTransactionLog([
				'event' => 'VIEW',
				'data' => 'Generated and viewed image'
			]);

		} elseif(isset($doc['document_instance']) && $doc['document_instance'] != '') {

			$mineType = get_mime_type($doc['name']);
			$is_image = preg_match('/^image/', $mineType);

			$dd = MatchaModel::setSenchaModel('App.model.administration.DocumentData', false, $doc['document_instance']);
			$data = $dd->load($doc['document_id'])->one();
			if ($data == false) {
				error_log('No Document Found For id ' . $doc['document_id']);
				die();
			}
			$data = (object)$data;
			$document = base64ToBinary($data->document, $doc['encrypted'], $is_image);
			$TransactionLog->saveTransactionLog([
				'event' => 'VIEW',
				'data' => 'Generated and viewed image'
			]);

		} else {

			$mineType = get_mime_type($doc['name']);
			$is_image = preg_match('/^image/', $mineType);

			$document = base64ToBinary($doc['document'], $doc['encrypted'], $is_image);
			$TransactionLog->saveTransactionLog([
				'event' => 'VIEW',
				'data' => 'Generated and viewed image'
			]);
		}
	}

	unset($TransactionLog);


    if($mineType == 'text/plain') {
        print '<pre>';
        if (substr($document, -1) == '=') {
            $document = base64_decode($document);
        }
        print $document;
        print '</pre>';

    } elseif ($is_image) {

		$enableEdit = isset($_SESSION['user']['auth']) && $_SESSION['user']['auth'] == true;

		if(isBinary($document)){
			$document = base64_encode($document);
		}

		if ($enableEdit) {

			$html = <<<HTML
			<!doctype html>
			<html>
				<head>
				    <meta charset="UTF-8">
				    <link rel="stylesheet" href="../lib/darkroomjs/build/darkroom.css">
				    <style>
						.btn {
						  font-family: Arial;
						  color: #ffffff;
						  font-size: 16px;
						  background: #616161;
						  padding: 5px 10px 5px 10px;
						  border: solid #3d3d3d 0px;
						  text-decoration: none;
						}
						
						.btn:hover {
						  background: #3cb0fd;
						  text-decoration: none;
						}
				    
					</style>
				</head>
				<body style="overflow: hidden; position: relative;">
					<input style="position: absolute; left:0;top:0" class="btn" type="button" value="Print" onclick="printImage(this);" />
					<input style="position: absolute; right:0;top:0" class="btn" type="button" value="Edit" onclick="enableDarkroom(this);" />
			        <div id="target-container" class="image-container target" style="left: 0; top:0;width:100%;">
				        <img style="width:100%;" alt="" id="target" crossOrigin="anonymous" src="data:{$mineType};base64,{$document}">
			        </div>
					<script src="../lib/darkroomjs/demo/vendor/fabric.js" ></script>
					<script src="../lib/darkroomjs/build/darkroom.js" ></script>
					<script>
					
						function enableDarkroom(btn){
							btn.style.display = 'none';
							var dkrm = new Darkroom('#target', {
						     	plugins: {
							        save: '{$doc['is_temp']}' == 'true' ? false : {
							            callback: function(){
					                        var msg = 'documentedit{"save":{"id":{$doc['id']},"document":"'+ dkrm.canvas.toDataURL() +'" }}';
					                        window.parent.postMessage(msg, '*');
							            }
							        }
					            }
						    });
						}
						
						function printImage(btn){
                            var w = window.open();
                            w.document.write(document.getElementById('target-container').innerHTML);
                            
                            setTimeout(function(){
                                w.print();
                                w.close();
                            }, 250);
                            
						}
				  </script>
				</body>
			</html>
HTML;
		} else {
			$html = <<<HTML
			<!doctype html>
			<html>
				<head>
				</head>
				<body>
				 	<img src="data:{$mineType};base64,{$document}" style="width:100%;" alt="" id="target" crossOrigin="anonymous">			        
				</body>
			</html>
HTML;
		}

		print $html;
	} else {
    	if($mineType === 'application/xml' || $mineType === 'text/xml'){

    		if(isset($_REQUEST['rawXml'])){

			    $document = preg_replace('/<\?xml-stylesheet (.*)\?>/', "" ,$document, 1);
			    $encoding = mb_detect_encoding($document, 'ISO-8859-1,UTF-8');
			    $encoding = $encoding === false ? '' : '; charset=' . $encoding;

			    header_remove();
			    header('Content-type: text/xml', true);
			    print ($document);

		    }else{
			    $isCcr = preg_match('/<ccr:/',$document);
			    $url = str_replace('index.php','',URL);
			    if($isCcr){
				    $href =  $url.'/lib/CCRCDA/schema/ccr.xsl';
			    }else{
				    $href =  $url.'/lib/CCRCDA/schema/cda2.xsl';
			    }

			    if(preg_match('/xml-stylesheet/', $document)){
				    $document = preg_replace('/(href=["\']).*\.xsl(["\'])/', "$1{$href}$2" ,$document, 1);
			    }else{
				    $stylesheet = "<?xml-stylesheet type=\"text/xsl\" href=\"{$href}\"?>";
				    $document = preg_replace('/(<\?xml version.*\?>)/', "$1$stylesheet" ,$document, 1);
			    }

			    $encoding = mb_detect_encoding($document, 'ISO-8859-1,UTF-8');
			    $encoding = $encoding === false ? '' : '; charset=' . $encoding;

			    header('Content-Type: ' . $mineType . $encoding, true);
			    header('Content-Disposition: inline; filename="' . $doc['name'] . '"');
			    header('Content-Transfer-Encoding: BINARY');
			    header('Content-Length: ' . strlen($document));
			    header('Accept-Ranges: bytes');
			    print $document;

		    }
	    } else {

		    $encoding = mb_detect_encoding($document, 'ISO-8859-1,UTF-8');
		    $encoding = $encoding === false ? '' : '; charset=' . $encoding;

		    header('Content-Type: ' . $mineType . $encoding, true);
		    header('Content-Disposition: inline; filename="' . $doc['name'] . '"');
		    header('Content-Transfer-Encoding: BINARY');
		    header('Content-Length: ' . strlen($document));
		    header('Accept-Ranges: bytes');
		    print $document;
	    }

	}

} else {
	print 'Not Authorized to be here, Please contact Support Desk. Thank You!';
}