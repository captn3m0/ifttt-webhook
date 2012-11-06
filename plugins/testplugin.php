<?php

    class TestPlugin extends Plugin {
        
        public function execute($plugin, $object, $raw) {
            
            error_log("Plugin: " . $plugin);
            error_log("Object:" . print_r($object, true));
            error_log("Raw: $raw");
            
            return $object;
        }
    }
