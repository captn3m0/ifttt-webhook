<?php
$request_body = file_get_contents('php://input');
$xml = simplexml_load_string($request_body);

switch($xml->methodName)
{
	//first authentication request from ifttt
	case 'metaWeblog.getRecentPosts':
		//send a blank blog response
		//this also makes sure that the channel is never triggered
		success('<array><data></data></array>');
		break;

	case 'metaWeblog.newPost':
		//@see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.newPost
		$obj = new stdClass;
		//get the parameters from xml
		$obj->user = (string)$xml->params->param[1]->value->string;
		$obj->pass = (string)$xml->params->param[2]->value->string;

		//@see content in the wordpress docs
		$content = $xml->params->param[3]->value->struct->member;
		foreach($content as $data)
		{
			switch((string)$data->name)
			{
				//we use the tags field for providing webhook URL
				case 'mt_keywords':
					$url = $data->xpath('value/array/data/value/string');
					$url = (string)$url[0];
					break;

				//the passed categories are parsed into an array
				case 'categories':
					$categories=array();
					foreach($data->xpath('value/array/data/value/string') as $cat)
						array_push($categories,(string)$cat);
					$obj->categories = $categories;
					break;

				//this is used for title/description
				default:
					$obj->{$data->name} = (string)$data->value->string;
			}
		}

		//Make the webrequest
		//Only if we have a valid url
		if(filter_var($url, FILTER_VALIDATE_URL))
		{
			// Load Requests Library
			include('requests/Requests.php');
			Requests::register_autoloader();

			$headers = array('Content-Type' => 'application/json');
			$response = Requests::post($url, $headers, json_encode($obj));

			if($response->success)
				success('<string>1</string>');
			else
				failure($response->status_code);
		}
		else
		{
			//since the url was invalid, we return 400 (Bad Request)
			failure(400);
		}
		
}

/** Copied from wordpress */

function success($innerXML)
{
	$xml =  <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value>
      $innerXML
      </value>
    </param>
  </params>
</methodResponse>

EOD;
	output($xml);
}

function output($xml){
	$length = strlen($xml);
	header('Connection: close');
	header('Content-Length: '.$length);
	header('Content-Type: text/xml');
	header('Date: '.date('r'));
	echo $xml;
	exit;
}

function failure($status){
$xml= <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>$status</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>Request was not successful.</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
output($xml);
}
