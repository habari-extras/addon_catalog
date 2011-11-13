<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
	$theme->display( 'header'); ?>
<?php foreach ( $posts as $post ) { ?>
	<div id="post-<?php echo $post->id; ?>" class="addon <?php echo $post->info->type; ?>">
		<div class="entry-head">
			<!-- @todo We should just be overriding the permalink
			<h2 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
			-->
			<h2 class="entry-title"><a href="<?php echo URL::get("display_addon", array('addon' => $post->info->type.'s', 'slug' => $post->slug)); ?>" title="<?php echo $post->title; ?>"><?php echo $post->title; ?></a></h2>
		</div>

		<div class="entry-content">
			<?php echo $post->info->description; ?>

			<div class="downloads"><h5>Available Versions</h5><table>
				<thead><tr><th>Version<th>Release Date<th>Information<th>Download Link</tr>
				</thead>
				<tbody>

				<?php foreach ( $post->versions as $v ) {
					echo "<tr><td>{$v->info->habari_version}-{$v->info->version}<td>" .
						HabariDateTime::date_create( $v->info->release )->format( Options::get( "plugin_directory__date_format", "F j, Y" ) ) .
						"<td><a href='{$v->info->info_url}'>{$v->info->info_url}</a><td><a href='{$v->info->url}'>{$v->info->url}</a></tr>";
				} ?></tbody>
			</table></div>
		</div>
	</div>
	<hr>
<?php } ?>
		<div id="page-selector">
			<?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>
		</div>

<?php $theme->display ('footer'); ?>
