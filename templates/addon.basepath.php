<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } 
	$theme->display( 'header');
	include( "catalog_header.php" ); // possibly a temporary measure.
?>
	<div class="content">
		<div id="primary">
			<div id="primarycontent" class="hfeed">
			<?php foreach ( $types as $type => $label): ?>
				<div class="addon-type addon-type-<?php echo $type; ?>">
					<h3 class="entry-title"><a href="<?php echo URL::get("display_addons", array('addon' => $type)); ?>" title="<?php echo $label; ?>"><?php echo $label; ?></a></h3>
				</div>
			<?php endforeach; ?>
			<a href="<?= Site::get_url("habari") . "/cart" ?>">Go to cart</a>
			</div>

		</div>
	</div>
<?php $theme->display ('footer'); ?>
