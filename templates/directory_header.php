<?php namespace Habari; ?>
<div id="involved" class="subpage addons">
	<div class="container">
		<div class="row">
			<div class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'theme')); ?>">
					<i class="icon-code">a</i>
					<p><strong>Themes</strong></p>
				</a>
			</div>		
			<div class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'plugin')); ?>">
					<i class="icon-code">P</i>
					<p><strong>Plugins</strong></p>
				</a>
			</div>
			<div class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'bundle')); ?>">
					<i class="icon-code">b</i>
					<p><strong>Bundles</strong></p>
				</a>
			</div>
			<div class="area four columns">
				<a href="<?php echo URL::get("display_addons", array('addon' => 'core')); ?>">
					<i class="icon-code">C</i>
					<p><strong>Core</strong></p>
				</a>
			</div>
		</div>
	</div>
</div>