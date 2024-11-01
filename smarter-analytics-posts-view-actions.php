<?php
add_action('wp_head', 'smarter_analytics_footer');
function smarter_analytics_footer() { 
    global $post;

    if (is_post_type_archive()) {
        $options = get_option( 'smarter_analytics_option' );
        $ua_code = $options["default"];
    }
    else {
        if ($post == null) { return; }
        
        $options = get_option( 'smarter_analytics_option' );
        
        // Get the UA Code
        $post_meta = get_post_meta( $post->ID, 'smarter_analytics_code', true );
        
        $no_code_reason = "";
        $ua_code = null;

        if (empty($post_meta)) {
            if ($post->post_type == "page") {
                if (empty($options["page_default"])) {
                    if (empty($options["default"])) {
                        $ua_code = null;
                        $exclude_reason = "No Google Analytics code has been set for the post or defaults.";
                    }
                    else { // ua_code is a global default
                        $ua_code = $options["default"];
                    }
                }
                else { // ua_code is a page default
                    $ua_code = $options["page_default"];
                    if ($ua_code = "no-show") { 
                        $ua_code = null;
                        $exclude_reason = "This page is set to not include analytics.";
                    }
                }
            }
            
            if ($post->post_type == "post") {
                if (empty($options["post_default"])) {
                    if (empty($options["default"])) {
                        $ua_code = null;
                        $exclude_reason = "No Google Analytics code has been set for the post or defaults.";
                    }
                    else { // ua_code is a global default
                        $ua_code = $options["default"];
                    }
                }
                else { // ua_code is a post default
                    $ua_code = $options["post_default"];
                }
            }
            
            if ($post->post_type == "post") {
                if (empty($options["post_default"])) {
                    if (empty($options["default"])) {
                        $ua_code = null;
                        $exclude_reason = "No Google Analytics code has been set for the post or defaults.";
                    }
                    else { // ua_code is a global default
                        $ua_code = $options["default"];
                    }
                }
                else { // ua_code is a post default
                    $ua_code = $options["post_default"];
                }
            }

            else { // all custom post types
                if (empty($options["default"])) {
                    $ua_code = null;
                    $exclude_reason = "No Google Analytics code has been set for the post or defaults.";
                }
                else { // ua_code is a global default
                    $ua_code = $options["default"];
                }
            }
        }
        else { // ua_code is a post meta
            $ua_code = $post_meta;
            if ($ua_code == "no-show") { 
                $ua_code = null;
                $exclude_reason = "This post is set to not include analytics.";
            }
        }
    }

    if ($ua_code != null) {
        $exclude = false;
        // Check if the user should be excluded
        $user_tracking_exclusions = explode(",", $options["user_tracking_exclusions"]);
        $ip_tracking_exclusions = explode(",", $options["ip_tracking_exclusions"]);
        
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        if ( is_user_logged_in() ) { // Check user role
            $current_user = wp_get_current_user();
            $roles = $current_user->roles;
            $result = array_intersect($roles, $user_tracking_exclusions);
            if(!empty($result)) { // Check role
                $exclude_reason = "The user role '" . $result[0] . "' has been excluded from tracking.";
                $exclude = true;
            }
            else { // Check username
                global $current_user;
                get_currentuserinfo();
                $username = $current_user->user_login;
                $exclude = in_array($username, $user_tracking_exclusions);
            }
        }
        
        if (!$exclude) {
            $real_ip = $_SERVER["REMOTE_ADDR"];
            foreach ($ip_tracking_exclusions as $ip) {
                if (substr_count($ip, "-") > 0) { // IP range
                    $ips = explode("-", $ip);
                    $low = ip2long($ips[0]);
                    $high = ip2long($ips[1]);
                    $real_ip_long = ip2long($real_ip);
                    if($real_ip_long <= $high && $real_ip_long >= $low) { 
                        $exclude_reason = "The IP range " . $ips[0] . " to " . $ips[1] . " has been excluded from tracking. Your IP is " . $real_ip;
                        $exclude = true;
                    }
                }
                else if ($real_ip == $ip) { // Single IP
                    $exclude_reason = "Your IP, " . $real_ip . ", has been excluded from tracking.";
                    $exclude = true;
                }
            }
        }
        
        if ($ua_code == null) { 
            echo "<!-- Smart Analytics has not included a Google Analytics code for the following reason: \n\t" . $no_code_reason . ' -->';
        }
        
        if ($exclude) { echo "<!-- Analytics tracking disabled due to following rule in the Smarter Analytics exclusion policy:\n\t" . $exclude_reason . "\n\t"; }
    ?>
    <script type="text/javascript">

        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '<?php echo $ua_code; ?>', 'auto');
        ga('send', 'pageview');

    </script>

        <?php
        if ($exclude) { echo '-->'; }
    }
}
?>