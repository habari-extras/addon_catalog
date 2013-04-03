<?php namespace Habari; ?>
<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
	$theme->display( 'header');
?>
<div id="involved" class="docpage <?php echo $addon_type; ?>">
	<div class="container">
		<div class="row">
			<div id="theme" class="area four columns alpha">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'theme')); ?>">
					<i class="icon-code">a</i>
					<p><strong>Themes</strong></p>
				</a>
			</div>
			<div id="plugin" class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'plugin')); ?>">
					<i class="icon-code">P</i>
					<p><strong>Plugins</strong></p>
				</a>
			</div>
			<div id="bundle" class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'bundle')); ?>">
					<i class="icon-code">b</i>
					<p><strong>Bundles</strong></p>
				</a>
			</div>
			<div id="core" class="area four columns omega">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'core')); ?>">
					<i class="icon-code">C</i>
					<p><strong>Core</strong></p>
				</a>
			</div>
		</div>
	</div>
</div>
<div id="intro_header">
	<div class="container">
		<h3>Recently updated <?php echo ucfirst( $addon_type_name ); ?>.</h3>
	</div>
</div>
<div id="article" class="addons">
	<div class="container">
		<div id="page-selector" class="top sixteen columns">
			<?php echo $theme->prev_page_link(); ?> <?php echo $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php echo $theme->next_page_link(); ?>
		</div>
		<div>
		<?php foreach( $posts as $addon ) { ?>
		<div class="addon_block sixteen columns">
			<div class="block columns three">
				<?php echo AddonCatalogPlugin::screenshot( $addon ); ?>
			</div>
			<div id="overview" class="body columns three">
					<h4><a href="<?php echo $addon->permalink; ?>" title="View <?php echo $addon->title; ?>"><?php echo $addon->title_out; ?></a></h4>
					<span>by <?php echo AddonCatalogPlugin::name_url_list( $addon->info->authors ); ?></span>
					<hr>						
					<div>
						<?php
						for ($z = 0; $z < 5; $z++):
							if ( $addon->rating <= $z * 20 || 0 == $addon->rating) {
								$class1 = '';
								$class2 = 'zero';
							}
							elseif ( $addon->rating > $z * 20 && $addon->rating < $z * 20 + 10 ) {
								$class1 = '';
								$class2 = 'fifty';
							}
							else {
								$class1 = 'hide';
								$class2 = 'hundred';
							}
							?>
							<div class="rating">
								<i class="icon-rating bottom <?php echo $class1; ?>">s</i>
								<i class="icon-rating top"><span class="amount <?php echo $class2; ?>">s</span></i>
							</div>
						<?php endfor; ?>
					</div>
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
		<div id="page-selector" class="bottom sixteen columns">
			<?php echo $theme->prev_page_link(); ?> <?php echo $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php echo $theme->next_page_link(); ?>
		</div>
	</div>
</div>
<div id="ending">
<?php $theme->display ('footer'); ?>
