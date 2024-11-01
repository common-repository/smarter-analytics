<?php

/**
 * The settings page for the Smarter Analytics plugin
 *
 * @version 1.0
 * @author Kerry Ritter
 */

class SmarterAnalyticsAdmin {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Smarter Analytics Settings', 
            'Smarter Analytics', 
            'manage_options', 
            'smarter-analytics-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'smarter_analytics_option' );
        ?>
        <div class="wrap smarter-analytics-admin">
            <?php screen_icon(); ?>
            <h2>Smarter Analytics</h2>
            <p class="plugin-sub-title">
            To add Google Analytics to your page, you must first add your code(s) to the table below.
            Then, set the default codes for pages and posts on your site. You can override these settings on the individual pages and posts while editing them.
            Use the Exclusion settings to exclude roles, users, and IP addresses or ranges from tracking.
            </p>
            
            <form method="post" action="options.php">
                <?php
                submit_button(); 
                ?>
                <h3>Enter Your Google Analytics Code(s)</h3>
            
                <table class="wp-list-table widefat fixed pages smarter-analytics-admin-table" cellspacing="0" style="width:600px;">
	                <thead>
	                    <tr>
                            <th scope="col" class="manage-column code-id-col">Code ID</th>
                            <th scope="col" class="manage-column ga-code-col">Google Analytics Code</th>
                            <th scope="col" class="manage-column remove-col"></th>
                        </tr>
	                </thead>
                    <tbody>
                    <?php
                    $codes = explode_codes($this->options['codes']);
                    $count = 1;
                    foreach ($codes as $code) {
                        if ($code != "") {
                            $label = ("Analytics Code #" . $count);
                            print '<tr id="smarter-analytics-code-' . $count . '" class="alternate"><td><label>' . $label . '</label></td><td class="inline-edit-row"><span class="input-text-wrap"><input class="smarter-analytics-code" type="text" value="' . $code .'" /></span></td><td class="actions"><button class="button button-default remove-existing-code">Remove</button></td></tr>';
                            $count++;
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><em>Enter at least one Google Analytics code to get started.</em></td>
                        </tr>
                    </tfoot>
                </table>
            
                <?php
                print '<p class="submit"><input type="button" id="add-code-button" class="button button-default" value="Add New Google Analytics Code" /></p>';
                ?>
                
                <h3>Set Google Analytics Code Defaults</h3>

                <?php
                settings_fields( 'smarter_analytics_option_group' );   
                do_settings_sections( 'smarter-analytics-admin' );
                
                $user_tracking_exclusions = $this->options['user_tracking_exclusions'];
                                
                function track_user_row ($userType, $tracking_exclusions) { 
                    $tracking_exclusions_array = explode(",", $tracking_exclusions);
                    
                    $userTypeLower = strtolower($userType);
                    echo '<tr>';
                    echo '<td class="track-user-label alternate">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $userType . '</td>';
                    echo '<td>';
                    
                    $checked = in_array($userTypeLower, $tracking_exclusions_array);
                    
                    echo '<input type="checkbox" class="track-user" data-usertype="' . $userTypeLower .'"' . ($checked == true ? " checked" : " ") . ' />';
                    $tracking_exclusions = str_replace($userTypeLower . ",", "", $tracking_exclusions);

                    echo '</td>';
                    echo '</tr>';
                    return $tracking_exclusions;
                }
                
                ?>
                
                <h3>Configure Analytics Tracking Exclusions</h3>
                <table class="wp-list-table widefat fixed pages smarter-analytics-user-exclusions-table" cellspacing="0" style="width:600px;">
                    <thead>
	                    <tr>
                            <th scope="col" class="manage-column code-id-col" colspan="2">User Tracking Exclusions</th>
                        </tr>
	                </thead>
                    <tbody>
                        <tr>
                            <td colspan="2"><label>Do not track the following user roles:</label></td>
                        </tr>
                        <?php
                        $user_tracking_exclusions = track_user_row("Administrator", $user_tracking_exclusions);
                        $user_tracking_exclusions = track_user_row("Editor",$user_tracking_exclusions);
                        $user_tracking_exclusions = track_user_row("Author",$user_tracking_exclusions);
                        $user_tracking_exclusions = track_user_row("Contributor",$user_tracking_exclusions);
                        $user_tracking_exclusions = track_user_row("Subscriber",$user_tracking_exclusions);
                        ?>
                        <tr>
                            <td class="track-ips-container" colspan="2">
                                <label>Do not track these users:</label>
                                <textarea class="track-users" placeholder="jack,jill,john,jane"><?php print $user_tracking_exclusions; ?></textarea>
                                
                                <p><em>Enter the list of usernames as a comma delimited list (example: jack,jill,john,jane)</em></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                
                <table class="wp-list-table widefat fixed pages smarter-analytics-ip-exclusions-table" cellspacing="0" style="width:600px;">
                    <thead>
	                    <tr>
                            <th scope="col" class="manage-column code-id-col">IP Tracking Exclusions</th>
                        </tr>
	                </thead>
                    <tbody>
                        <tr>
                            <td class="track-ips-container">
                                <label>Do not track these IPs</label>
                                <textarea class="track-ips" placeholder="12.123.12.123,34.345.34.345,56.567.56.567"><?php print $this->options['ip_tracking_exclusions']; ?></textarea>
                                <label>Add IP Range:</label>
                                <input id="add-ip-range-low" value="123.123.0.0" /> <span class="add-ip-range-seperator">to</span> <input id="add-ip-range-high" value="123.123.255.255" />
                                <button class="button button-default" id="add-ip-range-button">Add Range</button>
                                
                                <p><em>Enter the list of IPs as a comma delimited list (example: 12.123.12.123,34.345.34.345,56.567.56.567)</em></p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="your-ip-is">
                                Hint: Your IP is <strong><?php print $_SERVER["REMOTE_ADDR"]; ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <?php
                submit_button(); 
                print '<script src="' . plugins_url("smarter-analytics/smarter-analytics.js") . '" type="text/javascript"></script>'; 
                ?>
            </form>

            <form method="post" class="clear-all">
                <h3>Clear All Plugin Settings</h3>
                <input type="hidden" name="reset" value="reset" />
                <div class="reset-confirm">
                    <p style="color: #8f0222;">Warning: This will remove all of the analytics settings on the site. This operation cannot be undone. Are you sure you want to do this?</p>
                    <input type="submit" class="button button-delete" value="Reset All Plugin Settings" />
                </div>
                <button class="button button-delete show-confirm">Reset All Plugin Settings</button>
            </form>
        </div>
    <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {        
        register_setting( 'smarter_analytics_option_group',  'smarter_analytics_option', array( $this, 'sanitize' ) );

        add_settings_section( 'google_analytics_section', '', array( $this, 'print_section_info' ), 'smarter-analytics-admin' );  

        add_settings_field( 'codes', '', array( $this, 'codes_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
        add_settings_field( 'default', 'Default Google Analytics code for all posts and pages', array( $this, 'default_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
        add_settings_field( 'page_default', 'Default Google Analytics code for all pages', array( $this, 'page_default_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
        add_settings_field( 'post_default', 'Default Google Analytics code for all posts', array( $this, 'post_default_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
        add_settings_field( 'user_tracking_exclusions', '', array( $this, 'user_tracking_exclusions_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
        add_settings_field( 'ip_tracking_exclusions', '', array( $this, 'ip_tracking_exclusions_callback' ),  'smarter-analytics-admin',  'google_analytics_section' );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        if( !empty( $input['codes'] ) )
            $input['codes'] = sanitize_text_field( $input['codes'] );
        
        if( !empty( $input['tracking_exclusions'] ) )
            $input['tracking_exclusions'] = sanitize_text_field( str_replace(" ", "", $input['tracking_exclusions']) );

        return $input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
        print '';
    }

    public function codes_callback() {
        printf(
            '<input type="hidden" id="codes" name="smarter_analytics_option[codes]" value="%s" />',
            esc_attr( $this->options['codes'])
        );
    }
    
    private function generate_code_dropdown($settings_field_id) {
        $codes = explode_codes($this->options['codes']);
        $value = ($this->options[$settings_field_id]) ? $this->options[$settings_field_id] : "";
        
        $html = '<select id="' . $settings_field_id . '" class="default_dropdown" name="smarter_analytics_option[' . $settings_field_id . ']">';

        $default_option = ($settings_field_id == "default") ? "Do not use a code" : "Do not override the global default";
        
        $html .= '  <option value="">' . $default_option .'</option>';
        
        foreach ($codes as $code) {
            if ($code != "") {
                $selected = ($code == $value) ? " selected" : "";
                $html .= '  <option value="' . $code . '"' . $selected . '>' . $code . '</option>';
            }
        }
        
        return $html;
    }

    public function default_callback() {
        print $this->generate_code_dropdown("default");
    }

    public function page_default_callback() {
        print $this->generate_code_dropdown("page_default");
    }

    public function post_default_callback() {
        print $this->generate_code_dropdown("post_default");
    }

    public function user_tracking_exclusions_callback() {
        printf(
            '<input type="hidden" id="user_tracking_exclusions" name="smarter_analytics_option[user_tracking_exclusions]" value="%s" />',
            esc_attr( $this->options['user_tracking_exclusions'])
        );
    }

    public function ip_tracking_exclusions_callback() {
        printf(
            '<input type="hidden" id="ip_tracking_exclusions" name="smarter_analytics_option[ip_tracking_exclusions]" value="%s" />',
            esc_attr( $this->options['ip_tracking_exclusions'])
        );
    }
}




function smarter_analytics_stylesheet(){ ?>
<style type="text/css">
.smarter-analytics-admin .form-table { margin: -25px -10px -35px; }
.smarter-analytics-admin .form-table th { min-width: 350px; font-size: 12px; }

.smarter-analytics-admin .code-id-col { width: 150px; }
.smarter-analytics-admin .ga-code-col { }
.smarter-analytics-admin .remove-col { width: 75px; }

.smarter-analytics-user-exclusions-table { margin-bottom: 30px; }
.smarter-analytics-ip-exclusions-table { margin-bottom: 10px; }
.smarter-analytics-user-exclusions-table textarea, .smarter-analytics-ip-exclusions-table textarea { display: block; width: 100%; min-height: 100px; }

.smarter-analytics-admin .plugin-sub-title { max-width: 600px; }

.smarter-analytics-admin .button-delete { color: #FFFFFF; text-shadow: 0 1px 0 #8f0222; border: 1px solid #8f0222; background: #a90329; background: -moz-linear-gradient(top,  #a90329 0%, #8f0222 44%, #6d0019 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#a90329), color-stop(44%,#8f0222), color-stop(100%,#6d0019)); background: -webkit-linear-gradient(top,  #a90329 0%,#8f0222 44%,#6d0019 100%); background: -o-linear-gradient(top,  #a90329 0%,#8f0222 44%,#6d0019 100%); background: -ms-linear-gradient(top,  #a90329 0%,#8f0222 44%,#6d0019 100%); background: linear-gradient(to bottom,  #a90329 0%,#8f0222 44%,#6d0019 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#a90329', endColorstr='#6d0019',GradientType=0 ); }
.smarter-analytics-admin .button-delete:hover, .smarter-analytics-admin .button-delete:active { color: #FFFFFF; text-shadow: 0 1px 0 #8f0222; border: 1px solid #8f0222; background: #A5263F; background: -moz-linear-gradient(top,  #A5263F 1%, #8f0222 71%, #6d0019 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#A5263F), color-stop(71%,#8f0222), color-stop(100%,#6d0019)); background: -webkit-linear-gradient(top,  #A5263F 1%,#8f0222 71%,#6d0019 100%); background: -o-linear-gradient(top,  #A5263F 1%,#8f0222 71%,#6d0019 100%); background: -ms-linear-gradient(top,  #A5263F 1%,#8f0222 71%,#6d0019 100%); background: linear-gradient(to bottom,  #A5263F 1%,#8f0222 71%,#6d0019 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#A5263F', endColorstr='#6d0019',GradientType=0 ); }
.smarter-analytics-admin .reset-confirm { display: none; }
.smarter-analytics-admin .clear-all { margin-top: 60px; min-height: 100px; max-width: 600px; }
</style>
<?php 
}
add_action('admin_head', 'smarter_analytics_stylesheet');