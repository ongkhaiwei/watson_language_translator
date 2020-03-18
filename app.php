<?php

// Configuration

$WATSON_URL = 'YOUR_WATSON_API_ENDPOINT';
$WATSON_VERSION = '?version=2018-05-01';
$APIKEY = 'YOUR_API_KEY';
$MODEL_ID = 'en-th';
$FILENAME = 'FULL_PATH_OF_FILE';

// START - Upload document to IBM Cloud Watson Language Translator

// Prepare file and CURL Header

$mime = mime_content_type($FILENAME);
$info = pathinfo($FILENAME);
$name = $info['basename'];
$output = new CURLFile($FILENAME, $mime, $name);

$data = array(
  "file" => $output,
  "model_id" => $MODEL_ID
);

$ch = curl_init($WATSON_URL.'/v3/documents'.$WATSON_VERSION);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_USERPWD, 'apikey:'.$APIKEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec ($ch);
curl_close ($ch);

// Extract document_id for later use

$document_status = json_decode($result);
$document_id = $document_status->document_id;

// END - Upload document to IBM Cloud Watson Language Translator

// START - Check document processing status based on document_id

$ch = curl_init($WATSON_URL.'/v3/documents/'.$document_id.$WATSON_VERSION);
//$ch2 = curl_init('https://api.jp-tok.language-translator.watson.cloud.ibm.com/instances/6ed3de8a-4caa-4688-8117-2bb2ab2f5d43/v3/documents/'.$document_id.'?version=2018-05-01');
curl_setopt($ch, CURLOPT_USERPWD, 'apikey:'.$APIKEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$counter = 0;

// If the file is still in processing status, wait for 5 seconds and re-check status.
echo 'Processing. Please wait ';

do {
  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    $result = curl_error($ch);
  };
  $document_status = json_decode($result);
  if($document_status->status == 'available') {
    break;
  }
  echo '.';
  sleep(5);
  $counter++;
} 
while($document_status->status != 'available');
curl_close ($ch);

echo 'IBM Cloud Watson Language Translator processed in '.($counter*5).'seconds'.

$fp = fopen (dirname(__FILE__) . '/output_'.$FILENAME, 'w+');

// END - Check document processing status based on document_id 

// START - Download translated document

$ch = curl_init($WATSON_URL.'/v3/documents/'.$document_id.'/translated_document'.$WATSON_VERSION);
curl_setopt($ch, CURLOPT_USERPWD, 'apikey:'.$APIKEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$result = curl_exec($ch);
curl_close ($ch);
fclose($fp);

echo 'File Download Completed'

// END - Download translated document

?>