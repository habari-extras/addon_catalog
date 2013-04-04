<?php
namespace Habari;

	include('beaconhandler.php');


/**
 * Class AddonCatalogPlugin
 * @property Vocabulary $vocabulary the Vocabulary instance that holds addon version info
 * @property array $types An array of addon types
 */
class AddonCatalogPlugin extends Plugin {

	// This is the name of the vocabulary that holds versions for each addon
	const CATALOG_VOCABULARY = "Addon versions";
	/** @var Vocabulary $_vocabulary Instance of the vocab that holds version info */
	protected $_vocabulary;
	/** @var array $_types Default addon types */
	protected $_types = array(
		'theme' => 'Themes',
		'plugin' => 'Plugins',
		'bundle' => 'Bundles',
		'core' => 'Core',
	);

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
		'screenshot_url',
		'authors',
		'licenses',
		'parent',
	);

	// fields each version should have
	private $version_fields = array(
		'hash',
		'version',
		'release', /* release date */
		'description',
		'info_url',
		'url', /* download url */
		'habari_version',
		'severity',
		'source', /* github, bitbucket, etc */
		/* Features */
		'requires',
		'provides',
		'conflicts',
		'recommends',
	);

	/**
	 * Return named properties on this object
	 * @param string $name Name of property to get
	 * @return mixed
	 */
	public function __get( $name ) {
		switch( $name ) {
			case 'vocabulary':
				if ( !isset( $this->_vocabulary ) ) {
					$this->_vocabulary = Vocabulary::get( self::CATALOG_VOCABULARY );
				}
				return $this->_vocabulary;
			case 'types':
				return Plugins::filter('addon_types', $this->_types);
		}
		return null;
	}

	public function action_template_header() {
		Stack::add('template_stylesheet', array($this->get_url('/css/addons.css'), 'screen, projection'), 'addons');
	}

	/**
	 * Hook on activation of this plugin
	 */
	public function action_plugin_activation ( ) {

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
			'name' => self::CATALOG_VOCABULARY,
			'description' => _t( 'A vocabulary for addon versions in the addons catalog', 'addon_catalog' ),
			);
		$vocabulary = Vocabulary::create( $params );
		// @TODO: notification/log of some sort?

		// create the default content
		include( 'create_core_addons.php' );
	}

	/**
	 * Hook on deactivation of this plugin
	 */
	public function action_plugin_deactivation ( ) {
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

	/**
	 * Provide configuarion for the catalog plugin
	 * We don't use the magic configure() method because then it gets placed below custom actions in the dropbutton
	 * @param array $actions Actions to enable on this plugin
	 * @return mixed
	 */
	public function filter_plugin_config ( $actions ) {
		$actions['configure'] = _t('Configure', 'addon_catalog');
		$actions['uninstall'] = _t('Uninstall', 'addon_catalog');

		return $actions;
	}

	/**
	 * Respond to uninstalling this plugin in the plugin UI
	 */
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

	/**
	 * Responde to configuring this plugin in the plugin UI
	 */
	public function action_plugin_ui_configure ( ) {
		$ui = new FormUI('addon_catalog');

		$ui->append( FormControlCheckbox::create('use_basepath', 'addon_catalog__use_basepath')->label( _t( 'Use a base path: ', 'addon_catalog' ) ) );
		$ui->append( FormControlText::create('basepath', 'addon_catalog__basepath')->label( _t( 'Base path (without trailing slash), e.g. <em>explore</em> :', 'addon_catalog' ) ) );
		$ui->append( FormControlText::create('date_format', 'addon_catalog__date_format')->label( _t( 'Release Date format :', 'addon_catalog' ) ) );

		$ui->append( FormControlSubmit::create('save')->set_caption( _t( 'Save', 'addon_catalog' ) ) );

		$ui->out();
	}

	/**
	 * Add new rewrite rules to display addon-specific pages
	 * @param array $rules An array of protean RewriteRule data
	 * @return array The modified array
	 */
	public function filter_default_rewrite_rules ( $rules ) {

		if ( Options::get( 'addon_catalog__use_basepath', false ) ) {
			$basepath = Options::get( 'addon_catalog__basepath', 'explore' );
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
			'handler' => 'PluginHandler',
			'action' => 'display_basepath',
			'description' => 'Display addon catalog base page',
		);

		// add it to the stack
		$rules[] = $rule;

		// Add a trailing slash for the regex to the basepath if it is set
		$basepath = ($basepath == "") ? "" : $basepath . "/";

		$addon_regex = implode('|', array_keys($this->types));
		// create the post display rule for one addon
		$rule = array(
			'name' => "display_addon",
			'parse_regex' => "#^{$basepath}(?P<addon>{$addon_regex})/(?P<slug>[^/]+)/?$#i",
			'build_str' => $basepath . '{$addon}/{$slug}',
			'handler' => 'PluginHandler',
			'action' => 'display_addon',
			'parameters' => serialize( array( 'require_match' => array( 'Habari\Posts', 'rewrite_match_type' ), 'content_type' => 'addon' ) ),
			'description' => "Display an addon catalog post of a particular type",
		);
		$rules[] = $rule;

		// create the rule for downloading an addon as a zip
		$rule = array(
			'name' => "download_addon",
			'parse_regex' => "#^{$basepath}(?P<addon>{$addon_regex})/(?P<slug>[^/]+)/download/(?P<version>[^/]+)(?:/(?P<refresh>refresh))?/?$#i",
			'build_str' => $basepath . '{$addon}/{$slug}/download/{$version}(/{$refresh})',
			'handler' => 'PluginHandler',
			'action' => 'download_addon',
			'parameters' => '',
			'description' => "Download an addon",
		);
		$rules[] = $rule;

		// create the addon post display rule for multiple addons
		$rule = array(
			'name' => "display_addons",
			'parse_regex' => "%^{$basepath}(?P<addon>{$addon_regex})(?:/page/(?P<page>\d+))?/?$%",
			'build_str' => $basepath . '{$addon}(/page/{$page})',
			'handler' => 'PluginHandler',
			'action' => "display_addons",
			'priority' => 2,
			'description' => "Display addon catalog posts of a particular type",
		);
		
		$rules[] = $rule;
		
		// Add an /update endpoint to dispatch to different handlers
		$rule = array(
			'name' => "addon_update_ping",
			'parse_regex' => "#update#",
			'build_str' => 'update',
			'handler' => 'PluginHandler',
			'action' => 'addon_update_ping',
			'priority' => 2,
			'description' => 'Receive webservice pings from update services',
		);

		// add it to the stack
		$rules[] = $rule;

		// always return the rules
		return $rules;

	}

	public static function screenshot($addon) {
		if( $addon->info->screenshot_url != '' ) {
			return '<img class="" src="' . $addon->info->screenshot_url . '" alt="' . $addon->title . '">';
		} else {
			return '<i class="icon-' . $addon->info->type .'">' . self::get_type_icon( $addon->info->type ) . '</i>';
		}
	}

	public static function get_type_icon($type) {
			$out = '';
			switch( $type ) {
				case 'plugin' :
					$out = 'P';
				break;
				case 'theme' :
					$out = 'a';
				break;
				case 'bundle' :
					$out = 'b';
				break;
				case 'core' :
					$out = 'C';
				break;
			}
		
			return $out;
		}

	/**
	 * Handle requests for addon catalog base page
	 *
	 * @param Theme $theme The theme for output
	 * @param array $params The parameters passed in the URL
	 */
	public function theme_route_display_basepath( $theme, $params )
	{
		$theme->types = $this->types;
		$theme->addons = Posts::get( array('content_type' => Post::type('addon'), 'limit' => 8, 'orderby' => 'updated DESC') );
		$theme->display('addon.basepath');
	}

	/**
	 * Handle requests for multiple addons
	 *
	 * @param Theme $theme The theme for output
	 * @param array $params The parameters passed in the URL
	 */
	public function theme_route_display_addons( $theme, $params )
	{
		$theme->page = isset($params['page']) ? $params['page'] : 1;
		$theme->posts = Posts::get(array(
			'content_type' => Post::type( 'addon' ),
			'info' => array( 'type' => $params['addon'] ),
			'orderby' => 'title ASC',
			'limit' => 20,
			'page' => $theme->page,
		));
		$theme->addon_type = $params['addon'];
		$theme->addon_type_name = $this->_types[$params['addon']];
		$theme->display('addon.multiple');
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
		$guid = $form->append( FormControlText::create('guid', new ControlStorage(function($name) use ($post) { return $post->info->guid; }, function($name, $value) use ($post) { $post->info->guid = $value; }))->label( _t('GUID', 'addon_catalog') ) );
		$form->move_after( $guid, $form->label_for_title );	// position it after the title

		// add the description after the guid
		$description = $form->append( FormControlTextArea::create('description', $post, array('rows'=>2))->label( _t('Description', 'addon_catalog') ) );
		$form->move_after( $description, $guid );

		// add the instructions after the content
		$instructions = $form->append( FormControlTextArea::create('instructions')->add_class('resizable')->set_properties(array('rows'=>4))->label( _t('Instructions', 'addon_catalog') ) );
		$form->move_after( $instructions, $form->label_for_content );	// position it after the content box


		// create the addon details wrapper pane
		$addon_fields = $form->append( FormControlFieldset::create('addon_details')->set_caption( _t('Details', 'addon_catalog') ) );
		$form->move_after( $addon_fields, $form->label_for_tags );


		// add the type: plugin or theme
		$details_type = $addon_fields->append( FormControlSelect::create('type', $post)->set_options($this->_types)->add_validator( 'validate_required' )->label( _t('Addon Type', 'addon_catalog') ) );

		// add the url
		$details_url = $addon_fields->append( FormControlText::create('url', $post)->label( _t('URL', 'addon_catalog') ) );

		// add the screenshot
		$details_screenshot = $addon_fields->append( FormControlText::create('screenshot_url', $post)->label( _t('Screenshot', 'addon_catalog') ) );

	}

	/**
	 * Hook executes when a post is published
	 * @param Post $post The post that's being published
	 * @param FormUI $form The form that holds the post data
	 */
	public function action_publish_post ( $post, $form ) {
		if ( $post->content_type == Post::type( 'addon' ) ) {

			// save version information
			//$this->prepare_versions( $post, $form );  // @todo Implement this UI better
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

	/**
	 * Check if an addon exists by its GUID
	 * @param string|null $guid The GUID of an addon
	 * @return bool True if the addon exists in the database
	 */
	public static function addon_exists( $guid = null ) {
		return (
			isset( $guid )
			&& (
				Post::get( array(
					'status' => Post::status( 'published' ),
					'content_type' => Post::type( 'addon' ),
					'all:info' => array( 'guid' => $guid )
				) ) != false
			)
		);
	}

	/**
	 * Fetch an addon Post based on its GUID
	 * @param string|null $guid The GUID of the addon Post to look for
	 * @return Post The Post of the requested addon
	 */
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
		// Allow plugins to modify a new addon before it is created.
		Plugins::act( 'handle_addon_before', $info, $versions );

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
			'pubdate' => DateTime::create(),
		);

		$post = self::get_addon( strtoupper( $info[ 'guid' ] ) ); // strtoupper might not be necessary
		if ( ! $post ) {
			/* There is no addon already with the guid. Create a new one. */

			foreach( $main_fields as $k => $v ) {
				$post_fields[ $v ] = $info[ $k ];
			}

			unset( $post );
			$post = Post::create( $post_fields );
			$post->info->hoster = $info[ 'hoster' ];
			$post->update();

			EventLog::log( _t( 'Created post #%s - %s', array( $post->id, $post->title ) ), 'info' );
		}
		else {
			/* Update the existing addon. */
			$post->modify( array(
				'title' => $info[ 'name' ],
				'content' => $info[ 'description' ],
				'slug' => Utils::slugify( $info[ 'name' ] ),
				'pubdate' => DateTime::date_create(), // should this use the date from the ping?,

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

		// Allow plugins to act after a new addon has been created.
		Plugins::act( 'handle_addon_after', $info, $versions );
	}

	public static function save_versions( $post = null, $versions = array() ) {

		if( isset( $post ) && count( $versions ) !== 0 ) {

			$vocabulary = Vocabulary::get( self::CATALOG_VOCABULARY );
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
						'term_display' => $term_display,
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

	/**
	 * Hook implementation on plugin initialization, registers some templates
	 */
	public function action_init ( ) {

		// register our custom guid FormUI control for the post publish page
		$this->add_template( 'guidcontrol', dirname(__FILE__) . '/templates/guidcontrol.php' );
		$this->add_template( 'addon.basepath', dirname(__FILE__) . '/templates/addon.basepath.php' );
		$this->add_template( 'addon.multiple', dirname(__FILE__) . '/templates/addon.multiple.php' );
		$this->add_template( 'addon.single', dirname(__FILE__) . '/templates/addon.single.php' );
		$this->add_template( 'directory_header', dirname(__FILE__) . '/templates/directory_header.php' );

		// register admin pages
		$this->add_template( 'versions_admin', dirname( __FILE__ ) . '/addons_admin.php' );
		$this->add_template( 'version_iframe', dirname( __FILE__ ) . '/version_iframe.php' );
		
		$this->add_template( 'block.addoncart', dirname( __FILE__ ) . '/templates/block.addoncart.php' );

		$this->add_rule( '"remove_addon_version"/slug/version', 'remove_addon_version' );
		$this->add_rule( '"add_to_cart"/slug/version', 'add_to_cart' );
		$this->add_rule( '"remove_from_cart"/index', 'remove_from_cart' );
		$this->add_rule( '"cart"', 'cart' );
	}
	
	/**
	 * Add cart block
	 */
	public function filter_block_list( $blocklist )
	{
		$blocklist[ 'addoncart' ] = _t( 'Addon Cart' );
		return $blocklist;
	}
	
	/**
	 * Add target site and checkout forms to cart block
	 */
	public function action_block_content_addoncart( $block )
	{
		$data = Session::get_set('cart_target', false);
		$target_site = isset($data['target_site']) ? $data['target_site'] : false;
		$block->cart_target_site = $target_site;
		
		// Build form for setting the target site
		// @todo Later: If user is logged in, offer saving locations for re-use
		$target_form = new FormUI(__CLASS__ . "_target");
		$target_form->append(FormControlLabel::wrap(_t("Install addons on this website"), FormControlText::create('target_site', 'session:cart_target')));
		$target_form->target_site->add_validator('validate_required');
		$target_form->target_site->add_validator('validate_url');
		$target_form->append(FormControlSubmit::create('save')->set_caption('Save')->add_class('save_button'));
		$target_form->on_success(array($this, "cart_callback"));

		// Build checkout form
		if($target_site) {
			$target_site .= (substr($target_site, -1) == '/' ? '' : '/');
			$checkout = new FormUI(__CLASS__ . "_checkout");
			$checkout->append(FormControlLabel::wrap(_t("Install addons to <span>%s</span>", array($target_site)), FormControlSubmit::create('checkout')->set_caption('Install')->add_class('proceed_button')));
			// Include JSON payload for the target site
			$cart = Session::get_set("addon_cart", false);
			$checkout->append(FormControlHidden::create('payload')->set_value(json_encode($cart)));
			// Point form to target site
			$checkout->set_properties(array('action' => $target_site . 'install_addons'));
			$block->checkout_form = $checkout;
		}

		$block->target_form = $target_form;
	}
	
	/**
	 * Do a full reload again after processing the form with the target site to make it available when the checkout form displays
	 */
	function cart_callback($form)
	{
		$form->save();
		Utils::redirect();
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

	/**
	 * Remove a version if it's owner or a privileged person requested to do so
	 **/
	public function theme_route_remove_addon_version ( $theme, $params ) {
		$theme->post = Post::get(array(
			'content_type' => Post::type('addon'),
			'slug' => $params['slug'],
		));
		
		// Check if the current user has access to this addon.
		$theme->permitted_versions = $this->addon_permitted_versions($theme->post);
		
		$vocab = Vocabulary::get(self::CATALOG_VOCABULARY);
		$term = $vocab->get_term($params['version']);
		if($term && in_array($term->term, $theme->permitted_versions)) {
			
			$term->delete();
		}
		
		$remaining = $vocab->get_object_terms('addon', $theme->post->id);
		if(count($remaining) == 0)  {
			// No versions left on this addon, so remove it entirely
			$theme->post->delete();
			// We should propably redirect to the $type overview page instead
			Utils::redirect(Site::get_url('habari'));
		}
		
		// Redirect so the displayed page uses the updated version list
		Utils::redirect($theme->post->permalink);
	}

	/**
	 * @param Theme $theme
	 * @param array $params
	 */
	public function theme_route_display_addon ( $theme, $params ) {
		$theme->post = Post::get(array(
			'content_type' => Post::type('addon'),
			'info' => array( 'type' => $params['addon'] ),
			'slug' => $params['slug'],
		));
		
		// Check if the current user has access to this addon.
		$theme->permitted_versions = $this->addon_permitted_versions($theme->post);
		$theme->display( 'addon.single' );
	}
	
	/**
	 * Determine the addon versions the current user has access to
	 * Returns an array of term slugs
	 * @param Addon $addon
	 */
	private function addon_permitted_versions($addon) {
		// Users gain access by contributing via a hosting service, aka GitHub.
		// So we iterate through all the hosting services supported on this install
		// and check if the user contributed to one of the versions.
		// If we stored the hosting service in the addon (as postinfo, not as tag), we could get rid of the iteration.
		$user = User::identify();
		$services = Plugins::filter('socialauth_services', array());
		$permitted_versions = array();
		$versions = $addon->versions;
		if(!$versions) {
			return array();
		}

		foreach($services as $service) {
			foreach($versions as $versionterm) {
				if(isset($versionterm->info->{$service . '_user_id'}) && isset($user->info->{'servicelink_' . $service}) && $versionterm->info->{$service . '_user_id'} == $user->info->{'servicelink_' . $service}) {
					$permitted_versions[] = $versionterm->term;
				}
			}
		}
		
		return $permitted_versions;
	}

	/**
	 * Filter the permalink of addons
	 * @todo There is a way to omit this method by properly naming the arguments in RewriteRule and properly using the URLProperties of Post
	 */
	public function filter_post_permalink( $permalink, $post ) {
		if ($post->content_type == Post::type('addon')) {
			$types = array_flip($this->types);
			$permalink = URL::get(
				'display_addon',
				array(
					'addon' => $post->info->type,
					'slug' => $post->slug
				)
			);
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

		static $post_versions = array();

		if(!isset($post_versions[$post->id])) {

			$vocabulary = $this->vocabulary;
			if ( $vocabulary === false || $post->content_type != Post::type( 'addon' ) ) {
				return false;
			}

			$terms = $vocabulary->get_object_terms( 'addon', $post->id );
			if ( count( $terms ) > 0 ) {
				$terms = $terms->getArrayCopy();
				/**
				 * @var Term $term
				 */
				$self = $this;
				array_walk($terms, function(&$term) use($post, $self) {
					$infos = $term->info->getArrayCopy();
					array_walk($infos, function($value, $key) use($term, $self) {
						$term->$key = $value;
					});
					$term->download_url = URL::get(
						'download_addon',
						array(
							'slug' => $post->slug,
							'version' => $self->version_slugify($term),
							'addon' => $post->info->type
						)
					);
				});
				usort($terms, function(Term $a, Term $b) {
					if(($result = version_compare($a->info->habari_version, $b->info->habari_version)) == 0) {
						return version_compare($a->info->version, $b->info->version);
					}
					if($a->info->habari_version = '?.?.?') {
						return -1;
					}
					if($b->info->habari_version = '?.?.?') {
						return -1;
					}
					return $result;
				});

				$post_versions[$post->id] = new Terms($terms);
			}
			else {
				$post_versions[$post->id] = false;
			}
		}

		return $post_versions[$post->id];
	}

	public function filter_post_call_download_version($output, $post) {
		return reset($post->versions);
	}
	
	/**
	 * Provide a different writing for addon types
	 */
	public function filter_post_info_type_out($type)
	{
		return $this->_types[$type];
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

	public function theme_route_download_addon($theme, $url_args) {
		$addon = Post::get(array('slug' => $url_args['slug']));
		if(!$addon) {
			return; // Don't let people pass weird stuff into here
		}
		
		$version = $url_args['version'];
		$terms = $this->vocabulary->get_object_terms( 'addon', $addon->id );
		foreach($terms as $term) {
			if($version == $this->version_slugify($term)) {
				if(!isset($term->info->url) || !isset($term->info->hash)) {
					Utils::debug($term);
					return; // We must have a download url and a hash to get
				}
		
				// zip file of the requested version is located in /user/files/addon_downloads/{$addonslug}/{$versionslug}/{$hash}/{$addonslug}_{$versionslug}.zip
				$versiondir = '/files/addon_downloads/' . $addon->slug . '/' . $version . '/';
				$dir = $versiondir . $term->info->hash . '/';
				$zipfile = Site::get_dir('user') . $dir . $addon->slug . '_' . $version . '.zip';
				$zipurl = Site::get_url('user') . $dir . $addon->slug . '_' . $version . '.zip';

				if(!is_file($zipfile) || $url_args['refresh'] == 'refresh') {
					// File does not yet exist, prepare directories and create it
					if ( is_writable( Site::get_dir('user') . '/files/' ) ) {
						if ( !is_dir( Site::get_dir('user') . $versiondir ) ) {
							mkdir( Site::get_dir('user') . $versiondir, 0755, true );
						}

						$tmp_dir = sys_get_temp_dir() . '/' . $addon->slug;
						
						// Cleanup: Remove copies from older commits
						exec('rm -rf ' . Site::get_dir('user') . $versiondir . '*');
						exec('rm -rf ' . $tmp_dir);
						exec('rm -rf ' . $zipfile);
						
						if ( !is_dir( Site::get_dir('user') . $dir ) ) {
							mkdir( Site::get_dir('user') . $dir, 0755, true );
						}
						
						Plugins::act('addon_download', $term->info->source, $addon, $term, $tmp_dir);
						if(count(scandir($tmp_dir)) > 2) {
							exec('cd ' . $tmp_dir . ' && zip -9 -r ' . $zipfile . ' *');
						}
					}
				}

				if(is_file($zipfile)) {
					// Everything worked fine - or the file already existed
					Utils::redirect($zipurl);
				}
			}
		}
	}
	
	/**
	 * Save an addon version in the session and provide a "cart" for the user
	 * On "checkout" the plugin list will be transferred to the user's Habari installation
	 * @param Theme $theme
	 * @param Array $params Contains the addon and version slugs
	 */
	public function theme_route_add_to_cart($theme, $params)
	{
		$addon = Post::get(array(
			'content_type' => Post::type('addon'),
			'slug' => $params['slug'],
		));
		if(!$addon) {
			return; // Don't let people pass weird stuff into here
		}

		$version = $params['version'];
		$terms = $this->vocabulary->get_object_terms( 'addon', $addon->id );
		foreach($terms as $term) {
			if($version == $this->version_slugify($term)) {

				$data["download_url"] = URL::get('download_addon', array('slug' => $addon->slug, 'version' => $this->version_slugify($term), 'addon' => $addon->info->type));
				$data["name"] = $addon->title;
				$data["version"] = $term->info->version;
				$data["habari_version"] = $term->info->habari_version;
				$data["type"] = $addon->info->type;
				$data["permalink"] = $addon->permalink;
				$data["description"] = $term->info->description;
				$data["slug"] = $term->term;

				Session::add_to_set("addon_cart", $data);
				Session::notice(_t("You added %s v%s for Habari %s to your cart.", array($addon->title_out, $data["version"], $data["habari_version"]), "addon_catalog"));
			}
		}

		Utils::redirect($addon->permalink);
	}
	
	/**
	 * Remove an addon-version-combination from the session and therefore from the cart
	 * After removing, redirect to the overview page for that type of addon
	 */
	public function theme_route_remove_from_cart($theme, $params)
	{
		$oldlist = Session::get_set("addon_cart");
		for($i=0; $i<count($oldlist); $i++) {
			if($i == $params["index"]) {
				Session::notice(_t("You removed %s v%s for Habari %s from your cart.", array("<a href='" . $oldlist[$i]["permalink"] . "'>" . $oldlist[$i]["name"] . "</a>", $oldlist[$i]["version"], $oldlist[$i]["habari_version"]), "addon_catalog"));
				$type = $oldlist[$i]["type"];
				continue;
			}
			Session::add_to_set("addon_cart", $oldlist[$i]);
		}
		
		Utils::redirect(URL::get("display_addons", array('addon' => $type)));
	}


	public function filter_post_call_version_slugify($unused, $post, $habari, $addon = null)
	{
		return $this->version_slugify($habari, $addon);
	}

	/**
	 * Prepare a version string for use in display and URLs
	 * @param Term|string $habari_version A Term for a version or a Habari version string
	 * @param null|string $addon_version A version string
	 * @return string The slugified string
	 */
	public function version_slugify($habari_version, $addon_version = null) {
		if($habari_version instanceof Term) {
			$addon_version = $habari_version->info->version;
			$habari_version = $habari_version->info->habari_version;
		}

		$replace = function($value) {
			$value = preg_replace('#[^a-z0-9\.]+#i', '-', $value);
			$value = trim($value, '-.');
			return $value;
		};

		$version = array();
		if(($habari_version = $replace($habari_version)) != '') {
			$version[] = $habari_version;
		}
		if(($addon_version = $replace($addon_version)) != '') {
			$version[] = $addon_version;
		}
		return implode('-', $version);
	}

	public function theme_route_addon_update_ping($theme, $params) {
		Plugins::act('addon_update_ping');
	}

}

?>
