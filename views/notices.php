<?php
/**
 * Install Popup Notice
 */
?>
<div class="notice notice-success is-dismissible <?php echo $this->plugin->name; ?>-sendlio-popup">
	<p>
		<?php printf(__('Thank you for installing %1$s!', 'sendlio-integration'), $this->plugin->displayName); ?>
		<a href="<?php echo admin_url('options-general.php?page=' . $this->plugin->name) ?>"><?php esc_html_e('Click here', 'sendlio-integration'); ?></a> <?php esc_html_e('to configure the plugin.', 'sendlio-integration'); ?>
	</p>
</div>

<script type="text/javascript">
	jQuery(document).ready( function($) {	
        $(document).on('click', '.<?php echo $this->plugin->name; ?>-sendlio-popup button.notice-dismiss', function(e) {
			e.preventDefault();
			$.post(ajaxurl, {
				action: '<?php echo $this->plugin->name . '_remove_notices'; ?>',
				nonce: '<?php echo wp_create_nonce($this->plugin->name . 'sendlio-nonce'); ?>'
			});
			$('.<?php echo $this->plugin->name; ?>-sendlio-popup').remove();
		});
	});
</script>