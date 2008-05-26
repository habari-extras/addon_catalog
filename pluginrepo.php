<?php

class PluginRepo extends XMLRPCServer
{
	public function act_xmlrpc_call()
	{
		Plugins::register(array($this, 'packages_update'), 'xmlrpc', 'packages.update');
		//Plugins::register(array($this, 'packages_list'), 'xmlrpc', 'packages.list');
		//Plugins::register(array($this, 'packages_get'), 'xmlrpc', 'packages.get');
		
		Plugins::register(array($this, 'server_getInfo'), 'xmlrpc', 'server.getInfo');
		
		parent::act_xmlrpc_call();
	}
	
	/**
	 * @todo send the right version for the request params
	 */
	public function packages_update( $returnvalue, $params )
	{
		$packages= Posts::get( array( 'content_type' => 'plugin_directory', 'nolimit' => true ) );
		
		$xml= new SimpleXMLElement('<packages/>');
		$xml->addChild( 'version', time() );
		$xml->addChild( 'signature', 'Awsom3' );
		
		foreach ( $packages as $package ) {
			$package_xml= $xml->addChild('package');
			$version= $package->versions[count($package->versions)-1];
			
			$package_xml->addChild( 'name', utf8_encode($package->title) );
			$package_xml->addChild( 'description', utf8_encode(Utils::truncate(strip_tags($package->content))) ); // this won't work too well
			$package_xml->addChild( 'guid', utf8_encode($package->info->guid) );
			$package_xml->addChild( 'author', utf8_encode($package->info->author) );
			$package_xml->addChild( 'author_url', utf8_encode($package->info->author_url) );
			$package_xml->addChild( 'type', utf8_encode('plugin') );
			$package_xml->addChild( 'version', utf8_encode($version->version) );
			$package_xml->addChild( 'archive_md5', utf8_encode($version->md5) );
			$package_xml->addChild( 'archive_url', utf8_encode($version->url) );
			$package_xml->addChild( 'max_habari_version', utf8_encode($version->max_habari_version) );
			$package_xml->addChild( 'min_habari_version', utf8_encode($version->min_habari_version) );
			//$package_xml->addChild( 'requires', utf8_encode($version->requires) );
			//$package_xml->addChild( 'provides', utf8_encode($version->provides) );
			//$package_xml->addChild( 'recomends', utf8_encode($version->recomends) );
		}
		
		return $xml->asXml();
	}
	
	/**
	 * @todo update this info
	 */
	public function server_getInfo()
	{
		$xml= new SimpleXMLElement('<server/>');
		$info= $xml->addChild('info');
		$info->addChild('name', 'Wicket');
		$info->addChild('url', 'http://packages.drunkenmonkey.org/repo/');
		$info->addChild('browser_url', 'http://packages.drunkenmonkey.org/repo/');
		$info->addChild('description', 'Package repo for testing purposes.');
		$info->addChild('owner', 'Matt Read');
		$info->addChild('signature', 'Awsom3');
		return $xml->asXml();
	}
	
	private function to_xml( SimpleXMLElement $parent, $data )
	{
		foreach ( $data as $key => $value ) {
			$parent->addChild( $key, utf8_encode($value) );
		}
		return $parent;
	}
}

?>
