<?php

class PluginRepo extends ActionHandler
{
	public function act_packages()
	{
		if ( isset( $this->handler_vars['action'] ) ) {
			$action = $this->handler_vars['action'];
			if ( method_exists( $this, "packages_$action" ) ) {
				$this->{"packages_$action"}();
			}
			else {
				Plugins::act( "pluginrepo_packages_$action", $this );
			}
		}
	}
	
	/**
	 * @todo send the right version for the request params
	 */
	public function packages_update()
	{
		extract( $this->handler_vars );
		if ( empty( $habari_version ) ) {
			return;
		}
		$packages = Posts::get( array( 
			'content_type' => 'plugin_directory',
			'nolimit' => true,
			) );
		
		$xml = new SimpleXMLElement('<packages/>');
		
		foreach ( $packages as $package ) {
			if( ! $package->info->guid ) {
				continue;
			}
			$package_xml = $xml->addChild( 'package' );
			$package_xml->addChild( 'description', utf8_encode(Format::summarize(strip_tags($package->content))) );
			
			$package_xml->addAttribute( 'guid', $package->info->guid );
			$package_xml->addAttribute( 'name', $package->title );
			if($package->info->author) $package_xml->addAttribute( 'author', $package->info->author );
			if($package->info->author_url) $package_xml->addAttribute( 'author_url', $package->info->author_url );
			$package_xml->addAttribute( 'type', 'plugin' );
			$package_xml->addAttribute( 'tags', implode( ',', (array) $package->tags ) );
			
			$versions_xml = $package_xml->addChild( 'versions' );
			foreach( $package->versions as $version ) {
				$version_xml = $versions_xml->addChild( 'version' );
				$version_xml->addAttribute( 'version', $version->version );
				if($version->md5) $version_xml->addAttribute( 'archive_md5', $version->md5 );
				if($version->url) $version_xml->addAttribute( 'archive_url', $version->url );
				if($version->habari_version) $version_xml->addAttribute( 'habari_version', $version->habari_version );
			}
		}
		
		echo $xml->asXml();
	}
	
	public function packages_list()
	{
		$packages= Posts::get( array( 'content_type' => 'plugin_directory', 'nolimit' => true ) );
		$xml= new SimpleXMLElement('<packages/>');
		
		foreach ( $packages as $package ) {
			if( ! $package->versions || ! $package->info->guid ) {
				continue;
			}
			$package_xml = $xml->addChild('package');
			$package_xml->addAttribute( 'guid', utf8_encode($package->info->guid) );
		}
		echo $xml->asXml();
	}
}

?>
