<div class="container">

	<p class="pct25">
		<?php _e( 'URL' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_url" id="plugin_details_url" class="styledformelement" value="<?php echo $post->info->url; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Screenhot/Icon URL' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_screenshot" id="plugin_details_screenshot" class="styledformelement" value="<?php echo $post->info->screenshot; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'GUID' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_guid" id="plugin_details_guid" class="styledformelement" value="<?php echo $post->info->guid; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Author' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_author" id="plugin_details_author" class="styledformelement" value="<?php echo $post->info->author; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Author URL' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_author_url" id="plugin_details_author_url" class="styledformelement" value="<?php echo $post->info->author_url; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'License' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_details_license" id="plugin_details_license" class="styledformelement" value="<?php echo $post->info->license; ?>" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Licenses' ); ?>
	</p>
	
	<p class="pct75">
		<?php _e( 'Check all which apply.' ); ?>
	</p>
	
	<?php

		$licenses= PluginServer::licenses();
		$i= 0;
	
		foreach ( $licenses as $value => $name ) {
			
			?>
			
				<p class="pct25">&nbsp;</p>
				<p class="pct75">
			
			<?php
			
			$box= '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="' . $value . '"';
			
			if ( !empty( $post->info->licenses ) && in_array( $value, $post->info->licenses ) ) {
				$box= $box . ' checked="checked"';
			}
			
			$box= $box . '>';
			
			$box= $box . '<label for="' . $name . '">' . $name . '</label>';
			
			echo $box;
			
			?>
			
				</p>
			
			<?php
			
		}
	
	?>

	
</div>
