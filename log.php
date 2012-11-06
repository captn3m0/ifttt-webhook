<?php

    /**
     * Debug logging
     */


     function __log($message, $level = "NOTICE") {
         global $DEBUG;
         
         if ($DEBUG) {
             
             error_log("$level: $message");
             
         }
     }