<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }

	$theme->display('header');

	// figure out the tags we should display
	if ( count( $post->tags ) > 0 ) {
		$tags = _t( 'Tagged %s', array( Format::tag_and_list( $post->tags, ', ', ', ' ) ) );
	}
	else {
		$tags = null;
	}


	?>
		<div id="post-<?php echo $post->id; ?>" class="addon <?php echo $post->info->type; ?>">
			<h2 class="entry-title">
				<a href="<?php echo $post->permalink; ?>" title="<?php echo Utils::htmlspecialchars( _t( 'Permalink to %s', array( $post->title ), 'plugin_directory' ) ); ?>"><?php echo $post->title_out; ?></a>
			</h2>
			<div class="content">
				<?php echo $post->content_out; ?>
			</div>
			<hr><?php if ( $post->versions !== false ) { ?>

			<div class="info"><h3>Information</h3>
				<ul>
					<li>Author: <a href="<?php echo $post->info->author_url; ?>"><?php echo $post->info->author; ?></a>
					<li>URL: <a href="<?php echo $post->info->url; ?>"><?php echo $post->title; ?></a>
					<li>License: <?php echo $post->license_link; ?>
				</ul>
			</div>

			<div class="downloads"><h3>Available Versions</h3><table>
				<thead><tr><th>Version<th>Release Date<th>Information<th>Download Link</tr>
				</thead>
				<tbody>

				<?php foreach ( $post->versions as $v ) {
					echo "<tr><td>{$v->info->habari_version}-{$v->info->version}<td>" .
						HabariDateTime::date_create( $v->info->release )->format( Options::get( "plugin_directory__date_format", "F j, Y" ) ) .
						"<td><a href='{$v->info->info_url}'>{$v->info->info_url}</a><td><a href='{$v->info->url}'>{$v->info->url}</a></tr>";
				} ?></tbody>
			</table></div>
			<?php }
				else {
					// no versions available
				} ?>
			<div class="entry-utility">
				<?php
					if ( $tags != null ) {
						?>
							<span class="tags"><?php echo $tags; ?></span>
							<span class=meta-sep"> | </span>
						<?php
					}

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
