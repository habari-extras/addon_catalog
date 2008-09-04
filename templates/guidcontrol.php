	<script type="text/javascript">
		$(document).ready(function(){
			$('#generate-guid').click(function(){
				spinner.start();
				$.get(
					'<?php URL::out('auth_ajax', array('context' => 'generate_guid') ); ?>',
					function(data) {
						$('#<?php echo $id; ?>').val(data);
						$('#<?php echo $id; ?>').focus();
						spinner.stop();
					}
				);
			});
		});
		</script>
		<div class="container">
		<button type="button" id="generate-guid" style="float:right">Generate New GUID</button>
		</div>
		<?php include HABARI_PATH . '/system/admin/formcontrols/admincontrol_text.php'; ?>
