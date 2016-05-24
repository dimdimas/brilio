<?php

$cache['cachetime_short'] = 300;
$cache['cachetime_default'] = 900;
$cache['cachetime_long'] = 1800;
$cache['cachetime_day'] = 86400;

if (BRILIO_CURRENT_VIEWPORT == 'mobile') {
	if(BRILIO_CURRENT_LANG == 'en'){
		//CACHE Mobile EN
		$cache['prefix'] = 'brilionet_m_en';

		$cache['cache_serving_order'] = array("textcache", "memcache");

		/**
		 *  -------------------------------------------------
		 *  
		 *  Memcache settings
		 *  
		 *  -------------------------------------------------
		 */
		$cache['memcache_active'] = false;
		$cache['memcache_hosts'] = array();


		/**
		 * -------------------------------------------------
		 *
		 * Text Cache settings
		 *
		 * -------------------------------------------------
		 */
		$cache['filecache_active'] = false;
		$cache['filecache_path'] = __DIR__ . '/../../admin/cache/';


		/**
		 * Used for Cache
		 * Set MongoDB configuration in config/config.php
		 * MongoDB as Priority
		 * Config automatically will be $cache['cache_serving_order'] = array("mongodb", "memcache", "textcache");
		 * */
		$cache['mongocache_active'] = false;
		$cache['mongocache_collection'] = 'brilionet_cache_m_en';
		// END OF CACHE Mobile EN
	}else{
		//CACHE Mobile ID
		$cache['prefix'] = 'brilionet_m';

		$cache['cache_serving_order'] = array("textcache", "memcache");

		/**
		 *  -------------------------------------------------
		 *  
		 *  Memcache settings
		 *  
		 *  -------------------------------------------------
		 */
		$cache['memcache_active'] = false;
		$cache['memcache_hosts'] = array();


		/**
		 * -------------------------------------------------
		 *
		 * Text Cache settings
		 *
		 * -------------------------------------------------
		 */
		$cache['filecache_active'] = false;
		$cache['filecache_path'] = __DIR__ . '/../../admin/cache/';


		/**
		 * Used for Cache
		 * Set MongoDB configuration in config/config.php
		 * MongoDB as Priority
		 * Config automatically will be $cache['cache_serving_order'] = array("mongodb", "memcache", "textcache");
		 * */
		$cache['mongocache_active'] = false;
		$cache['mongocache_collection'] = 'brilionet_cache_m';
		//END OF CACHE Mobile ID
	}
}else{
	if(BRILIO_CURRENT_LANG == 'en'){
		//CACHE DESKTOP EN
		$cache['prefix'] = 'brilionet_en';

		$cache['cache_serving_order'] = array("textcache", "memcache");

		/**
		 *  -------------------------------------------------
		 *  
		 *  Memcache settings
		 *  
		 *  -------------------------------------------------
		 */
		$cache['memcache_active'] = false;
		$cache['memcache_hosts'] = array();


		/**
		 * -------------------------------------------------
		 *
		 * Text Cache settings
		 *
		 * -------------------------------------------------
		 */
		$cache['filecache_active'] = false;
		$cache['filecache_path'] = __DIR__ . '/../../admin/cache/';


		/**
		 * Used for Cache
		 * Set MongoDB configuration in config/config.php
		 * MongoDB as Priority
		 * Config automatically will be $cache['cache_serving_order'] = array("mongodb", "memcache", "textcache");
		 * */
		$cache['mongocache_active'] = false;
		$cache['mongocache_collection'] = 'brilionet_cache_en';
		// END OF CACHE DESKTOP EN
	}else{
		//CACHE DESKTOP ID
		$cache['prefix'] = 'brilionet_';

		$cache['cache_serving_order'] = array("textcache", "memcache");

		/**
		 *  -------------------------------------------------
		 *  
		 *  Memcache settings
		 *  
		 *  -------------------------------------------------
		 */
		$cache['memcache_active'] = false;
		$cache['memcache_hosts'] = array();


		/**
		 * -------------------------------------------------
		 *
		 * Text Cache settings
		 *
		 * -------------------------------------------------
		 */
		$cache['filecache_active'] = false;
		$cache['filecache_path'] = __DIR__ . '/../../admin/cache/';


		/**
		 * Used for Cache
		 * Set MongoDB configuration in config/config.php
		 * MongoDB as Priority
		 * Config automatically will be $cache['cache_serving_order'] = array("mongodb", "memcache", "textcache");
		 * */
		$cache['mongocache_active'] = false;
		$cache['mongocache_collection'] = 'brilionet_cache';
		//END OF CACHE DESKTOP ID
	}
}
