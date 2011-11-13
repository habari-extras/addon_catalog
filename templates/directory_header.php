<div id="addons-header">
<small><a href="<?php URL::out( array( "display_addon_basepath", "display_basepath" ) ); ?>"><?php Options::out( 'plugin_directory__basepath', 'explore' ); ?></a>
<?php
	switch (true) {
	case ( $request->display_addons ):?>
/ <a href="<?php URL::out( "display_addons", array( 'addon' => $posts[0]->info->type . "s" ) ); ?>"><?php echo $posts[0]->info->type . "s"; ?></a>
<?php		break;
 	case ( $request->display_addon ):?>
/ <a href="<?php URL::out( "display_addons", array( 'addon' => $post->info->type . "s" ) ); ?>"><?php echo $post->info->type . "s"; ?></a>
/ <a href="<?php echo $post->permalink; ?>"><?php echo $post->title; ?></a>
<?php		break;
} ?>
</small>
</div>
<hr>
