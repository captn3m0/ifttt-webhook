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
        if (!$plugins[0] == 'plugin') return $object;
            
        $plugin = trim($plugins[1]);
        $plugin = preg_replace("/[^a-zA-Z0-9\s]/", "", $plugin);
        
        $file = strtolower($plugin);
        
        require_once(dirname(__FILE__) . "/$file.php");
        
        $plugin_class = new $plugin();
        
        if ($plugin_class) return $plugin_class->execute ($plugin, $object, $raw);
        
        return false;
    }
    