<?php namespace Habari; ?>
<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } 
	$theme->display( 'header');
?>
<?php $theme->display('directory_header'); ?>
<div id="intro_header">
	<div class="container">
		<h3>Recently updated Plugins, Themes and Bundles.</h3>
	</div>
</div>
<div id="article" class="addons">
	<div class="container">
		<?php foreach( $addons as $addon ) { ?>
			<div class="addon_block sixteen columns">
				<div class="block columns three">
					<a href="<?php echo $addon->permalink; ?>" title="View <?php echo $addon->title; ?>"><?php echo AddonCatalogPlugin::screenshot( $addon ); ?></a>
				</div>
				<div class="body columns three">
					<h4><a href="<?php echo $addon->permalink; ?>" title="View <?php echo $addon->title; ?>"><?php echo $addon->title_out; ?></a></h4>
					<span>by <?php echo AddonCatalogPlugin::name_url_list( $addon->info->authors ); ?></span>
					<?php if( $addon->versions ) { ?>
					<hr>						
					<span class="meta">Habari <?php echo $addon->versions[0]->info->habari_version; ?> or higher</span>
					<?php } ?>
				</div>
				<div class="body columns eight">
					<p>
					<?php 
						if ( count( $addon->tags ) > 0 ) {
							echo _t( 'Tagged %s', array( Format::tag_and_list( $addon->tags, ', ', ', ' ) ) );
						}
					?>
					</p>
					<p><?php echo $addon->content_excerpt; ?></p>
				</div>
			</div>
		<?php } ?>
		</div>
</div>
<?php $theme->display ('quicklinks'); ?>
<?php $theme->display ('footer'); ?>