<?php

/**
 * Provides community plugin / theme directory as well as beacon update services.
 */

require 'beaconhandler.php';
require 'pluginrepo.php';

class PluginServer extends Plugin
{
	private $info_fields = array(
		'url',
		'guid',
		'author',
		'author_url',
		'license',
		'screenshot'
	);

	private $version_fields = array(
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

	const VERSION = '0.2alpha';

	public function info() {

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

	public function filter_default_rewrite_rules( $rules ) {

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
		$rule['name'] = 'display_plugin';
		$rule['parse_regex'] = '%^explore/plugins/(?P<slug>.+)/?$%';
		$rule['build_str'] = 'explore/plugins/{$slug}';
		$rule['handler'] = 'UserThemeHandler';
		$rule['action'] = 'display_plugin';
		$rule['priority'] = 3;
		$rule['description'] = 'Plugin Repo Server Browser';

		// add our rule to the stack
		$rules[] = $rule;
		
		// put together our rule
		$rule['name'] = 'display_plugins';
		$rule['parse_regex'] = '%^explore/plugins(?:/page/(?P<page>\d+))?/?$%';
		$rule['build_str'] = 'explore/plugins(/page/{$page})';
		$rule['handler'] = 'UserThemeHandler';
		$rule['action'] = 'display_plugins';
		$rule['priority'] = 2;
		$rule['description'] = 'Plugin Repo Server Browser';

		// add our rule to the stack
		$rules[] = $rule;
		
		// and pass it along
		return $rules;

	}
	
	/**
	 * @ todo make uoe own template for these
	 */
	public function filter_theme_act_display_plugins( $handled, $theme )
	{
		$paramarray['fallback']= array(
		 	'plugin.multiple',
			'multiple',
		);

		// Makes sure home displays only entries
		$default_filters = array(
			'content_type' => Post::type( 'plugin' ),
		);

		$paramarray['user_filters']= $default_filters;

		$theme->act_display( $paramarray );
		return true;
	}
	
	public function filter_theme_act_display_plugin( $handled, $theme )
	{
		$default_filters = array(
			'content_type' => Post::type( 'plugin' ),
		);
		$theme->act_display_post( $default_filters );
		return true;
	}

	public function action_plugin_activation ( $file ='' ) {

		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

			// add or activate our custom post type
			Post::add_new_type( 'plugin' );

			DB::register_table( 'plugin_versions' );

			// Create the database table, or upgrade it
			DB::dbdelta( $this->get_db_schema() );

			Session::notice( _t( 'updated plugin_versions table', 'plugin_directory' ) );
		}

	}

	public function action_plugin_deactivation( $file ='' ) {

		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

			// @todo it has yet been decided whether or not this is a good idea - MellerTime
			/*
			// get all the posts of our update type, so we can delete them
			$posts = Posts::get( array( 'content_type' => 'plugin', 'nolimit' => true ) );

			foreach ( $posts as $post ) {

				$post->delete();

			}
			*/

			// deactivate our custom post type
			Post::deactivate_post_type( 'plugin' );

		}

	}
	
	public function action_auth_ajax_generate_guid( $handler )
	{
		echo UUID::get();
	}
	
	/**
	 *Manipulate the controls on the publish page
	 *
	 *@param FormUI $form The form that is used on the publish page
	 *@param Post $post The post being edited
	 **/
	public function action_form_publish($form, $post)
	{
		if ( $form->content_type->value == Post::type('plugin') ) {
			// remove silos we don't need them, do we?
			$form->remove($form->silos);
			
			// add guid after title
			$guid = $form->append('text', 'plugin_details_guid', 'null:null', 'GUID');
			$guid->value = $post->info->guid;
			$guid->template = ($post->slug) ? 'admincontrol_text' : 'guidcontrol';
			$form->move_after($form->plugin_details_guid, $form->title);
				
			// todo Remove the settings tab, as it's not needed
			$plugin_details = array(
				'url' => $post->info->url,
				'screenshot' => $post->info->screenshot,
				'author' => $post->info->author,
				'author_url' => $post->info->author_url,
				'license' => $post->info->license
			);

			$plugin_fields = $form->publish_controls->append('fieldset', 'plugin_details', 'Plugin Details');

			foreach ( $plugin_details as $field => $value ) {
				$plugin_field = $plugin_fields->append('text', 'plugin_details_' . $field, 'null:null', ucfirst(str_replace('_', ' ', $field)));
				$plugin_field->value = $value;
				$plugin_field->template = 'tabcontrol_text';
			}
			
			$plugin_versions = $form->publish_controls->append('fieldset', 'plugin_versions', 'Plugin Versions');
			if ( $post->slug != '' ) {
				$form->plugin_versions->append('static', 'current_versions', 'Current Versions');
				foreach ( (array) $post->versions as $version ) {
					$version_info = $version->status . ": " . $post->title . " " . $version->version . " -- " . $version->description;
					$plugin_versions->append('static', 'version_info', $version_info);
				}
			}

			$form->plugin_versions->append('static', 'new_version', 'Add New Version');
			$version = $plugin_versions->append('text', 'plugin_version_version', 'null:null', _t( 'Version Number' ));
			$version->template = 'tabcontrol_text';
			$description = $plugin_versions->append('text', 'plugin_version_description', 'null:null', _t( 'Version Description' ));
			$description->template = 'tabcontrol_text';
			$url = $plugin_versions->append('text', 'plugin_version_url', 'null:null', _t( 'Archive URL' ));
			$url->template = 'tabcontrol_text';
			$habari_version = $plugin_versions->append('text', 'plugin_version_habari_version', 'null:null', _t( 'Compatible Habari Version <br> ("x" is a wildcard, eg. 0.5.x)' ));
			$habari_version->template = 'tabcontrol_text';

			$status = $plugin_versions->append( 'select', 'plugin_version_status', 'null:null', 'Status');
			$status->template = 'tabcontrol_select';
			$status->options = array(
				'release' => 'Release',
				'critical' => 'Critical',
				'bugfix' => 'Bugfix',
				'feature' => 'Feature',
				);

			$requires = $plugin_versions->append('text', 'plugin_version_requires', 'null:null', _t( 'Requires' ));
			$requires->template = 'tabcontrol_text';
			$provides = $plugin_versions->append('text', 'plugin_version_provides', 'null:null', _t( 'Provides' ));
			$provides->template = 'tabcontrol_text';
			$recommends = $plugin_versions->append('text', 'plugin_version_recommends', 'null:null', _t( 'Recommends' ));
			$recommends->template = 'tabcontrol_text';
		}
	}

	public function action_publish_post( $post, $form )
	{
		if ( $post->content_type == Post::type('plugin') ) {
			foreach ( $this->info_fields as $info_field ) {
				if ( $form->{"plugin_details_$info_field"}->value ) {
					$post->info->{$info_field} = $form->{"plugin_details_$info_field"}->value;
				}
			}
			
			$this->save_versions( $post, $form );
		}
	}

	/**
		* @todo check for required inputs
		*/
	public function save_versions( $post, $form )
	{
		if ( $form->plugin_version_version->value ) {
			$version_vals = array();
			foreach ( $this->version_fields as $version_field ) {
				if ( $form->{"plugin_version_$version_field"} ) {
					$version_vals[$version_field] = $form->{"plugin_version_$version_field"}->value;
				}
				else {
					$version_vals[$version_field] = '';
				}
			}
			$version_vals['post_id'] = $post->id;
			$version_vals['md5'] = $this->get_version_md5( $version_vals['url'] );
			
			DB::update(
				DB::table( 'plugin_versions' ),
				$version_vals,
				array( 'version' => $version_vals['version'], 'post_id' => $post->id )
			);
			
			Session::notice( 'Added version number ' . $version_vals['version'] );
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

	public static function licenses()
	{

		// @todo make this configurable through the plugin options - MellerTime

		$licenses['apache_20']= 'Apache License 2.0';
		$licenses['gpl']= 'GPL';
		$licenses['lgpl']= 'LGPL';
		$licenses['public']= 'Public Domain';

		return $licenses;

	}

	public function action_init()
	{

		DB::register_table( 'plugin_versions' );
		$this->add_template( 'plugin.multiple', dirname(__FILE__) . '/templates/plugin.multiple.php' );
		$this->add_template( 'plugin.single', dirname(__FILE__) . '/templates/plugin.single.php' );
		$this->add_template( 'guidcontrol', dirname(__FILE__) . '/templates/guidcontrol.php' );
	}

}

?>
