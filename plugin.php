<?php

    /**
     * Plugin superclass.
     */
    abstract class Plugin {
        
        abstract function execute($plugin, $object, $raw);
    }

    /**
     * Execute a plugin
     * @param type $plugin
     * @param type $object
     * @param type $raw 
     * @return stdClass
     */
    function executePlugin($plugin, $object, $raw) {
        global $ALLOW_PLUGINS;
        
        if (!$ALLOW_PLUGINS) return $object;
        
        $plugins = explode(':', $plugin);
        if (!$plugins[0] == 'plugin') return false;
            
        $plugin = trim($plugins[1]);
        $plugin = preg_replace("/[^a-zA-Z0-9\s]/", "", $plugin);
        
        $file = strtolower($plugin);
        
        if (!file_exists(dirname(__FILE__) . "/plugins/$file.php")) {
            __log("Plugin file $file.php could not be located");
            return false;
        }
        
        require_once(dirname(__FILE__) . "/plugins/$file.php");
        
        $plugin_class = new $plugin();
        
        if ($plugin_class) {
            __log("Plugin $plugin triggered");
            
            return $plugin_class->execute ($plugin, $object, $raw);
        }
        
        __log("Plugin is invalid");
        
        return false;
    }
    