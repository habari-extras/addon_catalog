<?php

/**
 * Provides community plugin / theme directory as well as beacon update services.
 */

	class PluginServer extends Plugin
	{
		
		const VERSION = '0.1';
		
		public function info ( ) {
			
			return array(
				'name' => 'Plugin Server',
				'version' => self::VERSION,
				'url' => 'http://habariproject.org',
				'author' => 'Habari Community',
				'authorurl' => 'http://habariproject.org',
				'license' => 'Apache License 2.0',
				'description' => 'Provides plugin directory and update beacon services.',
				'copyright' => '2008'
			);
			
		}
		
		public function filter_default_rewrite_rules ( $rules ) {
	
			// put together our rule
			$rule['name'] = 'beacon_server';
			$rule['parse_regex'] = '%^beacon$%i';
			$rule['build_str'] = 'beacon';
			$rule['handler'] = 'BeaconHandler';
			$rule['action'] = 'request';
			$rule['description'] = 'Incoming Beacon Update Requests';
			
			// add our rule to the stack
			$rules[] = $rule;
			
			// and pass it along
			return $rules;
			
		}
		
		public function action_plugin_activation ( $file='' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			
				// add or activate our custom post type
				Post::add_new_type( 'plugin_directory' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file='' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			
				// @todo it has yet been decided whether or not this is a good idea - MellerTime
				/*
				// get all the posts of our update type, so we can delete them
				$posts = Posts::get( array( 'content_type' => 'plugin_directory', 'nolimit' => true ) );
				
				foreach ( $posts as $post ) {
					
					$post->delete();
					
				}
				*/
				
				// deactivate our custom post type
				Post::deactivate_post_type( 'plugin_directory' );
			
			}
			
		}
		
		public function filter_publish_controls ( $controls = array(), $post = null ) {
			
			if ( Controller::get_handler()->handler_vars['content_type'] == Post::type('plugin_directory') ) {
				
				ob_start();
				
				include( 'plugin_directory_pane.php' );
				
				$contents = ob_get_clean();
				
				$controls[ 'Plugin Details' ] = $contents;
				
				// remove the 'settings' tab, it's not needed for directory entries
				//unset( $controls[ 'Settings' ] );		@todo uncomment this when it doesn't break the publish page
				
			}
			
			return $controls;
			
		}
		
		public static function licenses ( ) {
			
			// @todo make this configurable through the plugin options - MellerTime
			
			$licenses['apache_20']= 'Apache License 2.0';
			$licenses['gpl']= 'GPL';
			$licenses['lgpl']= 'LGPL';
			$licenses['public']= 'Public Domain';
			
			return $licenses;
			
		}
		
		public function action_init ( ) {
			
			// nothing, yet
			
		}
		
		
	}
	
	class BeaconHandler extends ActionHandler {
		
		public function __construct ( ) {
			
			
		}
		
		public function act_request ( ) {
			
			/*
			 * @todo refactor this so we Posts::get() only those GUIDs requested:
			 * 			array( ... 'info:any' => array( 'guid1', 'guid2', ... ) );
			 * @todo potentially cache individual plugins seperately, or eliminate caching all together
			 */
			
			if ( Cache::has( 'plugin_directory:plugins' ) && false ) {
				
				$plugins = Cache::get( 'plugin_directory:plugins' );
				
				$from_cache = true;
				
			}
			else {
				
				// get the entire list of plugins from our directory based on their custom content type
				$plugins = Posts::get( array( 'content_type' => 'plugin_directory', 'nolimit' => true ) );
				
				$from_cache = false;
				
			}
			
			
			
			// build the xml output
			$xml = new SimpleXMLElement( '<updates></updates>' );
			
			foreach ( $plugins as $plugin ) {
				
				// only include this one if it was requested
				if ( in_array( $plugin->info->guid, array_keys( $this->handler_vars ) ) ) {
					
					// create the beacon's node
					$beacon_node = $xml->addChild( 'beacon' );
					$beacon_node->addAttribute( 'id', $plugin->info->guid );
					$beacon_node->addAttribute( 'url', $plugin->info->url );
					$beacon_node->addAttribute( 'name', $plugin->title );
					
					foreach ( array( 'critical', 'bugfix', 'feature' ) as $status ) {
						
						// does this plugin currently have one of this type?
						if ( $version = $plugin->info->$status ) {
							
							$status_content = $status . '_content';
							
							// create an update node for the beacon  with the status' message
							$update_node = $beacon_node->addChild( 'update', $plugin->info->$status_content );
							$update_node->addAttribute( 'severity', $status );
							$update_node->addAttribute( 'version', $version );
							
						}
						
					}
					
				}
				
			}
			
			Utils::debug($plugins, 'Plugins');
			
			// only cache this set of plugins if it wasn't already from the cache
			if ( $from_cache == false ) {
				Cache::set( 'plugin_directory:plugins', $plugins );
			}
			
			$xml= Plugins::filter( 'plugin_directory_beacon_xml', $xml, $this->handler_vars );
			$xml= $xml->asXML();
			
			// @todo uncomment when we're actually outputting xml again
			//ob_clean();
			//header( 'Content-Type: application/xml' );
			echo $xml;
			
		}
		
	}
	
?>