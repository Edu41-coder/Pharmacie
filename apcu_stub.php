<?php
// apcu_stub.php

if (!function_exists('apcu_fetch')) {
    /**
     * Fetch a stored variable from the cache
     * @param string $key
     * @param bool &$success [optional]
     * @return mixed
     */
    function apcu_fetch($key, &$success = null) {}
}

if (!function_exists('apcu_add')) {
    /**
     * Cache a variable in the data store, only if it's not already stored
     * @param string $key
     * @param mixed $var
     * @param int $ttl [optional]
     * @return bool
     */
    function apcu_add($key, $var, $ttl = 0) {}
}