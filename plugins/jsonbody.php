<?php

    /**
     * JSON Body.
     * 
     * This plugin takes the content of the body and assumes that it is valid json, decodes it and replaces 
     * any of the original variables passed.
     * 
     * This lets you mimic payloads expected by various webhook endpoints.
     */
    class JSONBody extends Plugin {
        
        public function execute($plugin, $object, $raw) {
            
            __log("Raw JSON string passed: '{$object->description}'");
            
            $json = json_decode($object->description);
            if (!$json) {
                __log("Invalid JSON payload '$json'", 'ERROR');
                return false;
            }
            
            return $json;
            
        }
    }
