<?php

	include('beaconhandler.php');

	class AddonCatalogPlugin extends Plugin {

		// @todo we can do better than this
		private	$types = array( 'themes' => 'theme', 'plugins' => 'plugin', 'core' => 'core' );

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
			'authors',
			'licenses',
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

			/* Features */
			'requires',
			'provides',
			'conflicts',
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

			// allow reading the new content types
			UserGroup::get_by_name( 'anonymous' )->grant( 'post_addon', 'read' );

			// create a permissions token
			ACL::create_token( 'manage_versions', _t( 'Manage Addon Versions', 'addon_catalog'), 'Addon Catalog', false );

			// create the addon vocabulary (type)
			Vocabulary::add_object_type( 'addon' );

			// create the addon vocabulary
			$params = array(
				'name' => self::$vocabulary,
				'description' => _t( 'A vocabulary for addon versions in the addons catalog', 'addon_catalog' ),
				);
			$vocabulary = Vocabulary::create( $params );
			// @TODO: notification/log of some sort?

			// create the default content
			include( 'create_core_addons.php' );
		}

		public function action_plugin_deactivation ( $file ) {

			// when deactivating, don't destroy data, just turn it 'off'
			Post::deactivate_post_type( 'addon' );
			ACL::destroy_token( 'manage_versions' );
		}

		/**
		 * Nomenclature for the main menu -> New, and Manage
		 */
		public function filter_post_type_display ( $type, $plurality ) {

			if ( $type == 'addon' ) {
				if ( $plurality == 'singular' ) {
					$type = _t('Addon', 'addon_catalog');
				}
				else {
					$type = _t('Addons', 'addon_catalog');
				}
			}

			return $type;
		}

		public function filter_plugin_config ( $actions, $plugin_id ) {

			// we don't use the magic configure() method because then it gets placed below custom actions in the dropbutton
			$actions['configure'] = _t('Configure', 'addon_catalog');
			$actions['uninstall'] = _t('Uninstall', 'addon_catalog');

			return $actions;

		}

		public function action_plugin_ui_uninstall ( ) {

			// get all the posts of the types we're deleting
			$addons = Posts::get( array( 'content_type' => array( 'addon' ), 'nolimit' => true ) );

			foreach ( $addons as $addon ) {
				$addon->delete();
			}

			// now that the posts are gone, delete the type - this would fail if we hadn't deleted the content first
			Post::delete_post_type( 'addon' );

			// remove vocabulary and terms
			$vocabulary = $this->vocabulary;
			if ( $vocabulary ) {
				$vocabulary->delete();
			}

			// now deactivate the plugin
			Plugins::deactivate_plugin( __FILE__ );

			Session::notice( _t("Uninstalled plugin '%s'", array( $this->info->name ), 'addon_catalog' ) );

			// redirect to the plugins page again so the page updates properly - this is what AdminHandler does after plugin deactivation
			Utils::redirect( URL::get( 'admin', 'page=plugins' ) );

		}

		public function action_plugin_ui_configure ( ) {

			$ui = new FormUI('addon_catalog');

			$ui->append( 'checkbox', 'use_basepath', 'addon_catalog__keep_pages', _t( 'Use a base path: ', 'addon_catalog' ) );
			$ui->append( 'text', 'basepath', 'addon_catalog__basepath', _t( 'Base path (without trailing slash), e.g. <em>explore</em> :', 'addon_catalog' ) );
			$ui->append( 'text', 'date_format', 'addon_catalog__date_format', _t( 'Release Date format :', 'addon_catalog' ) );

			$ui->append( 'submit', 'save', _t( 'Save', 'addon_catalog' ) );

			$ui->out();

		}

		public function filter_default_rewrite_rules ( $rules ) {

			if ( Options::get( 'addon_catalog__use_basepath', false ) ) {
				$basepath = Options::get( 'addon_catalog__basepath', 'explore' ) . "/";
			}
			else {
				$basepath = "";
			}

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

			// create the display rule for the addon base page
			$rule = array(
				'name' => 'display_addon_basepath',
				'parse_regex' => '#^' . $basepath . '$#i',
				'build_str' => $basepath,
				'handler' => 'UserThemeHandler',
				'action' => 'display_basepath',
				'description' => 'Display addon catalog base page',
			);

			// add it to the stack
			$rules[] = $rule;

			$addon_regex = implode('|', array_keys($this->types));
			// create the post display rule for one addon
			$rule = array(
				'name' => "display_addon",
				'parse_regex' => "#^{$basepath}(?P<addon>{$addon_regex})/(?P<slug>[^/]+)/?$#i",
				'build_str' => $basepath . '{$addon}/{$slug}',
				'handler' => 'UserThemeHandler',
				'action' => 'display_addon',
				'parameters' => serialize( array( 'require_match' => array( 'Posts', 'rewrite_match_type' ), 'content_type' => 'addon' ) ),
				'description' => "Display an addon caralog post of a particular type",
			);

			// add it to the stack
			$rules[] = $rule;

			// create the addon post display rule for multiple addons
			$rule = array(
				'name' => "display_addons",
				'parse_regex' => "%^{$basepath}(?P<addon>{$addon_regex})(?:/page/(?P<page>\d+))?/?$%",
				'build_str' => $basepath . '{$addon}(/page/{$page})',
				'handler' => 'UserThemeHandler',
				'action' => "display_addons",
				'priority' => 2,
				'description' => "Display addon catalog posts of a particular type",
			);

			// add it to the stack
			$rules[] = $rule;

			// always return the rules
			return $rules;

		}

		/**
		 * Handle requests for addon catalog base page
		 *
		 * @param Boolean $handled
		 * @param Theme $post
		 *
		 * @return Boolean Whether the request has been handled
		 */
		public function filter_theme_act_display_basepath( $handled, $theme )
		{
			$paramarray[ 'fallback' ] = array(
				'addon.basepath',
			);

			$theme->types = array('plugins' => 'Plugins', 'themes' => 'Themes');
			$paramarray['user_filters'] = array(); // sufficient for the time being since this shows no content.
			$theme->act_display( $paramarray );
			return true;
		}

		/**
		 * Handle requests for multiple addons
		 *
		 * @param Boolean $handled
		 * @param Theme $post
		 */
		public function filter_theme_act_display_addons( $handled, $theme )
		{
			$paramarray[ 'fallback' ] = array(
				'addon.multiple',
				'multiple',
			);

			$type = $theme->matched_rule->named_arg_values['addon'];
			$type = $this->types[$type];

			$default_filters = array(
				'content_type' => Post::type( 'addon' ),
				'info' => array( 'type' => $type ),
				'orderby' => 'title ASC',
				'limit' => 20,
			);
			$handler = Controller::get_handler();
			if(isset($handler->handler_vars['page'])) {
				$default_filters['page'] = $handler->handler_vars['page'];
			}
			$paramarray['posts'] = Posts::get($default_filters);

			$paramarray['user_filters'] = $default_filters;
			$theme->act_display( $paramarray );
			return true;
		}

		public function filter_template_where_filters( $filters )
		{
			$basepath = Options::get( 'addon_catalog__basepath', 'explore' );
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
			$guid = $form->append( 'text', 'addon_details_guid', 'null:null', _t('GUID', 'addon_catalog') );
			$guid->value = $post->info->guid;	// populate it, if it exists
			$guid->template = ( $post->slug ) ? 'admincontrol_text' : 'guidcontrol';
			$form->move_after( $form->addon_details_guid, $form->title );	// position it after the title

			// add the description after the guid
			$description = $form->append( 'textarea', 'addon_details_description', 'null:null', _t('Description', 'addon_catalog') );
			$description->value = $post->info->description;	// populate it, if it exists
			$description->rows = 2; // Since it's resizable, this doesn't need to start out so big, does it?
			$description->template = 'admincontrol_textarea';
			$form->move_after( $form->addon_details_description, $form->addon_details_guid );

			// add the instructions after the content
			$instructions = $form->append( 'textarea', 'addon_details_instructions', 'null:null', _t('Instructions', 'addon_catalog') );
			$instructions->value = $post->info->instructions;	// populate it, if it exists
			$instructions->class[] = 'resizable';
			$instructions->rows = 4; // Since it's resizable, this doesn't need to start out so big, does it?
			$instructions->template = 'admincontrol_textarea';
			$form->move_after( $form->addon_details_instructions, $form->content );	// position it after the content box


			// create the addon details wrapper pane
			$addon_fields = $form->append( 'fieldset', 'addon_details', _t('Details', 'addon_catalog') );
			$form->move_after( $form->addon_details, $form->tags );


			// add the type: plugin or theme
			$details_type = $addon_fields->append( 'select', 'addon_details_type', 'null:null', _t('Addon Type', 'addon_catalog') );
			$details_type->value = $post->info->type;
			$details_type->template = 'tabcontrol_select';
			$details_type->options = array(
				'' => '',
				'plugin' => _t('Plugin', 'addon_catalog'),
				'theme' => _t('Theme', 'addon_catalog'),
			);
			// admins can use the 'core' type for habari itself
			if ( User::identify()->can('superuser') ) {
				$details_type->options['core'] = _t('Core', 'addon_catalog');
			}
			$details_type->add_validator( 'validate_required' );

			// add the url
			$details_url = $addon_fields->append( 'text', 'addon_details_url', 'null:null', _t('URL', 'addon_catalog') );
			$details_url->value = $post->info->url;
			$details_url->template = 'tabcontrol_text';

			// add the screenshot
			$details_screenshot = $addon_fields->append( 'text', 'addon_details_screenshot', 'null:null', _t('Screenshot', 'addon_catalog') );
			$details_screenshot->value = $post->info->screenshot;
			$details_screenshot->template = 'tabcontrol_text';

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

		public static function addon_exists( $guid = null ) {

			return ( isset( $guid ) and (
				Post::get( array( 'status' => Post::status( 'published' ),  'content_type' => Post::type( 'addon' ), 'all:info' => array( 'guid' => $guid ) ) )
			!= false ) );
		}

		public static function get_addon( $guid = null ) {
/* we don't need an isset() on the guid after all, do we? Do we need addon_exists at all, since this would return false when not found? */
			return Post::get( array(
				'status' => Post::status( 'published' ),
				'content_type' => Post::type( 'addon' ),
				'all:info' => array( 'guid' => $guid ),
				//'fetch_fn' => 'get_query',
			) );
		}

		public static function handle_addon( $info = array(), $versions =  array() ) {

			$main_fields = array(
				/* This is a crosswalk of the Post-related items expected in the $info array for create_addon() */
				'user_id' => 'user_id',
				'name' => 'title',
				'description' => 'content',
				'tags' => 'tags',
			);

			$post_fields = array(
				'content_type' => Post::type( 'addon' ),
				'status' => Post::status( 'published' ),
				'pubdate' => HabariDateTime::date_create(),
			);

			$post = self::get_addon( strtoupper( $info[ 'guid' ] ) ); // strtoupper might not be necessary
			if ( ! $post ) {
				/* There is no addon already with the guid. Create a new one. */				

				foreach( $main_fields as $k => $v ) {
					$post_fields[ $v ] = $info[ $k ];
				}

				unset( $post );
				$post = Post::create( $post_fields );

				EventLog::log( _t( 'Created post #%s - %s', array( $post->id, $post->title ) ), 'info' );
			}
			else {
				/* Update the existing addon. */
				$post->modify( array(
					'title' => $info[ 'name' ],
					'content' => $info[ 'description' ],
					'slug' => Utils::slugify( $info[ 'name' ] ),
					'pubdate' => HabariDateTime::date_create(), // should this use the date from the ping?,

				) );
				$post->update();

				EventLog::log( _t( 'Edited post #%s - %s', array( $post->id, $post->title ) ), 'info' );

				$info = array_diff_key( $info, array( 'original_repo' => 'should be removed for updates' ) );
			}

			// remove the fields that should not become postinfo.

			$info_fields = array_diff_key( $info, $main_fields );

			foreach ( $info_fields  as $k => $v ) {
				switch($k) {
					case 'guid':
						$post->info->$k = strtoupper($v);
						break;
					default:
						$post->info->$k = $v;
						break;
				}
			}
			$post->info->commit();

			self::save_versions( $post, $versions );
		}

		public static function save_versions( $post = null, $versions = array() ) {

			if( isset( $post ) && count( $versions ) !== 0 ) {

				$vocabulary = Vocabulary::get( self::$vocabulary );
				$extant_terms = $vocabulary->get_associations($post->id, 'addon');

				foreach( $versions as $key => $version ) {

					$term_display = "{$post->id} {$key} {$post->info->repo_url}";

					$found = false;
					foreach( $extant_terms as $eterm ) {
						if( $eterm->term_display == $term_display ) {  // This is super-cheesy!
							$found = true;
							$term = $eterm;
							break;
						}
					}
					if(!$found) {
						$term = new Term( array(
							'term_display' => $post->id . " $key",
						) );
					}
					foreach ( $version as $field => $value ) {
						$term->info->$field = $value;
					}
					if( $found ) {
						$term->update();
					}
					else {
						$vocabulary->add_term( $term );
						$term->associate( 'addon', $post->id );
					}
				}
			}
			else {
				// post didn't work or there was no version.
			}
		}

		public function action_init ( ) {

			// register our custom guid FormUI control for the post publish page
			$this->add_template( 'guidcontrol', dirname(__FILE__) . '/templates/guidcontrol.php' );
			$this->add_template( 'addon.basepath', dirname(__FILE__) . '/templates/addon.basepath.php' );
			$this->add_template( 'addon.multiple', dirname(__FILE__) . '/templates/addon.multiple.php' );
			$this->add_template( 'addon.single', dirname(__FILE__) . '/templates/addon.single.php' );

			// register admin pages
			$this->add_template( 'versions_admin', dirname( __FILE__ ) . '/addons_admin.php' );
			$this->add_template( 'version_iframe', dirname( __FILE__ ) . '/version_iframe.php' );

		}

		/**
		 * Provide a quick AJAX method to return a GUID for the post page.
		 *
		 * @param ActionHandler $handler The handler being executed.
		 */
		public function action_auth_ajax_generate_guid ( $handler ) {

			echo UUID::get();

		}

		public function action_ajax_addoncatalog_update ( $handler ) {

			if ( !isset( $_POST['api_key'] ) ) {
				throw new Exception( _t('No API Key specified!', 'addon_catalog' ) );
			}

			$api_key = $_POST['api_key'];

			if ( !in_array( $api_key, $this->api_keys ) ) {
				throw new Exception( _t('Invalid API key!', 'addon_catalog' ) );
			}

		}

		public function filter_theme_act_display_addon ( $handled, $theme ) {

			$type = $theme->matched_rule->named_arg_values['addon'];
			$type = $this->types[$type];

			$default_filters = array(
				'content_type' => Post::type('addon'),
				'info' => array( 'type' => $type ),
			);

			$theme->act_display_post( $default_filters );

			return true;

		}

		/**
		 * Filter the permalink of addons
		 */
		public function filter_post_permalink( $permalink, $post ) {
			if ($post->content_type == Post::type('addon')) {
				$types = array_flip($this->types);
				$permalink = URL::get("display_addon", array('addon' => $types[$post->info->type], 'slug' => $post->slug));
			}
			return $permalink;
		}

		/**
		 * Return an array of all versions associated with a post
		 */
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
		 * Add link to the main menu
		 *
		 **/
		public function filter_adminhandler_post_loadplugins_main_menu( $menu ) {

			// add to main menu
			$item_menu = array( 'addons' =>
				array(
					'url' => URL::get( 'admin', 'page=addons' ),
					'title' => _t( 'Addon Versions', 'addon_catalog' ),
					'text' => _t( 'Addon Versions', 'addon_catalog' ),
					'hotkey' => 'V',
					'selected' => false,
					'access' => array( 'manage_versions', true ),
				)
			);

			$slice_point = array_search( 'themes', array_keys( $menu ) ); // Element will be inserted before "themes"
			$pre_slice = array_slice( $menu, 0, $slice_point);
			$post_slice = array_slice( $menu, $slice_point);

			$menu = array_merge( $pre_slice, $item_menu, $post_slice );

			return $menu;
		}

		/**
		 * Handle GET and POST requests
		 *
		 **/
		public function alias()
		{
			return array(
				'action_admin_theme_get_addons' => 'action_admin_theme_post_addons',
				'action_admin_theme_get_version_iframe' => 'action_admin_theme_post_version_iframe',
			);
		}

		/**
		 * Restrict access to the admin page
		 *
		 **/
		public function filter_admin_access_tokens( array $require_any, $page )
		{
			switch ( $page ) {
				case 'version_iframe':
				case 'addons':
					$require_any = array( 'manage_versions' => true );
					break;
			}
			return $require_any;
		}

		/**
		 * Prepare and display admin page
		 *
		 **/
		public function action_admin_theme_get_addons( AdminHandler $handler, Theme $theme )
		{
			$theme->page_content = "";

			$theme->display( 'versions_admin' );
		}


		/**
		 * function name_url_list
		 * Formatting function
		 * Turns an array of array( name, url ) into an HTML-linked list with commas and an "and".
		 * @param array $array An array of items
		 * @param string $between Text to put between each element
		 * @param string $between_last Text to put between the next to last element and the last element
		 * @return string HTML links with specified separators.
		 */
		public static function name_url_list( $items = array(), $between = ', ', $between_last = null )
		{
			$array = array();

			foreach ( $items as $item ) {
				$array[ $item[ 'name' ] ] = $item[ 'url' ];
			}

			if ( $between_last === null ) {
				$between_last = _t( ' and ' );
			}

			$fn = create_function( '$a,$b', 'return "<a href=\\"" . $a . "\\">" . $b . "</a>";' );
			$array = array_map( $fn, $array, array_keys( $array ) );
			$last = array_pop( $array );
			$out = implode( $between, $array );
			$out .= ( $out == '' ) ? $last : $between_last . $last;
			return $out;

		}

	}

?>
