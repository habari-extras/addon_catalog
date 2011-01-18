<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } ?>
<?php

	$theme->display('header');
	
	// figure out the tags we should display
	if ( count( $post->tags ) > 0 ) {
		$tags = _t( 'Tagged %s', array( Format::tag_and_list( $post->tags, ', ', ', ' ) ) );
	}
	else {
		$tags = null;
	}

	?>
	
		<div id="post-<?php echo $post->id; ?>" class="<?php echo $theme->post_class( $post ); ?>">
			<h2 class="entry-title">
				<a href="<?php echo $post->permalink; ?>" title="<?php echo Utils::htmlspecialchars( _t( 'Permalink to %s', array( $post->title ), 'plugin_directory' ) ); ?>"><?php echo $post->title_out; ?></a>
			</h2>
			
			<div class="entry-meta">
				<?php echo _t( 'Posted on %1$s at %2$s by %3$s', array( $post->pubdate->date, $post->pubdate->time, $post->author->displayname ), 'plugin_directory' ); ?>
			</div>
			
			<div class="entry-summary">
				<p>
					<?php echo $post->content; ?>
				</p>
			</div>
			
			<div class="entry-utility">
				<?php 
				
					if ( $tags != null ) {
						?>
							<span class="tags"><?php echo $tags; ?></span>
							<span class=meta-sep"> | </span>
						<?php
					}
				
				?>
				<span class="comments-link"><?php $theme->comments_link( $post ); ?></span>
				<?php 
				
					if ( ACL::access_check( $post->get_access(), 'edit' ) ) {
						?>
							<span class="meta-sep"> | </span>
							<span class="edit-link">
								<a class="post-edit-link" href="<?php echo $post->editlink; ?>" title="<?php echo _t( 'Edit Post', 'plugin_directory' ); ?>"><?php echo _t( 'Edit', 'plugin_directory' ); ?></a>
							</span>
						<?php
					}
				
				?>
			</div>
		
		</div>
	
	<?php
	
	$theme->display('footer');

?>