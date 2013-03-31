<?php namespace Habari; if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
	$theme->display('header');
	include( "catalog_header.php" ); // @todo a temporary measure.
	?>
	
	<div id="cart">
		<h2><?php _e("Cart"); ?></h2>
		<div class="downloads"><h3>Addons in your cart</h3>
			<table>
				<thead>
					<tr>
						<th>Category</th>
						<th>Addon name</th>
						<th>Version</th>
						<th>Remove</th>
					</tr>
				</thead>
				<tbody>
				
				<?php foreach(Session::get_set("addon_cart", false) as $index => $c): ?>
					<tr>
						<td><?= $c["type"] ?></td>
						<td><a href="<?= $c["permalink"] ?>"><?= $c["name"] ?></a></td>
						<td><?= $c["version"] ?></td>
						<td><a href="<?= Site::get_url('habari') . "/remove_from_cart/" . $index ?>">Remove</a></td>
					</tr>
				<?php endforeach; ?>
			</table>
			
			<h2>Checkout</h2>
			
			<?php
			$target_form->out();
			if($cart_target_site) {
				$checkout_form->out();
			}
			?>
			
		</div>
	</div>
	