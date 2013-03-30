<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }

	$theme->display('header');

	// figure out the tags we should display
	if ( count( $post->tags ) > 0 ) {
		$tags = _t( 'Tagged %s', array( Format::tag_and_list( $post->tags, ', ', ', ' ) ) );
	}
	else {
		$tags = null;
	}

	include( "catalog_header.php" ); // @todo a temporary measure.
	?>
		<div id="post-<?php echo $post->id; ?>" class="addon <?php echo $post->info->type; ?>">
			<h2 class="entry-title">
				<a href="<?php echo $post->permalink; ?>" title="<?php echo Utils::htmlspecialchars( _t( 'Permalink to %s', array( $post->title ), 'addon_catalog' ) ); ?>"><?php echo $post->title_out; ?></a>
			</h2>
			<div class="entry-content">
				<?php echo $post->content_out; ?>
			</div>
<?php		if( $post->info->help !== '' ) { ?>
			<h4>More Information</h4>
				<?php echo $post->info->help;
			} ?>

			<hr><?php if ( $post->versions !== false ) : ?>

			<div class="info"><h3><em>Stuff that won't show up on the finished product</em></h3>
				<ul>
					<li>repo : <?php echo $post->info->repo_url; ?>
					<li>tree : <?php echo $post->info->tree_url; ?>
					<li>blob : <?php echo $post->info->blob_url; ?>
					<li>json : <small><pre style="width:100%;overflow-x:scroll"><?php echo htmlspecialchars($post->info->json); ?></pre></small>
					<li>xml : <small><pre style="width:100%;overflow-x:scroll"><?php echo htmlspecialchars($post->info->xml); ?></pre></small>
					<li>guid : <?php echo $post->info->guid; ?>
					<li>type : <?php echo $post->info->type; ?>
<?php			if( $post->info->parent != false ) { ?>
					<li>parent theme : <?php echo $post->info->parent;
				} ?>
					<li>username : <?php echo User::get( $post->user_id )->username; ?>
				</ul>
			</div>

			<div class="info"><h3>Information <em>(that will)</em></h3>
				<ul>
<?php			if( $post->info->screenshot_url != false ) { ?>
					<li>screenshot : <img src="<?php echo $post->info->screenshot_url . '">';
				} ?>
					<li>Author<?php echo _n( " ", "s ", count( $post->info->authors ) ); ?>: <?php echo AddonCatalogPlugin::name_url_list( $post->info->authors ); ?>
					<li>URL : <a href="<?php echo $post->info->url; ?>"><?php echo $post->info->url; ?></a>
					<li>License<?php echo _n( " ", "s ", count( $post->info->licenses ) ); ?>: <?php echo AddonCatalogPlugin::name_url_list( $post->info->licenses ); ?>
				</ul>
			</div>

			<div class="downloads"><h5>Available Versions</h5><table>
				<thead>
					<tr>
						<th>Version</th>
						<th>Release Date</th>
						<th>Information</th>
						<th>Download Link</th>
					</tr>
				</thead>
				<tbody>

				<?php foreach ( $post->versions as $v ): ?>
				<tr>
					<td><?= "{$v->info->habari_version}-{$v->info->version}" ?>
					<?php if(in_array($v->term, $permitted_versions)): ?>
						<a href="<?php echo Site::get_url('habari') . '/remove_addon_version/' . $post->slug . '/' . $v->term; ?>">[<?php _e('Remove', 'addon_catalog'); ?>]</a>
					<?php endif; ?>
					</td>
					<td><?= HabariDateTime::date_create($v->info->release)->format( Options::get( "addon_catalog__date_format", "F j, Y" ) ) ?></td>
					<td><a href="<?= $v->info->info_url ?>"><?= $v->info->info_url ?></a></td>
					<td><a href="<?= $v->download_url ?>"><?= $v->download_url ?></a></td>
				</tr>
				<?php endforeach; ?>

				</tbody>
			</table></div>
			<?php else: ?>
					<!-- // no versions available -->
			<?php endif; ?>
			<div class="entry-utility">
				<?php
					if ( $tags != null ) {
						?>
							<span class="tags"><?php echo $tags; ?></span>
						<?php
					}

					if ( ACL::access_check( $post->get_access(), 'edit' ) ) {
						?>
							<span class="meta-sep"> | </span>
							<span class="edit-link">
								<a class="post-edit-link" href="<?php echo $post->editlink; ?>" title="<?php echo _t( 'Edit Post', 'addon_catalog' ); ?>"><?php echo _t( 'Edit', 'addon_catalog' ); ?></a>
							</span>
						<?php
					}

				?>
			</div>
		</div>

	<?php
	$theme->display('footer');

?>
