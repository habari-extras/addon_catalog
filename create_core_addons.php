<?php
namespace Habari;
$habari_addon = Posts::get( array( 'content_type' => 'addon', 'slug' => 'habari' ) );

if ( count( $habari_addon ) == 0 ) {
	$habari = Post::create( array(
		'content_type' => Post::type( 'addon' ),
		'title' => 'Habari',
		'content' => file_get_contents( dirname( __FILE__ ) . '/addon.habari.txt' ),
		'status' => Post::status('published'),
		'tags' => array( 'habari' ),
		'pubdate' => DateTime::date_create(),
		'user_id' => User::identify()->id,
		'slug' => 'habari',
	) );
	$habari->info->guid = '7a0313be-d8e3-11db-8314-0800200c9a66';
	$habari->info->url = 'http://habariproject.org';
	$habari->info->description = 'Habari is next-generation blogging.';
	$habari->info->authors = array( array( 'name' => 'The Habari Community', 'url' => 'http://habariproject.org' ) );
	$habari->info->licenses = array( array( 'name' => 'Apache License, Version 2.0', 'url' => 'http://www.apache.org/licenses/LICENSE-2.0' ) );
	$habari->info->type = 'core';
	$habari->info->commit();

	$versions = array(
		'0.9' => array(
			'version' => '0.9',
			'description' => 'Habari release 0.9',
			'info_url' => 'http://wiki.habariproject.org/en/Release_0.9',
			'url' => 'http://habariproject.org/dist/habari-0.9.zip',
			'habari_version' => '0.9',
			'severity' => 'feature',
			'requires' => '',
			'provides' => '',
			'recommends' => '',
			'release' => DateTime::date_create( '2012-11-20' )->sql,
		),
	);

	$this->save_versions( $habari, $versions );

	$wazi = Post::create( array(
		'content_type' => Post::type( 'addon' ),
		'title' => 'Wazi',
		'content' => file_get_contents( Site::$config_path . '/system/themes/wazi/README' ),
		'status' => Post::status('published'),
		'tags' => array( 'habari', 'theme' ),
		'pubdate' => DateTime::date_create(),
		'user_id' => User::identify()->id,
		'slug' => 'wazi',
	) );
	$wazi->info->guid = '';
	$wazi->info->url = 'http://habariproject.org';
	$wazi->info->description = 'Responsive theme included with Habari.';
	$wazi->info->authors = array( array( 'name' => 'The Habari Community', 'url' => 'http://habariproject.org' ) );
	$wazi->info->licenses = array( array( 'name' => 'Apache License, Version 2.0', 'url' => 'http://www.apache.org/licenses/LICENSE-2.0' ) );
	$wazi->info->type = 'theme';
	$wazi->info->commit();

	$versions = array(
		'0.9' => array(
			'version' => '0.9',
			'description' => 'Wazi',
			'info_url' => 'http://wiki.habariproject.org/en/Release_0.9',
			'url' => 'http://habariproject.org/dist/habari-0.9.zip',
			'habari_version' => '0.9',
			'severity' => 'feature',
			'requires' => '',
			'provides' => '',
			'recommends' => '',
			'release' => DateTime::date_create( '2012-11-20' )->sql,
		),
	);

	$this->save_versions( $wazi, $versions );

}
?>
