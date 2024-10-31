<?php
/**
* Plugin Name: Sendlio
* Plugin URI: https://www.sendlio.com
* Version: 1.0.0
* Description: Adds sendlio tracking code into your website
* License: GPLv2 or later
*/

/*  Copyright 2021 Sendlio

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class SendlioIntegration
{
    public function __construct()
    {
        $this->hasBackAccess            = false;
        $this->wooActive                = false;
       
        $this->plugin                   = new stdClass;
        $this->plugin->name             = 'sendlio-integration';
        $this->plugin->displayName      = 'Sendlio';
        $this->plugin->folder           = plugin_dir_path(__FILE__);
        $this->plugin->url              = plugin_dir_url(__FILE__);
        $this->plugin->welcome_notice   = $this->plugin->name . '_welcome_notice';
        
        // Fields (properties)
        $this->fields = new stdClass;

        // Options
        $this->fields->options = new stdClass;
        $this->fields->options->isLoggedIn = false;
        $this->fields->options->sendlio_code = esc_html(wp_unslash(get_option('sendlio_code')));
     
        /* Hooks */
        add_action('init', array( &$this, 'registerFields' ));
        add_action('admin_notices', array( &$this, 'popupNotice' ));
        add_action('wp_ajax_' . $this->plugin->name . '_remove_notices', array( &$this, 'removeNotices' ));
        add_action('admin_menu', array( &$this, 'menuPage' ));
        
        add_action('wp_enqueue_scripts', array( &$this, 'frontScripts' ));
    }

    /**
     * Fields sent to sendlio
     */
    public function registerFields()
    {
        if (is_user_logged_in()) {
            $this->fields->options->isLoggedIn = true;
            $current_user = wp_get_current_user();
            if (!in_array('subscriber', $current_user->roles) && !in_array('customer', $current_user->roles)) {
                $this->hasBackAccess = true;
                return;
            }
        }

        if (class_exists('WooCommerce')) {
            $this->wooActive = true;
        }

        if (isset($current_user->data->user_email)) {
            $this->fields->email = $current_user->data->user_email;
        }
        if (isset($current_user->ID)) {
            $this->fields->first_name = get_user_meta($current_user->ID, 'first_name', true);
            $this->fields->last_name  = get_user_meta($current_user->ID, 'last_name', true);
            $this->fields->last_login = date('Y-m-d H:i:s');
        }
        if (isset($current_user->data->user_registered)) {
            $this->fields->registered = $current_user->data->user_registered;
        }
        if (isset($current_user->data->display_name)) {
            $this->fields->display_name = $current_user->data->display_name;
        }
                    
        if ($this->wooActive) {
            global $woocommerce;

            $customer_id = get_current_user_id();
            $customer = new WC_Customer($customer_id);
            $customer_orders = get_posts(array(
                'meta_key'    => '_customer_user',
                'meta_value'  => $customer_id,
                'post_type'   => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'numberposts' => -1
            ));

            $this->fields->WooCommerce = new stdClass;

            $this->fields->WooCommerce->phone       = get_user_meta($customer_id, 'billing_phone', true);
            $this->fields->WooCommerce->city        = get_user_meta($customer_id, 'billing_city', true);
            $this->fields->WooCommerce->country     = get_user_meta($customer_id, 'billing_country', true);
            $this->fields->WooCommerce->state       = get_user_meta($customer_id, 'billing_state', true);
            $this->fields->WooCommerce->address_1   = get_user_meta($customer_id, 'billing_address_1', true);
            $this->fields->WooCommerce->address_2   = get_user_meta($customer_id, 'billing_address_2', true);
            $this->fields->WooCommerce->company     = get_user_meta($customer_id, 'billing_company', true);

            $this->fields->WooCommerce->orders      = $customer->get_order_count();
            $this->fields->WooCommerce->total_spent = $customer->get_total_spent();
            $this->fields->WooCommerce->currency    = get_woocommerce_currency();

            if (count($customer_orders) != 0) {
                $this->fields->WooCommerce->last_order_date = $customer_orders[0]->post_date;
                $this->fields->WooCommerce->first_order_date = $customer_orders[count($customer_orders) - 1]->post_date;
            }
        }
    }

    /**
     * Show popup notices when plugin is installed
    */
    public function popupNotice()
    {
        global $pagenow;
        if (!get_option($this->plugin->welcome_notice) && current_user_can('manage_options')) {
            if (!($pagenow === 'options-general.php' && isset($_GET['page']) && $_GET['page'] === 'sendlio-integration')) {
                include_once($this->plugin->folder . '/views/notices.php');
            }
        }
    }

    /**
     * Remove welcome popup
     */
    public function removeNotices()
    {
        check_ajax_referer($this->plugin->name . 'sendlio-nonce', 'nonce');
        update_option($this->plugin->welcome_notice, 1);
        exit;
    }

    /**
     * Adding admin scripts
     */
    public function frontScripts()
    {
        // Validate Code
        if (empty($this->fields->options->sendlio_code) || trim($this->fields->options->sendlio_code) === '' || $this->hasBackAccess) {
            return;
        }
        wp_enqueue_script('sendlio-members-script', 'https://app.sendlio.com/members.js', array(), '1.0.0', false);

        wp_enqueue_script('sendlio-track-script', $this->plugin->url . '/js/sendlio-track.js', array(), '1.0.0', false);
        wp_localize_script('sendlio-track-script', 'fields', json_decode(json_encode($this->fields), true));
    }

    /**
     * Add menu page
     */
    public function menuPage()
    {
        add_submenu_page('options-general.php', $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( &$this, 'settingsPage' ));
    }

    /**
     * Settings page
     */
    public function settingsPage()
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('Sorry, you are not allowed to access this page.', 'sendlio-integration'));
        }

        if (! current_user_can('unfiltered_html')) {
            wp_die(__('Sorry, only have read-only access to this page. Ask your administrator for assistance editing.', 'sendlio-integration'));
        }

        // Handle Submit
        if (isset($_REQUEST['submit'])) {
            if (! current_user_can('unfiltered_html')) {
                wp_die(__('Sorry, only have read-only access to this page. Ask your administrator for assistance editing.', 'sendlio-integration'));
            } elseif (! isset($_REQUEST[ $this->plugin->name . 'sendlio-nonce' ])) {
                $this->error = __('nonce field is missing. Settings NOT saved.', 'sendlio-integration');
            } elseif (! wp_verify_nonce($_REQUEST[ $this->plugin->name . 'sendlio-nonce' ], $this->plugin->name)) {
                $this->error = __('Invalid nonce specified. Settings NOT saved.', 'sendlio-integration');
            } else {
                update_option('sendlio_code', sanitize_text_field( $_REQUEST['sendlio_code']) );
                update_option($this->plugin->welcome_notice, 1);
                
                $this->success = __('Settings Saved.', 'sendlio-integration');
            }
        }
        $this->fields->sendlio_code = esc_html(wp_unslash(get_option('sendlio_code')));
        
        include_once($this->plugin->folder . '/views/settings.php');
    }
}
new SendlioIntegration();
