<?php

require_once(dirname(__FILE__) . '/settings.php');

error_reporting(-1);
ini_set('display_errors', 1);
$request_body = file_get_contents('php://input');
$xml = simplexml_load_string($request_body);

// Plugin?
$__PLUGIN = null;

if (!$xml) die ("Ooops! No XML Payload: You possibly want to <a href=\"index.php\">read the documentation!</a>");

switch ($xml->methodName) {

    //wordpress blog verification
    case 'mt.supportedMethods':
        success('metaWeblog.getRecentPosts');
        break;
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
        $obj->user = (string) $xml->params->param[1]->value->string;
        $obj->pass = (string) $xml->params->param[2]->value->string;

        //@see content in the wordpress docs
        $content = $xml->params->param[3]->value->struct->member;
        foreach ($content as $data) {
            switch ((string) $data->name) {
                //we use the tags field for providing webhook URL
                case 'mt_keywords':
                    $url = $data->xpath('value/array/data/value/string');
                    $url = (string) $url[0];
                    break;

                //the passed categories are parsed into an array
                case 'categories':
                    $categories = array();
                    foreach ($data->xpath('value/array/data/value/string') as $cat)
                        array_push($categories, (string) $cat);
                    $obj->categories = $categories;
                    break;

                //this is used for title/description
                default:
                    $obj->{$data->name} = (string) $data->value->string;
            }
        }

        // Plugin details
        if ($ALLOW_PLUGINS) {
            
            foreach ($obj->categories as $category) {
                if (strpos($category, 'plugin:') !== false)
                        $__PLUGIN = $category;
            }
            
            // If we allow plugins, pass the constructed object to 
            $obj = executePlugin($__PLUGIN, $obj, $content);
        }
        
        //Make the webrequest
        //Only if we have a valid url
        if (valid_url($url, true)) {
            // Load Requests Library
            include('requests/Requests.php');
            Requests::register_autoloader();

            $headers = array('Content-Type' => 'application/json');
            $response = Requests::post($url, $headers, json_encode($obj));

            if ($response->success)
                success('<string>' . $response->status_code . '</string>');
            else
                failure($response->status_code);
        }
        else {
            //since the url was invalid, we return 400 (Bad Request)
            failure(400);
        }
}

/** Copied from wordpress */
function success($innerXML) {
    $xml = <<<EOD
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

function output($xml) {
    $length = strlen($xml);
    header('Connection: close');
    header('Content-Length: ' . $length);
    header('Content-Type: text/xml');
    header('Date: ' . date('r'));
    echo $xml;
    exit;
}

function failure($status) {
    $xml = <<<EOD
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

/** Used from drupal */
function valid_url($url, $absolute = FALSE) {
    if ($absolute) {
        return (bool) preg_match("
      /^                                                      # Start at the beginning of the text
      (?:https?):\/\/                                # Look for ftp, http, https or feed schemes
      (?:                                                     # Userinfo (optional) which is typically
        (?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*      # a username or a username and password
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@          # combination
      )?
      (?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+                        # A domain name or a IPv4 address
        |(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])         # or a well formed IPv6 address
      )
      (?::[0-9]+)?                                            # Server port number (optional)
      (?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})   # The path and query (optional)
      *)?
    $/xi", $url);
    } else {
        return (bool) preg_match("/^(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $url);
    }
}