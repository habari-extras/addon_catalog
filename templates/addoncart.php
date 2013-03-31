<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); }
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
						<th>Release Date</th>
						<th>Information</th>
						<th>Remove</th>
					</tr>
				</thead>
				<tbody>
				
				<?php foreach(Session::get_set("addon_cart", false) as $index => $c): ?>
					<tr>
						<td><?= $c[0]->info->type_out ?></td>
						<td><a href="<?= $c[0]->permalink ?>"><?= $c[0]->title_out ?></a></td>
						<td><?= $c[1]->info->habari_version . "-" . $c[1]->info->version ?></td>
						<td><?= HabariDateTime::date_create($c[1]->info->release)->format( Options::get( "addon_catalog__date_format", "F j, Y" ) ) ?></td>
						<td><a href="<?= $c[1]->info->info_url ?>"><?= $c[1]->info->info_url ?></a></td>
						<td><a href="<?= Site::get_url('habari') . "/remove_from_cart/" . $index ?>">Remove</a></td>
					</tr>
				<?php endforeach; ?>
			</table>
			
			<h2>Checkout</h2>
			<?php $cart_form->out(); ?>
		</div>
	</div>
	