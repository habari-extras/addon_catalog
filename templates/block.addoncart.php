<?php namespace Habari; ?>
<div id="cart">
	<div class="rate_title"><span><?php _e('Your Cart'); ?></span><span class="execute"><a href="#install" title="install your addons">Install</a></span></div>
	<hr>
	<div class="installer">
	<?php
		$content->target_form->out();
		if($content->cart_target_site) {
			$content->checkout_form->out();
		}
	?>
	<hr>
	</div>
<?php if(count(Session::get_set("addon_cart", false)) == 0): ?>
	<div class="empty_cart">Your cart is empty</div>
<?php else: ?>
	<div id="cart_downloads">
		<ul>
		<?php foreach( Session::get_set("addon_cart", false) as $index => $c ): ?>
			<li>
				<span><i class="icon-<?php echo $c["type"]; ?>"><?php echo AddonCatalogPlugin::get_type_icon($c["type"]); ?></i></span>
				<span><a href="<?php echo $c["permalink"] ?>"><?php echo $c["name"]; ?></a></span>
				<span style="margin-right:5px;float:right;"><a class="remove_addon" href="<?php echo Site::get_url('site') . "/remove_from_cart/" . $index; ?>"><i class="icon-remove">R</i></a></span>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
</div>

<script>
	$(function(){
		$('body').on('click', '.remove_addon', function(){
			$('#cart_downloads').load($(this).attr('href') + ' #cart_download');
			return false;
		})
	})
</script>