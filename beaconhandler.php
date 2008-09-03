<?php

class BeaconHandler extends ActionHandler {
		
		public function __construct ( ) {
			
			
		}
		
		public function act_request ( ) {
			
			/*
			 * @todo refactor this so we Posts::get() only those GUIDs requested:
			 * 			array( ... 'info:any' => array( 'guid1', 'guid2', ... ) );
			 * @todo potentially cache individual plugins seperately, or eliminate caching all together
			 * 
			 * @todo check against the versioin passed with guid, to only output updated version info.
			 */
			
			if ( Cache::has( 'plugin_directory:plugins' ) && false ) {
				
				$plugins = Cache::get( 'plugin_directory:plugins' );
				
				$from_cache = true;
				
			}
			else {
				
				// get the entire list of plugins from our directory based on their custom content type
				$plugins = Posts::get( array( 'content_type' => 'plugin', 'nolimit' => true ) );
				
				$from_cache = false;
				
			}
			
			
			
			// build the xml output
			$xml = new SimpleXMLElement( '<updates></updates>' );
			
			foreach ( $plugins as $plugin ) {
				if ( !$plugin->versions ) {
					continue;
				}
				
				// create the beacon's node
				$beacon_node = $xml->addChild( 'beacon' );
				$beacon_node->addAttribute( 'id', $plugin->info->guid );
				$beacon_node->addAttribute( 'name', $plugin->title );
				
				foreach ( $plugin->versions as $version ) {
					// create an update node for the beacon  with the status' message
					$update_node = $beacon_node->addChild( 'update', $version->description );
					$update_node->addAttribute( 'severity', $version->status );
					$update_node->addAttribute( 'version', $version->version );
					$update_node->addAttribute( 'habari_version', $version->habari_version );
					$update_node->addAttribute( 'url', $version->url );
				}
			}
			
			//Utils::debug($plugins, 'Plugins');
			
			// only cache this set of plugins if it wasn't already from the cache
			if ( $from_cache == false ) {
				Cache::set( 'plugin_directory:plugins', $plugins );
			}
			
			$xml= Plugins::filter( 'plugin_directory_beacon_xml', $xml, $this->handler_vars );
			$xml= $xml->asXML();
			
			// @todo uncomment when we're actually outputting xml again
			ob_clean();
			header( 'Content-Type: application/xml' );
			echo $xml;
			
		}
		
	}
?>
