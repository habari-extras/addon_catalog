<?php

/**
 * Rino theme home page
 */

$theme->display('header');

?>

	<div class="column span-18 prepend-3 last">
	  <div class="column span-10" id="masthead">
		<h1><a href="<?php Site::out_url('habari'); ?>">habari</a></h1>
	  	<h2>spread the news</h2>
	  </div>

	  <div class="column span-8 last" id="subhead">
	  	<p><b>ha&middot;bar&middot;i - </b> noun<br>
	  	Definition - Swahili greetings: What's the news?
			</p>
	  </div>

	  <hr>

	  <div class="column span-18 content last">

		<h2><a href="<?php echo $post->permalink; ?>"><?php echo $post->title_out; ?></a></h2>

		<?php echo $post->content_out; ?>
		
		<h3>Versions</h3>
		  <?php foreach( (array) $post->versions as $version ) : ?>
		  <li>
			<a href="<?php echo $version->url; ?>">
				<?php echo $post->title_out; ?> <?php echo $version->habari_version; ?>-<?php echo $version->version; ?>
			</a>
		  </li>
		  <?php endforeach; ?>

	  </div>
	  

	</div>

<?php $theme->display('footer'); ?>
