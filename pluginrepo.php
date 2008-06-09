<?php

class PluginRepo extends ActionHandler
{
	public function act_packages()
	{
		$packages = Posts::get( array( 
			'content_type' => 'plugin_directory',
			'nolimit' => true,
			) );
		
		$xml = new SimpleXMLElement('<packages/>');
		
		foreach ( $packages as $package ) {
			if( ! $package->info->guid ) {
				continue;
			}
			$package_node = $xml->addChild( 'package' );
			$package_node->addChild( 'description', utf8_encode(Format::summarize(strip_tags($package->content))) );
			
			$package_node->addAttribute( 'guid', $package->info->guid );
			$package_node->addAttribute( 'name', $package->title );
			if ( $package->info->author ) $package_node->addAttribute( 'author', $package->info->author );
			if ( $package->info->author_url ) $package_node->addAttribute( 'author_url', $package->info->author_url );
			$package_node->addAttribute( 'type', 'plugin' );
			$package_node->addAttribute( 'tags', implode( ',', (array) $package->tags ) );
			
			$versions_node = $package_node->addChild( 'versions' );
			foreach( $package->versions as $version ) {
				if ( $version->habari_version ) {
					$version_node = $versions_node->addChild( 'version', $version->description );
					$version_node->addAttribute( 'version', $version->version );
					$version_node->addAttribute( 'archive_md5', $version->md5 );
					$version_node->addAttribute( 'archive_url', $version->url );
					$version_node->addAttribute( 'habari_version', $version->habari_version );
				}
			}
		}
		
		ob_clean();
		header( 'Content-Type: application/xml' );
		echo $xml->asXml();
	}
}

?>
