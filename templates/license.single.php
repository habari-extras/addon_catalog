<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
	/**
	 * Single post display of a license.
	 * In addition to $post->content, shortname, simpletext, and url are also available from postinfo.
	 **/

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

			<div class="license-simpletext">
				<blockquote><code><pre><?php echo $post->info->simpletext; ?>
				</pre></code></blockquote>
			</div>
			<div class="license-content entry-summary">
				<p>
					<?php echo $post->content; ?>
				</p>
				<a href="<?php echo $post->info->url; ?>">Link</a>
			</div>
			<hr>
			<div class="entry-utility">
				<?php
					if ( $tags != null ) {
						?>
							<span class="tags"><?php echo $tags; ?></span>
				<?php 	}
				?>
			</div>
		</div>
	<?php
	$theme->display('footer');
?>
