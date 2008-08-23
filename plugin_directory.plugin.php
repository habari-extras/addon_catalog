<?php

/**
 * Provides community plugin / theme directory as well as beacon update services.
 */

require 'beaconhandler.php';
require 'pluginrepo.php';

	class PluginServer extends Plugin
	{
		private $info_feilds = array(
			'url',
			'guid',
			'author',
			'author_url',
			'license',
			'screenshot'
			);
		
		private $version_feilds = array(
			'post_id',
			'description',
			'url',
			'version',
			'md5',
			'status',
			'habari_version',
			'requires',
			'provides',
			'recomends'
			);
		
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
			
			// put together our rule
			$rule['name'] = 'repo_server';
			$rule['parse_regex'] = '%^packages[/]?$%i';
			$rule['build_str'] = 'packages';
			$rule['handler'] = 'PluginRepo';
			$rule['action'] = 'packages';
			$rule['description'] = 'Plugin Repo Server';
			
			// add our rule to the stack
			$rules[] = $rule;
			
			// put together our rule
			$rule['name'] = 'repo_browser';
			$rule['parse_regex'] = '%^packages/browse(?:page/(?P<page>[2-9]|[1-9][0-9]+))?/?$%';
			$rule['build_str'] = 'packages/browse';
			$rule['handler'] = 'UserThemeHandler';
			$rule['action'] = 'display_packages';
			$rule['priority'] = 3;
			$rule['description'] = 'Plugin Repo Server Browser';
			
			// add our rule to the stack
			$rules[] = $rule;
			
			// and pass it along
			return $rules;
			
		}
		
		public function filter_theme_act_display_packages( $handled, $theme )
		{
			$theme->posts = Posts::get( array( 'content_type' => 'plugin_directory', 'limit' => 20 ) );
			$theme->display( 'packages' );
			return true;
		}
		
		public function action_plugin_activation ( $file='' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			
				// add or activate our custom post type
				Post::add_new_type( 'plugin_directory' );

				DB::register_table( 'plugin_versions' );
				// Create the database table, or upgrade it
				DB::dbdelta( $this->get_db_schema() );

				Session::notice( _t( 'updated plugin_versions table', 'plugins_directory' ) );
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
				
				ob_start();
				$version_feilds = $this->version_feilds;
				include 'plugin_version_pane.php';
				$contents = ob_get_clean();
				$controls[ 'Plugin Versions' ] = $contents;
				
				// remove the 'settings' tab, it's not needed for directory entries
				// unset( $controls[ 'Settings' ] );		// @todo uncomment this when it doesn't break the publish page
				
			}
			
			return $controls;
			
		}
		
		public function action_post_insert_before( $post )
		{
			$this->action_post_update_before( $post );
		}
		
		public function action_post_update_before( $post )
		{
			if ( $post->content_type == Post::type('plugin_directory') ) {
				foreach ( $this->info_feilds as $info_feild ) {
					if ( Controller::get_var( 'plugin_details_' . $info_feild ) ) {
						$post->info->{$info_feild} = Controller::get_var( 'plugin_details_' . $info_feild );
					}
				}

				$this->save_versions( $post );
			}
		}
		
		/**
		 * @todo check for required inputs
		 */
		public function save_versions( $post )
		{
			$plugin_version = Controller::get_var( 'plugin_version' );
			if ( !empty( $plugin_version['version'] ) ) {
				$plugin_version['post_id'] = $post->id;
				$plugin_version['md5'] = $this->get_version_md5( $plugin_version['url'] );
				$version_vals = array();
				foreach ( $this->version_feilds as $version_feild ) {
					$version_vals[$version_feild] = $plugin_version[$version_feild];
				}
				Session::notice( 'we made it!' );

				DB::update(
					DB::table( 'plugin_versions' ),
					$version_vals,
					array( 'version' => $version_vals['version'], 'post_id' => $post->id )
					);
			}
		}
		
		public function get_version_md5( $url )
		{
			$file = RemoteRequest::get_contents( $url );
			return md5( $file );
		}
		
		public function filter_post_versions( $versions, $post )
		{
			return DB::get_results( 'SELECT * FROM {plugin_versions} WHERE post_id = ?', array( $post->id ) );
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
			
			DB::register_table( 'plugin_versions' );
			$this->add_template( 'packages', dirname(__FILE__) . '/packages.php' );		
		}
		
		
	}
	
?>
