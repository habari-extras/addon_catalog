<?php namespace Habari; ?>
<?php $theme->display('header'); ?>
<div id="involved" class="docpage <?php echo $post->info->type; ?>">
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
<div id="intro_header" class="docpage addon">
	<div class="container">
		<h3><?php echo $post->info->description; ?></h3>
	</div>
</div>
<div id="article" class="addon">
	<div class="container">
		<div id="edit_content" class="single">
			<div class="body columns eleven">
				<h2><?php echo $post->title_out; ?></h2>
				<?php echo $post->content_out; ?>
				<?php if( $post->info->instructions !== '' ) { ?>
					<?php echo $post->info->instructions; ?>
				<?php } ?>
				<?php if( $post->info->help !== '' ) { ?>
					<?php echo $post->info->help; ?>
				<?php } ?>
				<?php if ( $post->versions !== false ) { ?>
				<div class="downloads">					
					<hr>
					<h5>Available Versions</h5>
					<table id="addon_versions" width="100%">
						<thead>
							<tr>
								<th>Version</th>
								<th>Release Date</th>
								<th>Download Link</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $post->versions as $v ) { ?>
							<tr>
								<td><?php echo $v->info->habari_version; ?>-<?php echo $v->info->version; ?></td>
								<td><?php echo DateTime::date_create( $v->info->release )->format( Options::get( "plugin_directory__date_format", "F j, Y" ) ); ?></td>
								<td><a href="<?php echo $v->download_url; ?>">Download <?php echo $v->info->version; ?></a></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<?php } ?>
			</div>
			<div id="addon_sidebar" class="columns four offset-by-one">
			<?php if ( $post->versions !== false ) { ?>
				<div class="download four columns"><a href="<?php echo $post->versions[0]->download_url; ?>" title="download 1.0">Download <?php echo $post->versions[0]->info->version; ?></a></div>
			<?php } ?>
				<div class="info">
					<ul>
						<li>
							<div style="float:left;margin-right: 5px;"><i class="icon-authors">V</i></div>
							<div style="float:left;width:185px;"><?php echo _n( " ", "", count( $post->info->authors ) ); ?> <?php echo AddonCatalogPlugin::name_url_list( $post->info->authors ); ?></div>
						</li>
						<li>
							<div style="float:left;margin-right: 5px;"><i class="icon-link">L</i></div>
							<div style="float:left;width:185px;"><a href="<?php echo $post->info->url; ?>">Developer Site</a></div>
						</li>
						<li>
							<div style="float:left;margin-right: 5px;"><i class="icon-license">l</i></div>
							<div style="float:left;width:185px;"><?php echo _n( " ", "", count( $post->info->licenses ) ); ?> <?php echo AddonCatalogPlugin::name_url_list( $post->info->licenses ); ?></div>
						</li>
						<?php if ( count( $post->tags ) > 0 ) { ?>
						<li>
							<div style="float:left;margin-right: 5px;"><i class="icon-tags">t</i></div>
							<div style="float:left;width:185px;">
								<?php 
									if ( count( $post->tags ) > 0 ) {
										echo _t( '%s', array( Format::tag_and_list( $post->tags, ', ', ', ' ) ) );
									}
								?>
							</div>
						</li>
						<?php } ?>
						<li>
							<span class="rate_title">Ratings</span>
							<hr>
							<div style="margin-left:55px;">
								<div class="rating">
									<i class="icon-rating bottom hide">s</i>
									<i class="icon-rating top"><span class="amount hundred">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom hide">s</i>
									<i class="icon-rating top"><span class="amount hundred">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount fifty">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
							</div>
						</li>
						<li>
							<ol>
								<li>
									<span class="number">5 Stars</span>
									<span class="bar"><span class="color" style="width: 33%;"></span></span>
								</li>
								<li>
									<span class="number">4 Stars</span>
									<span class="bar"><span class="color" style="width: 23.3%;"></span></span>
								</li>
								<li>
									<span class="number">3 Stars</span>
									<span class="bar"><span class="color" style="width: 75%;"></span></span>
								</li>
								<li>
									<span class="number">2 Stars</span>
									<span class="bar"><span class="color" style="width: 13%;"></span></span>
								</li>
								<li>
									<span class="number">1 Star</span>
									<span class="bar"><span class="color"></span></span>
								</li>
							</ol>
						</li>
						<li>
							<span class="rate_title">My Rating</span>
							<hr>
							<div id="my_rating">
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
								<div class="rating">
									<i class="icon-rating bottom">s</i>
									<i class="icon-rating top"><span class="amount zero">s</span></i>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="ending">
<?php $theme->display('footer'); ?>