<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
	$theme->display( 'header');
	include( "directory_header.php" ); // possibly a temporary measure.
foreach ( $posts as $post ) { ?>
	<div id="post-<?php echo $post->id; ?>" class="addon <?php echo $post->info->type; ?>">
		<div class="entry-head">
			<h2 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
		</div>

		<div class="entry-content">
			<?php echo $post->info->description; ?>
		</div>
	</div>
	<hr>
<?php } ?>
		<div id="page-selector">
			<?php echo $theme->prev_page_link(); ?> <?php echo $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php echo $theme->next_page_link(); ?>
		</div>

<?php $theme->display ('footer'); ?>
