<?php
/**
 * Settings Page
 */
?>
<div class="wrap">
    <h2><?php echo $this->plugin->displayName; ?> &raquo; <?php esc_html_e('Settings', 'sendlio-integration'); ?></h2>
    
    <?php if (isset($this->success)): ?>
		<div class="updated fade"><p><?php echo $this->success; ?></p></div>
    <?php endif;?>
    
    <?php if (isset($this->error)): ?>
		<div class="error fade"><p><?php echo $this->error; ?></p></div>
    <?php endif ?>
	
    <div id="poststuff">
		<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><?php esc_html_e('Settings', 'sendlio-integration'); ?></h3>
                        <div class="inside">
							<form action="options-general.php?page=<?php echo $this->plugin->name; ?>" method="post">
                                <p>
									<label for="sendlio_code"><strong><?php esc_html_e('Sendlio Company ID', 'sendlio-integration'); ?></strong></label>
									<input 
                                        name="sendlio_code" 
                                        id="sendlio_code" 
                                        class="regular-text"
                                        <?php echo (! current_user_can('unfiltered_html')) ? ' disabled="disabled" ' : ''; ?> 
                                        placeholder="<?php esc_html_e('Company ID', 'sendlio-integration'); ?>" 
                                        value="<?php echo $this->fields->sendlio_code ?>" 
                                    />
                                    <br>
                                    <small><?php  esc_html_e('Find your Company ID by logging into your Sendlio account and navigating to ‘Settings > Installation’.', 'sendlio-integration'); ?></small>
								</p>
                                <?php if (current_user_can('unfiltered_html')) : ?>
									<?php wp_nonce_field($this->plugin->name, $this->plugin->name . 'sendlio-nonce'); ?>
									<p>
										<input name="submit" 
                                            type="submit" 
                                            name="Submit" 
                                            class="button button-primary" 
                                            value="<?php esc_attr_e('Save', 'sendlio-integration'); ?>" 
                                        />
									</p>
								<?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>