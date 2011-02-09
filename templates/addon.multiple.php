<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } 
	$theme->display( 'header'); ?>
	<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
<?php foreach ( $posts as $post ) { ?>
		<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
			<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
			<div class="entry-content">
			<?php echo $post->content_out; ?>
			</div>

		</div>
	<hr>
<?php } ?>
		</div>

		<div id="page-selector">
			<?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>
		</div>

	</div>
	</div>
<?php $theme->display ('footer'); ?>
