<?php if ( $post ) : ?>
<div class="container">
<strong>Current Versions</strong>
</div>

<hr />
<div class="container">
<?php foreach( (array) $post->versions as $version ) : ?>
<pre>
<?php echo $version->status; ?>: <?php echo $post->title; ?> <?php echo $version->version; ?> -- <?php echo $version->description; ?><br />
</pre>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="container">
<strong>Add New Version</strong>
</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Version Number' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_version[version]" id="plugin_version_version" class="styledformelement" value="" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Version Description' ); ?>
	</p>
	
	<p class="pct75">
		<textarea name="plugin_version[description]" id="plugin_version_version" class="styledformelement"></textarea>
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Arhcive URL' ); ?>
	</p>
	
	<p class="pct75">
		<input type="text" name="plugin_version[url]" id="plugin_version_url" class="styledformelement" value="" />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Minimmum and Maximum Habari Version' ); ?>
	</p>
	
	<p class="pct75">
		<label><input type="text" name="plugin_version[min_habari_version]" id="plugin_version_min_habari_version" class="styledformelement" value="" /> Minimum Habari </label><br />
		<label><input type="text" name="plugin_version[max_habari_version]" id="plugin_version_max_habari_version" class="styledformelement" value="" /> Maximum Habari</label>
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Update Beacon Status' ); ?>
	</p>
	
	<p class="pct75">
		<label>
			<input type="radio" name="plugin_version[status]" id="plugin_version_status_release" class="styledformelement" value="release" checked="checked" />
			Release
		</label><br />
			
		<label>
			<input type="radio" name="plugin_version[status]" id="plugin_version_status_critical" class="styledformelement" value="critical" />
			Critical
		</label><br />
		
		<label>
			<input type="radio" name="plugin_version[status]" id="plugin_version_status_bugfix" class="styledformelement" value="bugfix" />
			Bugfix
		</label><br />
		
		<label>
			<input type="radio" name="plugin_version[status]" id="plugin_version_status_feature" class="styledformelement" value="feature" />
			Feature
		</label><br />
	</p>

</div>

<hr />

<div class="container">

	<p class="pct25">
		<?php _e( 'Requires, Provides, and Recomends (comma seperated)' ); ?>
	</p>
	
	<p class="pct75">
		<label><input type="text" name="plugin_version[requires]" id="plugin_version_requires" class="styledformelement" value="" /> Requires</label><br />
		<label><input type="text" name="plugin_version[provides]" id="plugin_version_provides" class="styledformelement" value="" /> Provides</label><br />
		<label><input type="text" name="plugin_version[recomends]" id="plugin_version_recomends" class="styledformelement" value="" /> Recomends</label>
	</p>

</div>
