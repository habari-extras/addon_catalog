<?php

	include('beaconhandler.php');

	class PluginDirectory extends Plugin {

		// @todo these should be handled in plugin config ui
		private $api_keys = array(
			'braGecezu4rUsephap6Tu5ebabu4ecay6wustUj2che3e4ruprahuruStuspe8ut',		// -extras hook
			'9eFazezu4utRECHEStutUC4eQeGeVEbrUCRuGadahu7TUxEB5esWuvEheGEdavAM',		// unused
			'sPUsEphecApRewrECheJUswaqephujuyEwRAbetUbracHaj7cREcraDRuqUjAswu',		// unused
			'kephucuwrudruthePeswubRukuzabajafrejatrefu4u8wefu5uwrej8dar5dreb',		// unused
			'swetaphutraphecr6betreThe8uv2bupebuxu572ethejus9zuyawruprefr9chu',		// unused
		);

		private $addon_fields = array(
			'guid',
			'description', /* shorter description of the plugin, such as the line from the pluggable xml */
			'instructions',
			'type',
			'url',
			'screenshot',
			'author',
			'author_url',
			'license',
		);

		// fields defined on the license publish form
		private $license_fields = array(
			'shortname',
			'simpletext',
			'url',
		);

		// fields each version should have
		private $version_fields = array(
			'version',
			'release', /* release date */
			'description',
			'info_url',
			'url', /* download url */
			'habari_version',
			'severity',
			'requires',
			'provides',
			'recommends',
		);

		private static $vocabulary = "Addon versions";
		protected $_vocabulary;

		public function __get( $name ) {
			switch( $name ) {
				case 'vocabulary':
					if ( !isset( $this->_vocabulary ) ) {
						$this->_vocabulary = Vocabulary::get( self::$vocabulary );
					}
					return $this->_vocabulary;
			}
		}

		public function action_plugin_activation ( $file ) {

			// add the new content types
			Post::add_new_type( 'addon' );
			Post::add_new_type( 'license' );

			// allow reading the new content types
			UserGroup::get_by_name( 'anonymous' )->grant( 'post_addon', 'read' );
			UserGroup::get_by_name( 'anonymous' )->grant( 'post_license', 'read' );

			// create the addon vocabulary (type)
			Vocabulary::add_object_type( 'addon' );

			// create the addon vocabulary
			$params = array(
				'name' => self::$vocabulary,
				'description' => _t( 'A vocabulary for addon versions in the addons directory', 'plugin_directory' ),
				);
			$vocabulary = Vocabulary::create( $params );
			// @TODO: notification/log of some sort?

			// create the default content
			$this->create_default_content();
		}

		private function create_default_content ( ) {

			$habari_addon = Posts::get( array( 'content_type' => 'addon', 'slug' => 'habari' ) );

			if ( count( $habari_addon ) == 0 ) {
				$habari = Post::create( array(
					'content_type' => Post::type( 'addon' ),
					'title' => 'Habari',
					'content' => file_get_contents( dirname( __FILE__ ) . '/addon.habari.txt' ),
					'status' => Post::status('published'),
					'tags' => array( 'habari' ),
					'pubdate' => HabariDateTime::date_create(),
					'user_id' => User::identify()->id,
					'slug' => 'habari',
				) );

				$habari->info->guid = '7a0313be-d8e3-11db-8314-0800200c9a66';
				$habari->info->url = 'http://habariproject.org';
				$habari->info->author = 'The Habari Community';
				$habari->info->author_url = 'http://habariproject.org';
				$habari->info->license = 'asl2';
				$habari->info->type = 'core';
				$habari->info->commit();

				$versions = array(
					'0.6' => array(
						'version' => '0.6',
						'description' => 'Adds ACL support, increases SQLite performance, improves support for the HiEngine template engine, and improves UTF8 support.',
						'info_url' => 'http://wiki.habariproject.org/en/Releases/0.6',
						'url' => 'http://habariproject.org/dist/habari-0.6.zip',
						'habari_version' => '0.6',
						'severity' => 'feature',
						'requires' => '',
						'provides' => '',
						'recommends' => '',
						'release' => HabariDateTime::date_create('2009-04-06')->sql,
					),
					'0.6.6' => array(
						'version' => '0.6.6',
						'description' => 'Fixes three potential security exploits and corrects known bugs in the Habari Silo.',
						'info_url' => 'http://wiki.habariproject.org/en/Releases/0.6.6',
						'url' => 'http://habariproject.org/dist/habari-0.6.6.zip',
						'habari_version' => '0.6',
						'severity' => 'security',
						'requires' => '',
						'provides' => '',
						'recommends' => '',
						'release' => HabariDateTime::date_create('2010-12-04')->sql,
					),
					'0.7' => array(
						'version' => '0.7',
						'description' => 'The bestest release ever!',
						'info_url' => 'http://wiki.habariproject.org/en/Releases/0.7',
						'url' => 'http://habariproject.org/dist/habari-0.7.zip',
						'habari_version' => '0.7',
						'severity' => 'feature',
						'requires' => '',
						'provides' => '',
						'recommends' => '',
						'release' => HabariDateTime::date_create('2011-04-01')->sql,
					),
					'0.7.1' => array(
						'version' => '0.7.1',
						'description' => 'Improvements upon the bestest release ever!',
						'info_url' => 'http://wiki.habariproject.org/en/Releases/0.7.1',
						'url' => 'http://habariproject.org/dist/habari-0.7.1.zip',
						'habari_version' => '0.7',
						'severity' => 'feature',
						'requires' => '',
						'provides' => '',
						'recommends' => '',
						'release' => HabariDateTime::date_create('2011-05-12')->sql,
					),
				);
				$this->save_versions( $habari, $versions );
			}

			$apache_license = Posts::get( array( 'content_type' => 'license', 'slug' => 'asl2' ) );

			if ( count( $apache_license ) == 0 ) {
				$asl2 = Post::create( array(
					'content_type' => Post::type( 'license' ),
					'title' => 'Apache Software License, version 2.0',
					'content' => file_get_contents( dirname( __FILE__ ) . '/license.asl2.description.txt' ),
					'status' => Post::status('published'),
					'pubdate' => HabariDateTime::date_create(),
					'user_id' => User::identify()->id,
					'slug' => 'asl2',
				) );

				$asl2->info->simpletext = file_get_contents( dirname( __FILE__ ) . '/license.asl2.txt' );
				$asl2->info->shortname = 'asl2';
				$asl2->info->url = 'http://www.apache.org/licenses/LICENSE-2.0';

				$asl2->info->commit();

				// assume that if there is no apache post there are no other licenses either
				$bsd = Post::create( array(
					'content_type' => Post::type( 'license' ),
					'title' => 'New BSD',
					'content' => file_get_contents( dirname( __FILE__ ) . '/license.bsd.description.txt' ),
					'status' => Post::status('published'),
					'pubdate' => HabariDateTime::date_create(),
					'user_id' => User::identify()->id,
					'slug' => 'bsd',
				) );

				$bsd->info->simpletext = file_get_contents( dirname( __FILE__ ) . '/license.bsd.txt' );
				$bsd->info->shortname = 'bsd';
				$bsd->info->url = 'http://opensource.org/licenses/bsd-license.php';

				$bsd->info->commit();

				$mit = Post::create( array(
					'content_type' => Post::type( 'license' ),
					'title' => 'MIT',
					'content' => file_get_contents( dirname( __FILE__ ) . '/license.mit.description.txt' ),
					'status' => Post::status('published'),
					'pubdate' => HabariDateTime::date_create(),
					'user_id' => User::identify()->id,
					'slug' => 'mit',
				) );

				$mit->info->simpletext = file_get_contents( dirname( __FILE__ ) . '/license.mit.txt' );
				$mit->info->shortname = 'mit';
				$mit->info->url = 'http://opensource.org/licenses/mit-license.php';

				$mit->info->commit();

			}
		}

		public function action_plugin_deactivation ( $file ) {

			// when deactivating, don't destroy data, just turn it 'off'
			Post::deactivate_post_type( 'addon' );
			Post::deactivate_post_type( 'license' );

		}

		public function filter_post_type_display ( $type, $plurality ) {

			if ( $type == 'addon' ) {

				if ( $plurality == 'singular' ) {
					$type = _t('Addon', 'plugin_directory');
				}
				else {
					$type = _t('Addons', 'plugin_directory');
				}

			}

			if ( $type == 'license' ) {

				if ( $plurality == 'singular' ) {
					$type = _t('License', 'plugin_directory');
				}
				else {
					$type = _t('Licenses', 'plugin_directory');
				}

			}

			return $type;

		}

		public function filter_plugin_config ( $actions, $plugin_id ) {

			// we don't use the magic configure() method because then it gets placed below custom actions in the dropbutton
			$actions['configure'] = _t('Configure', 'plugin_directory');
			$actions['uninstall'] = _t('Uninstall', 'plugin_directory');

			return $actions;

		}

		public function action_plugin_ui_uninstall ( ) {

			// get all the posts of the types we're deleting
			$addons = Posts::get( array( 'content_type' => array( 'addon', 'license' ), 'nolimit' => true ) );

			foreach ( $addons as $addon ) {
				$addon->delete();
			}

			// now that the posts are gone, delete the type - this would fail if we hadn't deleted the content first
			Post::delete_post_type( 'addon' );
			Post::delete_post_type( 'license' );

			// remove vocabulary and terms
			$vocabulary = $this->vocabulary;
			if ( $vocabulary ) {
				$vocabulary->delete();
			}

			// now deactivate the plugin
			Plugins::deactivate_plugin( __FILE__ );

			Session::notice( _t("Uninstalled plugin '%s'", array( $this->info->name ), 'plugin_directory' ) );

			// redirect to the plugins page again so the page updates properly - this is what AdminHandler does after plugin deactivation
			Utils::redirect( URL::get( 'admin', 'page=plugins' ) );

		}

		public function action_plugin_ui_configure ( ) {

			$ui = new FormUI('plugin_directory');

			//$ui->append( 'text', 'licenses', 'option:', _t( 'Licenses to use:', 'Lipsum' ) );
			$ui->append( 'text', 'basepath', 'plugin_directory__basepath', _t( 'Base path (without trailing slash), e.g. <em>explore</em> :', 'plugin_directory' ) );
			$ui->append( 'text', 'date_format', 'plugin_directory__date_format', _t( 'Release Date format :', 'plugin_directory' ) );

			$ui->append( 'submit', 'save', _t( 'Save', 'plugin_directory' ) );

//			$ui->on_success( array( $this, 'updated_config' ) );

			$ui->out();

		}

		public function filter_default_rewrite_rules ( $rules ) {

			$basepath = Options::get( 'plugin_directory__basepath', 'explore' );

			// create the beacon endpoint rule
			$rule = array(
				'name' => 'beacon_server',
				'parse_regex' => '%^beacon$%i',
				'build_str' => 'beacon',
				'handler' => 'BeaconHandler',
				'action' => 'request',
				'description' => 'Incoming Beacon Update Requests',
			);

			// add it to the stack
			$rules[] = $rule;

			// create the addon post display rule for one plugin
			$rule = array(
				'name' => 'display_addon_plugin',
				'parse_regex' => '#^' . $basepath . '/plugins/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i',
				'build_str' => $basepath . '/plugins/{$slug}(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => 'display_plugin',
				'parameters' => serialize( array( 'require_match' => array( 'Posts', 'rewrite_match_type' ), 'content_type' => 'addon', 'info' => array( 'type' => 'plugin' ) ) ),
				'description' => 'Display addon directory posts of the type plugin',
			);

			// add it to the stack
			$rules[] = $rule;

			// create the addon post display rule for plugins
			$rule = array(
				'name' => 'display_addon_plugins',
				'parse_regex' => '%^' . $basepath . '/plugins(?:/page/(?P<page>\d+))?/?$%',
				'build_str' => $basepath . '/plugins(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => 'display_plugins',
				'priority' => 2,
				'description' => 'Plugin Repo Server Browser',
			);

			// add it to the stack
			$rules[] = $rule;

			// create the addon post display rule for themes
			$rule = array(
				'name' => 'display_addon_themes',
				'parse_regex' => '#^' . $basepath . '/themes/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i',
				'build_str' => $basepath . '/themes/{$slug}(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => 'display_theme',
				'parameters' => serialize( array( 'require_match' => array( 'Posts', 'rewrite_match_type' ), 'content_type' => 'addon', 'info' => array( 'type' => 'theme' ) ) ),
				'description' => 'Display addon directory posts of the type theme',
			);

			// add it to the stack
			$rules[] = $rule;

			// create the license display rule
			$rule = array(
				'name' => 'display_license',
				'parse_regex' => '#^' . $basepath . '/license/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$#i',
				'build_str' => $basepath . '/license/{$slug}(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => 'display_post',
				'parameters' => serialize( array( 'require_match' => array( 'Posts', 'rewrite_match_type' ), 'content_type' => 'license' ) ),
				'description' => 'Display addon directory license posts',
			);

			// add it to the stack
			$rules[] = $rule;

			// always return the rules
			return $rules;

		}

		/**
		 * Handle requests for multiple plugins
		 * 
		 * @param ?? $handled
		 * @param Theme $post
		 */
		public function filter_theme_act_display_plugins( $handled, $theme )
		{
			$paramarray[ 'fallback' ] = array(
				'addon.multiple',
				'multiple',
			);

			$default_filters = array(
				'content_type' => Post::type( 'addon' ),
				'info' => array( 'type' => 'plugin' ),
			);

			$paramarray['user_filters'] = $default_filters;

			$theme->act_display( $paramarray );
			return true;
		}

		public function filter_template_where_filters( $filters )
		{
			$basepath = Options::get( 'plugin_directory__basepath', 'explore' );
			$vars = Controller::get_handler_vars();
			if( strlen( $vars['entire_match'] ) && strpos( $vars['entire_match'], $basepath . '/' ) !== FALSE ) {
				$filters['orderby'] = 'title';
			}
			return $filters;
		}

		/**
		 * Manipulate the controls on the publish page
		 * 
		 * @param FormUI $form The form that is used on the publish page
		 * @param Post $post The post that's being edited
		 */
		public function action_form_publish ( $form, $post ) {

			// split out to smaller functions based on the content type
			if ( $form->content_type->value == Post::type( 'addon' ) ) {
				$this->form_publish_addon( $form, $post );
			}
			else if ( $form->content_type->value == Post::type( 'license' ) ) {
				$this->form_publish_license( $form, $post );
			}

		}

		/**
		 * Manipulate the controls on the publish page for Addons
		 * 
		 * @todo fix tab indexes
		 * @todo remove settings tab without breaking everything in it?
		 * 
		 * @param FormUI $form The form that is used on the publish page
		 * @param Post $post The post that's being edited
		 */
		private function form_publish_addon ( $form, $post ) {

			// remove the settings pane from the publish controls for non-admin users, we don't want anyone editing that
			if ( User::identify()->can( 'superuser' ) == false ) {
				$form->publish_controls->remove( $form->publish_controls->settings );
			}

			// add guid after title
			$guid = $form->append( 'text', 'addon_details_guid', 'null:null', _t('GUID', 'plugin_directory') );
			$guid->value = $post->info->guid;	// populate it, if it exists
			$guid->template = ( $post->slug ) ? 'admincontrol_text' : 'guidcontrol';
			$form->move_after( $form->addon_details_guid, $form->title );	// position it after the title

			// add the description after the guid
			$description = $form->append( 'textarea', 'addon_details_description', 'null:null', _t('Description', 'plugin_directory') );
			$description->value = $post->info->description;	// populate it, if it exists
			$description->rows = 2; // Since it's resizable, this doesn't need to start out so big, does it?
			$description->template = 'admincontrol_textarea';
			$form->move_after( $form->addon_details_description, $form->addon_details_guid );

			// add the instructions after the content
			$instructions = $form->append( 'textarea', 'addon_details_instructions', 'null:null', _t('Instructions', 'plugin_directory') );
			$instructions->value = $post->info->instructions;	// populate it, if it exists
			$instructions->class[] = 'resizable';
			$instructions->rows = 4; // Since it's resizable, this doesn't need to start out so big, does it?
			$instructions->template = 'admincontrol_textarea';
			$form->move_after( $form->addon_details_instructions, $form->content );	// position it after the content box


			// create the addon details wrapper pane
			$addon_fields = $form->publish_controls->append( 'fieldset', 'addon_details', _t('Details', 'plugin_directory') );

			// add the type: plugin or theme
			$details_type = $addon_fields->append( 'select', 'addon_details_type', 'null:null', _t('Addon Type', 'plugin_directory') );
			$details_type->value = $post->info->type;
			$details_type->template = 'tabcontrol_select';
			$details_type->options = array(
				'' => '',
				'plugin' => _t('Plugin', 'plugin_directory'),
				'theme' => _t('Theme', 'plugin_directory'),
			);
			// admins can use the 'core' type for habari itself
			if ( User::identify()->can('superuser') ) {
				$details_type->options['core'] = _t('Core', 'plugin_directory');
			}
			$details_type->add_validator( 'validate_required' );

			// add the url
			$details_url = $addon_fields->append( 'text', 'addon_details_url', 'null:null', _t('URL', 'plugin_directory') );
			$details_url->value = $post->info->url;
			$details_url->template = 'tabcontrol_text';

			// add the screenshot
			$details_screenshot = $addon_fields->append( 'text', 'addon_details_screenshot', 'null:null', _t('Screenshot', 'plugin_directory') );
			$details_screenshot->value = $post->info->screenshot;
			$details_screenshot->template = 'tabcontrol_text';

			// add the author name
			$details_author = $addon_fields->append( 'text', 'addon_details_author', 'null:null', _t('Author', 'plugin_directory') );
			$details_author->value = $post->info->author;
			$details_author->template = 'tabcontrol_text';

			// add the author url
			$details_author_url = $addon_fields->append( 'text', 'addon_details_author_url', 'null:null', _t('Author URL', 'plugin_directory') );
			$details_author_url->value = $post->info->author_url;
			$details_author_url->template = 'tabcontrol_text';

			// add the license @todo should be populated with a list of license content types
			$details_license = $addon_fields->append( 'select', 'addon_details_license', 'null:null', _t('License', 'plugin_directory') );
			$details_license->value = $post->info->license;
			$details_license->template = 'tabcontrol_select';
			$details_license->options = $this->get_license_options();

			// create the addon versions wrapper pane
			$addon_versions = $form->publish_controls->append( 'fieldset', 'addon_versions', _t('Versions', 'plugin_directory') );

			if ( $post->info->versions ) {

				$form->addon_versions->append( 'static', 'current_versions', _t('Current Versions', 'plugin_directory') );

				foreach ( $post->info->versions as $version ) {

					$version_info = $version['severity'] . ': ' . $post->title . ' ' . $version['version'] . ' -- ' . $version['description'];
					$addon_versions->append( 'static', 'version_info_' . $version['version'], $version_info );

				}
			}

			// add the new version fields
			$form->addon_versions->append( 'static', 'new_version', _t('Add New Version', 'plugin_directory') );

			// the version number
			$version = $addon_versions->append( 'text', 'addon_version_version', 'null:null', _t('Version Number', 'plugin_directory') );
			$version->template = 'tabcontrol_text';
			$version->add_validator( 'validate_required' );

			// the version release date
			$version_release = $addon_versions->append( 'text', 'addon_version_release', 'null:null', _t('Release Date', 'plugin_directory') );
			$version_release->template = 'tabcontrol_text';
			$version_release->value = HabariDateTime::date_create()->format();
			$version_release->add_validator( 'validate_required' );

			// the version description
			$version_description = $addon_versions->append( 'text', 'addon_version_description', 'null:null', _t('Version Description', 'plugin_directory') );
			$version_description->template = 'tabcontrol_text';

			// the version information url
			$info_url = $addon_versions->append( 'text', 'addon_version_info_url', 'null:null', _t('Information URL', 'plugin_directory') );
			$info_url->template = 'tabcontrol_text';

			// the version download url
			$version_url = $addon_versions->append( 'text', 'addon_version_url', 'null:null', _t('Download URL', 'plugin_directory') );
			$version_url->template = 'tabcontrol_text';
			$version_url->add_validator( 'validate_required' );

			// the habari version it's compatible with
			$habari_version = $addon_versions->append( 'text', 'addon_version_habari_version', 'null:null', _t('Compatible Habari Version', 'plugin_directory') );
			$habari_version->template = 'tabcontrol_text';
			$habari_version->helptext = _t('"x" is a wildcard, eg. 0.6.x', 'plugin_directory');
			$habari_version->add_validator( 'validate_required' );

			// the release severity
			$severity = $addon_versions->append( 'select', 'addon_version_severity', 'null:null', _t('Severity', 'plugin_directory') );
			$severity->template = 'tabcontrol_select';
			$severity->options = array(
				'release' => _t('Initial Release', 'plugin_directory'),
				'critical' => _t('Critical', 'plugin_directory'),
				'bugfix' => _t('Bugfix', 'plugin_directory'),
				'feature' => _t('Feature', 'plugin_directory'),
			);

			// features required
			$requires = $addon_versions->append( 'text', 'addon_version_requires', 'null:null', _t('Requires Feature', 'plugin_directory') );
			$requires->template = 'tabcontrol_text';
			$requires->helptext = _t('Comma separated, like tags.', 'plugin_directory');

			// features provided
			$provides = $addon_versions->append( 'text', 'addon_version_provides', 'null:null', _t('Provides Feature', 'plugin_directory') );
			$provides->template = 'tabcontrol_text';

			// features recommended
			$recommends = $addon_versions->append( 'text', 'addon_version_recommends', 'null:null', _t('Recommends Feature', 'plugin_directory') );
			$recommends->template = 'tabcontrol_text';

		}

		private function get_license_options ( ) {

			$licenses = Posts::get( array( 'content_type' => 'license', 'nolimit' => true ) );

			$l = array( '' => '' );		// start with a blank option
			foreach ( $licenses as $license ) {

				$l[ $license->slug ] = $license->title;

			}

			return $l;

		}

		/**
		 * Manipulate the controls on the publish page for Licenses
		 * 
		 * @param FormUI $form The form that is used on the publish page
		 * @param Post $post The post that's being edited
		 */
		private function form_publish_license ( $form, $post ) {

			// remove silos, we don't need them
			$form->remove( $form->silos );

			// remove the tags, we don't use those
			$form->remove( $form->tags );

			// remove the settings pane from the publish controls for non-admin users, we don't want anyone editing that
			if ( User::identify()->can( 'superuser' ) == false ) {
				$form->publish_controls->remove( $form->publish_controls->settings );
			}

			// add shortname after title
			$shortname = $form->append( 'text', 'license_shortname', 'null:null', _t('Short Name', 'plugin_directory') );
			$shortname->value = $post->info->shortname;		// populate it, if it exists
			$shortname->template = 'admincontrol_text';
			$form->move_after( $form->license_shortname, $form->title );	// move it after the title field

			// add the simple text
			$simpletext = $form->append( 'textarea', 'license_simpletext', 'null:null', _t('Simple Text', 'plugin_directory') );
			$simpletext->value = $post->info->simpletext;	// populate it, if it exists
			$simpletext->template = 'admincontrol_textarea';
			$form->move_after( $form->license_simpletext, $form->license_shortname );

			// add url
			$url = $form->append( 'text', 'license_url', 'null:null', _t('License URL', 'plugin_directory') );
			$url->value = $post->info->url;		// populate it, if it exists
			$url->template = 'admincontrol_text';
			$form->move_after( $form->license_url, $form->license_simpletext );

		}

		public function action_publish_post ( $post, $form ) {

			if ( $post->content_type == Post::type( 'addon' ) ) {

				foreach ( $this->addon_fields as $field ) {

					if ( $form->{'addon_details_' . $field}->value ) {
						$post->info->$field = $form->{'addon_details_' . $field}->value;
					}

				}

				// save version information
				$this->prepare_versions( $post, $form );

			}
			else if ( $post->content_type == Post::type( 'license' ) ) {

				foreach ( $this->license_fields as $field ) {

					if ( $form->{'license_' . $field}->value ) {
						$post->info->$field = $form->{'license_' . $field}->value;
					}

				}

				// if the shortname is set, use it as the slug
				if ( isset( $post->info->shortname ) ) {
					$post->slug = Utils::slugify( $post->info->shortname );
				}

			}

		}

		private function prepare_versions ( $post, $form ) {

			// first see if a version is trying to be added
			if ( $form->addon_version_version != '' ) {
				// create an array to store all the version info
				$version = array();

				// loop through all the fields and add them to our array if they are set
				foreach ( $this->version_fields as $field ) {
					if ( $form->{"addon_version_$field"}->value != '' ) {
						$version[ $field ] = $form->{"addon_version_$field"}->value;
					}
				}
				$this->save_versions( $post, array( $form->addon_version_version->value => $version ) );
			}
		}

		protected function save_versions ( $post, $versions = array() ) {

			$vocabulary = $this->vocabulary;

			foreach( $versions as $key => $version ) {
				$term = new Term( array(
					'term_display' => $post->id . " $key",
				) );
				foreach ( $version as $field => $value ) {
					$term->info->$field = $value;
				}
				$vocabulary->add_term( $term );
				$term->associate( 'addon', $post->id );
			}
		}

		public function action_init ( ) {

			// register our custom guid FormUI control for the post publish page
			$this->add_template( 'guidcontrol', dirname(__FILE__) . '/templates/guidcontrol.php' );
			$this->add_template( 'addon.multiple', dirname(__FILE__) . '/templates/addon.multiple.php' );
			$this->add_template( 'addon.single', dirname(__FILE__) . '/templates/addon.single.php' );
			$this->add_template( 'license.single', dirname(__FILE__) . '/templates/license.single.php' );

		}

		/**
		 * Provide a quick AJAX method to return a GUID for the post page.
		 * 
		 * @param ActionHandler $handler The handler being executed.
		 */
		public function action_auth_ajax_generate_guid ( $handler ) {

			echo UUID::get();

		}

		public function action_ajax_plugindirectory_update ( $handler ) {

			if ( !isset( $_POST['api_key'] ) ) {
				throw new Exception( _t('No API Key specified!', 'plugin_directory' ) );
			}

			$api_key = $_POST['api_key'];

			if ( !in_array( $api_key, $this->api_keys ) ) {
				throw new Exception( _t('Invalid API key!', 'plugin_directory' ) );
			}

		}

		public function filter_theme_act_display_plugin ( $handled, $theme ) {

			$default_filters = array(
				'content_type' => Post::type('addon'),
				'info' => array( 'type' => 'plugin' ),
			);

			$theme->act_display_post( $default_filters );

			return true;

		}

		/**
		 * Return an array of all versions associated with a post
		 **/
		public function filter_post_versions( $versions, $post ) {

			if ( $post->content_type != Post::type( 'addon' ) ) {
				return false;
			}

			$vocabulary = $this->vocabulary;
			if ( $vocabulary === false || $post->content_type != Post::type( 'addon' ) ) {
				return false;
			}

			$terms = $vocabulary->get_all_object_terms( 'addon', $post->id );
			if ( count( $terms ) > 0 ) {
				// @TODO: order them here?
				return $terms;
			}
			else {
				return false;
			}
		}
		/**
		 * Return an HTML link to the license of an addon
		 **/
		public function filter_post_license_link( $license_link, $post ) {
			$license_post = Post::get( array( 'slug' => $post->info->license ) );

			return "<a href='{$license_post->permalink}' title='More details about this license'>{$license_post->title}</a>";
		}
	}

?>
