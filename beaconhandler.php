<?php

	class BeaconHandler extends ActionHandler {
		
		public function __construct ( ) {
			
			
		}
		
		public function act_request ( ) {
			
			// @todo limit this to GUIDs POST'd
			$plugins = Posts::get( array( 'content_type' => 'addon', 'nolimit' => true, 'status' => Post::status('published') ) );
			
			$xml = new SimpleXMLElement( '<updates></updates>' );
			
			foreach ( $plugins as $plugin ) {
				
				// if we don't have any versions, skip this plugin
				if ( empty( $plugin->info->versions ) ) {
					//continue;
				}
				
				// create the beacon's node
				$beacon = $xml->addChild( 'beacon' );
				$beacon['id'] = $plugin->info->guid;
				$beacon['name'] = $plugin->title;
				$beacon['url'] = $plugin->permalink;
				$beacon['type'] = $plugin->info->type;
				
				foreach ( $plugin->info->versions as $version ) {
					
					// @todo limit this to only versions older than the one POST'd
					$update = $beacon->addChild( 'update', $version['description'] );
					$update['severity'] = $version['severity'];
					$update['version'] = $version['version'];
					$update['habari_version'] = $version['habari_version'];
					$update['url'] = $version['url'];
					//$update['date'] = $version->date;
					
				}
				
			}
			
			// spit out the xml
			ob_clean();		// clean the output buffer
			header( 'Content-type: application/xml' );
			echo $xml->asXML();
			
		}
		
	}

?>