<div id="addons-header">
<small><a href="<?php URL::out( array( "display_addon_basepath", "display_basepath" ) ); ?>"><?php Options::out( 'addon_catalog__basepath', 'explore' ); ?></a>
<?php
	switch (true) {
	case ( $request->display_addons ):?>
/ <a href="<?php URL::out( "display_addons", array( 'addon' => $matched_rule->named_arg_values[ 'addon' ] ) ); ?>"><?php echo $matched_rule->named_arg_values[ 'addon' ]; ?></a>
<?php		break;
 	case ( $request->display_addon ):?>
/ <a href="<?php URL::out( "display_addons", array( 'addon' => $matched_rule->named_arg_values[ 'addon' ] ) ); ?>"><?php echo $matched_rule->named_arg_values[ 'addon' ]; ?></a>
/ <a href="<?php echo $post->permalink; ?>"><?php echo $post->title; ?></a>
<?php		break;
} ?>
</small>
</div>
<hr>
