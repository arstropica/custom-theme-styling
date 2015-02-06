<?php
    /*
    Plugin Name: Custom Theme Styling
    Plugin URI: http://arstropica.com
    Description: Create a dynamically generated stylesheet for your theme
    Version: 5.0
    Author: ArsTropica <info@arstropica.com>
    Author URI: http://arstropica.com
    */

    // Definitions
    global $current_blog;
    
    define('CTS_PLUGIN_FILE', __FILE__);
    define('CTS_PLUGIN_BASENAME', plugin_basename(__FILE__));
    define('CTS_PLUGIN_PATH', trailingslashit(dirname(__FILE__)));
    define('CTS_PLUGIN_DOMAIN', str_replace('http://', '', home_url()));
    if (empty($current_blog->domain) === false)
        define('CTS_PLUGIN_DIR', trailingslashit(str_replace($current_blog->domain, CTS_PLUGIN_DOMAIN, plugins_url('/'))) . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));
    else
        define('CTS_PLUGIN_DIR', trailingslashit(plugins_url('/')) . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));
    
    if ( !defined( 'WP_PLUGIN_DIR' ) )
        define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

    class cts_options{
        var $page;
        var $menupos;
        var $begin = "# BEGIN - CUSTOM THEME STYLING HTACCESS\n##############################################\nOptions +FollowSymLinks\nRewriteEngine on\nRewriteBase /";
        var $end = "##############################################\n# END - CUSTOM THEME STYLING";
        var $standalone;
        var $multisite;
        var $htstate = false;
        

        public function cts_options(){
            $this->menupos = "60.995";
            $full_stylesheet_url = plugins_url('includes/theme_stylesheet.php', __FILE__);
            $local_stylesheet_url = ltrim(parse_url($full_stylesheet_url, PHP_URL_PATH), "/");
            $this->multisite =  "RewriteRule ^([_0-9a-zA-Z-]+/)?files/cts_theme/style\.css $local_stylesheet_url [L]\n";
            $this->standalone = "RewriteRule ^wp-content/uploads/cts_theme/style\.css $local_stylesheet_url\n";
            add_action( 'admin_init', array( &$this, 'cts_settings_init'));
            add_action( 'admin_head' , array( &$this, 'screen_icon' ) );
            add_action( 'admin_menu' , array( &$this, 'cts_menu' ), 10 );
            add_action( 'admin_menu' , array( &$this, 'cts_edit_menu' ), 100 );
            add_action("admin_print_scripts-toplevel_page_cts_options", array(&$this,'cts_scripts'));
            add_action("admin_print_styles-toplevel_page_cts_options", array(&$this,'cts_styles'));
            add_action("wp_head", array(&$this, "cts_custom_stylesheet"), 100);
        }

        public function cts_custom_stylesheet(){
            if(is_multisite()) :
                ?>
                <style type="text/css">
                  @import url("<?php echo get_bloginfo('url') . '/files/cts_theme/style.css'; ?>");
                </style>
                <?php
            else :
                ?>
                <style type="text/css">
                  @import url("<?php echo get_bloginfo('url') . '/wp-content/uploads/cts_theme/style.css'; ?>");
                </style>
                <?php
            endif;
        }

        public function cts_scripts(){
            $script_dir = CTS_PLUGIN_DIR . 'js/';
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-tabs', $script_dir . 'jquery.tools.tabs.min.js', array('jquery'));
            wp_enqueue_script('codemirror-script', $script_dir . 'codemirror/lib/codemirror.js');
            wp_enqueue_script('codemirror-css-mode-script', $script_dir . 'codemirror/mode/css/css.js', array('codemirror-script'));
            wp_enqueue_script('codemirror-ui-script', $script_dir . 'codemirror/ui/codemirror-ui.js', array('codemirror-script'));
        }

        public function cts_styles(){
            $style_dir = CTS_PLUGIN_DIR . 'css/';
            $script_dir = CTS_PLUGIN_DIR . 'js/';
            wp_enqueue_style('jquery-tabs-style', $style_dir . 'tabs.css');
            wp_enqueue_style('jquery-tabs-panes-style', $style_dir . 'tabs-panes.css');
            wp_enqueue_style('codemirror-style', $script_dir . 'codemirror/lib/codemirror.css');
            wp_enqueue_style('codemirror-css-style', $script_dir . 'codemirror/mode/css/css.css');
            wp_enqueue_style('codemirror-ui-style', $script_dir . 'codemirror/ui/css/codemirror-ui.css');
        }

        public function cts_menu(){
            $this->page = add_menu_page( "Custom Theme Styling Settings", "Custom Styles", "manage_options", "cts_options", array(&$this, "cts_page"), CTS_PLUGIN_DIR . 'images/cts_options_icon_16.png', $this->menupos);
        }

        function cts_edit_menu(){
            global $menu, $submenu;
            if (isset($menu[$this->menupos])){
                $menu[$this->menupos][2] = "options-general.php?page=cts_options";
            }

        }
        
        function display_site_admin_fields(){
            $options = get_option('cts_options');
            $user = get_user_by('email', $options['cts_admin_email']);
            ?>
            <h3>Extra profile information</h3>

            <table class="form-table">
                <tr>
                    <th><label for="twitter">Twitter</label></th>

                    <td>
                        <input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
                        <span class="description">Please enter Admin's Twitter Profile URL ex http://twitter.com/username</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="facebook">Facebook</label></th>

                    <td>
                        <input type="text" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text" /><br />
                        <span class="description">Please enter Admin's Facebook URL. ex: http://facebook.com/username</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="linkedin">LinkedIn</label></th>

                    <td>
                        <input type="text" name="linkedin" id="linkedin" value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>" class="regular-text" /><br />
                        <span class="description">Please enter Admin's LinkedIn URL. ex: http://www.linkedin.com/in/username</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="website">Website</label></th>

                    <td>
                        <input type="text" name="website" id="website" value="<?php echo esc_attr( get_the_author_meta( 'website', $user->ID ) ); ?>" class="regular-text" /><br />
                        <span class="description">Please enter Admin's full website URL.  ex: http://example.com</span>
                    </td>
                </tr>
            </table>
            <hr />
            <?php
        }

        public function cts_page(){
            screen_icon('cts_options');
            echo "<h2>Custom Theme Styling Settings</h2>\n";
            if (!function_exists('is_admin')) {
                header('Status: 403 Forbidden');
                header('HTTP/1.1 403 Forbidden');
                exit();
            }
            $action = '';
            $location = "admin.php?page=cts_options"; // based on the location of your sub-menu page

            $message = 'Settings Updated.';
            $options = get_option('cts_options');
        ?><div id="cts_messages" style="display: block !important; width: 100%; clear: both;">
            <style type="text/css">
                .cts_updated{
                    background-color: #FFFFE0;
                    border-color: #E6DB55;
                    -webkit-border-radius: 3px;
                    border-radius: 3px;
                    border-width: 1px;
                    border-style: solid;
                    padding: 0 0.6em;
                    margin: 5px 15px 2px;
                }
                .cts_updated P{
                    margin: 0.5em 0;
                    padding: 2px;                    
                }
                #cts_style TH{
                    display: none;
                }
                .CodeMirror {
                    border: 1px solid #eee;
                    height: 450px;
                }
                .CodeMirror-scroll {
                    height: auto;
                    max-height: 400px;
                    overflow-y: auto;
                    overflow-x: auto;
                    width: 100%;
                }
                .codemirror-ui-button-frame{
                    font-size: 12pt;
                }
            </style>
            <?php
                ob_start();
                if ($options['cts_new_admin'] == 'true'){
                    $site_admin_obj = get_user_by('email', $options['cts_admin_email']);
                    echo'<div id="message" class="cts_updated"><p>New user: <a href="' . admin_url('user-edit.php?user_id='.$site_admin_obj->ID) . '">' . $site_admin_obj->user_login . '</a> has been created</p></div>';
                    $options['cts_new_admin'] = 'false';
                    delete_option( 'adminhash' );
                    update_option( 'new_admin_email', $options['cts_admin_email'] );
                    update_option('cts_options', $options);
                }                                                                   
                $new_admin_email = get_option( 'new_admin_email' );
                $ob_output = ob_get_clean();
                echo $ob_output;
        ?></div><?php

            echo "<div class=\"wrap\">\n";
        ?>
        <!-- the tabs -->
        <ul class="tabs">
            <li><a href="#">Header Settings</a></li>
            <li><a href="#">Stylesheet Images</a></li>
            <li><a href="#">Blog Stylesheet</a></li>
        </ul>
        <!-- tab "panes" -->
        <div class="panes">
            <div class="cts_pane">
                <form name="cts_client" id="cts_client" action="options.php" method="post">
                    <?php settings_fields('cts_options'); ?>
                    <?php do_settings_sections('cts_client'); ?>
                    <hr />
                    <?php $this->display_site_admin_fields(); ?>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                    </p>
                </form>
            </div>
            <div class="cts_pane">
                <?php $this->cts_blueimp(); ?>
            </div>
            <div class="cts_pane" style="min-height: 600px;">
                <form name="cts_style" id="cts_style" action="options.php" method="post">
                    <?php settings_fields('cts_style_options'); ?>
                    <?php do_settings_sections('cts_style'); ?>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                    </p>
                </form>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(function() {
                // setup ul.tabs to work as tabs for each div directly under div.panes
                // CodeMirror + UI
                var textarea = document.getElementById('cts_style_options');
                var codeMirrorOptions = {
                    lineNumbers: true
                    ,height: "500px"
                    ,parserfile: "parsecss.js"
                    ,textWrapping: false
                    ,path : '<?php echo CTS_PLUGIN_DIR; ?>js/codemirror/lib/'
                    ,mode: "css"
                };
                var editor = new CodeMirrorUI(textarea,
                {
                    path : '<?php echo CTS_PLUGIN_DIR; ?>js/codemirror/ui/',
                    searchMode : 'popup'
                },
                codeMirrorOptions
                );

                var tabs = jQuery("ul.tabs").tabs("div.panes div.cts_pane", {
                    onClick: function(event, tabIndex) {
                        editor.mirror.refresh();
                    }
                });
            });
        </script>        
        <?php
            echo "</div>\n";

        }

        function cts_blueimp(){
            global $blog_id;
            $cts_query = (is_multisite()) ? '?cts_blog_id='.$blog_id : '';
        ?>
        <iframe src="<?php echo CTS_PLUGIN_DIR . 'includes/blueimp/index.inc.php'.$cts_query; ?>" height="600" width="100%" frameborder="0" style="overflow: visible;"></iframe>
        <?php 
        }

        function cts_settings_init() {

            register_setting(
            'cts_options',                                           // settings page
            'cts_options',                                           // option name
            array(&$this, 'cts_options_validate')                    // validation
            );

            register_setting(
            'cts_style_options',                                     // settings page
            'cts_style_options',                                     // option name
            array(&$this, 'cts_style_validate')                      // validation
            );

            add_settings_section(
            'cts_settings_client',                                       // section name
            'Branding Settings',                                        // description
            array(&$this, 'cts_settings_client_callback'),               // callback
            'cts_client');                                                      // page

            add_settings_section(
            'cts_settings_stylesheet',                                   // section name
            'Custom Stylesheet',                                        // description
            array(&$this, 'cts_settings_stylesheet_callback'),           // callback
            'cts_style');                                                      // page

            add_settings_field(
            'cts_client_website_name',                            // id
            'Client Website Name',                           // setting title
            array(&$this, 'cts_client_website_name_callback'),    // display callback
            'cts_client',                                           // settings page
            'cts_settings_client'                            // settings section
            );

            add_settings_field(
            'cts_client_website',                            // id
            'Client Website URL',                           // setting title
            array(&$this, 'cts_client_website_callback'),    // display callback
            'cts_client',                                           // settings page
            'cts_settings_client'                            // settings section
            );

            add_settings_field(
            'cts_client_network_name',                            // id
            'Client PMC Name',                           // setting title
            array(&$this, 'cts_client_network_name_callback'),    // display callback
            'cts_client',                                           // settings page
            'cts_settings_client'                            // settings section
            );

            add_settings_field(
            'cts_client_network',                            // id
            'Client PMC URL',                           // setting title
            array(&$this, 'cts_client_network_callback'),    // display callback
            'cts_client',                                           // settings page
            'cts_settings_client'                            // settings section
            );

            add_settings_field(
            'cts_admin_email',                            // id
            'Site Admin Email',                           // setting title
            array(&$this, 'cts_admin_email_callback'),    // display callback
            'cts_client',                                 // settings page
            'cts_settings_client'                         // settings section
            );

            add_settings_field(
            'cts_client_stylesheet',                             // id
            'Extra Stylesheet',                                 // setting title
            array(&$this, 'cts_client_stylesheet_callback'),    // display callback
            'cts_style',                                        // settings page
            'cts_settings_stylesheet'                           // settings section
            );

        }
        
        function cts_save_extra_profile_fields( $user_id ) {
            global $_POST;
            update_usermeta( $user_id, 'twitter', esc_html( $_POST['twitter'] ) );
            update_usermeta( $user_id, 'facebook', esc_html( $_POST['facebook'] ) );
            update_usermeta( $user_id, 'linkedin', esc_html( $_POST['linkedin'] ) );
            update_usermeta( $user_id, 'website', esc_html( $_POST['website'] ) );
        }
        

        function cts_options_validate( $input ){
            $options = get_option( 'cts_options' );
            $user_email = esc_attr($input['cts_admin_email']);
            $user = get_user_by('email', esc_attr($input['cts_admin_email']));
            if ($user){
                $user_id = $user->ID;
                update_option('admin_email', $user_email);
            } else {
                $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                $email_parts = explode("@", $user_email);
                $user_name = $email_parts[0];
                $i =1;
                while(username_exists( $user_name )){
                    $user_name = $user_name . $i;
                    $i ++;
                }
                $user_id = wp_create_user( $user_name, $random_password, $user_email );
                $user = new WP_User($user_id);
                $user->set_role('administrator');
                update_option('admin_email', $user_email);
                delete_option( 'adminhash' );
                update_option( 'new_admin_email', $user_email );
                $options['cts_new_admin'] = "true";
                $input['cts_new_admin'] = "true";
                update_option('cts_options', $options);
            }
            $this->cts_save_extra_profile_fields($user_id);
            return $input;
        }

        function cts_style_validate( $input ){
            $options = get_option( 'cts_style_options' );
            if (! empty($input['cts_client_stylesheet'])){
                if (! is_serialized($input['cts_client_stylesheet'])){
                    $tmp = $input['cts_client_stylesheet'];
                    $input['cts_client_stylesheet'] = serialize($tmp);
                }
            }
            return $input;
        }

        function cts_settings_client_callback() {
            echo '<h3>Blog Settings</h3>';
        }

        function cts_settings_stylesheet_callback() {
            echo '<h3>Stylesheet Editor</h3>';
        }

        function cts_client_website_name_callback() {
            $options = get_option('cts_options');
            echo '<input name="cts_options[cts_client_website_name]" id="cts_client_website_name" type="text" value="' . $options['cts_client_website_name'] . '" class="regular-text" /> Enter website name';
        }

        function cts_client_website_callback() {
            $options = get_option('cts_options');
            echo '<input name="cts_options[cts_client_website]" id="cts_client_website" type="text" value="' . $options['cts_client_website'] . '" class="regular-text" /> Enter website URL (http(s):// ...)';
        }

        function cts_client_network_name_callback() {
            $options = get_option('cts_options');
            echo '<input name="cts_options[cts_client_network_name]" id="cts_client_network_name" type="text" value="' . $options['cts_client_network_name'] . '" class="regular-text" /> Enter PMC name';
        }

        function cts_client_network_callback() {
            $options = get_option('cts_options');
            echo '<input name="cts_options[cts_client_network]" id="cts_client_network" type="text" value="' . $options['cts_client_network'] . '" class="regular-text" /> Enter PMC URL (http(s):// ...)';
        }

        function cts_admin_email_callback() {
            global $wpdb;
            $options = get_option('cts_options');
            $admin_email = empty($options['cts_admin_email']) ? get_option('admin_email') : $options['cts_admin_email'];
            echo '<input name="cts_options[cts_admin_email]" id="cts_admin_email" type="text" value="' . $admin_email . '" class="regular-text" /> Enter Site Admin Email';
            echo '<input name="cts_options[cts_new_admin]" id="cts_new_admin" type="hidden" value="false" />';
        }

        function cts_client_stylesheet_callback() {
            $options = get_option('cts_style_options');
            $tmp = @$options['cts_client_stylesheet'];
            if (is_serialized($tmp)){
                $stylesheet = unserialize($tmp);
            } else{
                if(!isset($options['cts_client_stylesheet'])){
                    $stylesheet = file_get_contents(CTS_PLUGIN_PATH . 'default_css.txt');
                } else {
                    $stylesheet = @$options['cts_client_stylesheet'];
                }
            }
            echo '<textarea name="cts_style_options[cts_client_stylesheet]" id="cts_style_options" style="height: 450px; width: 100%;">' . $stylesheet . '</textarea>';
            echo '<div style="clear:both; width: 100%; height: 0px;"></div>';
        }

        function screen_icon() { ?>
        <style type="text/css">
            #icon-cts_options { background-image: url('<?php echo CTS_PLUGIN_DIR . '/images/cts_options_icon_32.png'; ?>'); background-repeat: no-repeat; }
        </style>
        <?php
        }
        
        function cts_check_htaccess($htaccess=''){
            if (empty($htaccess)) $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            $this->htstate = $this->cts_parse_htaccess_state($htaccess);
            $this->cts_process_htaccess();
            return;
        }

        function cts_process_htaccess(){
            $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            // Backup
            if (is_multisite()) :
                $backup = get_site_option('cts_orig_backup', false, false);
                $backup_status = get_site_option('cts_orig_backup_status', false, false);
            else :
                $backup = get_option('cts_orig_backup', false);
                $backup_status = get_option('cts_orig_backup_status', false);
            endif;
            
            if ($backup === false || $backup_status === false) {
                $backedup = $this->cts_save_backup();
            }
            // Parse
            $this->htstate = $this->cts_parse_htaccess_state($htaccess);
            
            
            // Switch
            switch($this->htstate){
                case 'new':
                case 'unedited':
                    if (is_multisite())
                        $create = $this->cts_openfile($htaccess, "PREPEND", $this->begin . "\n" . $this->multisite . $this->end . "\n");
                    else
                        $create = $this->cts_openfile($htaccess, "PREPEND", $this->begin . "\n" . $this->standalone . $this->end . "\n");
                    if ($create === true) {
                        $this->htstate = 'edited';
                        add_action('admin_notices', array(&$this, 'cts_new_msg'));
                    } else {
                        $this->htstate = 'locked';
                        add_action('admin_notices', array(&$this, 'cts_locked_msg'));
                        $this->htstate = 'edited';
                    }
                    break;
                case 'locked':
                    add_action('admin_notices', array(&$this, 'cts_locked_msg'));
                    break;
                case 'damaged':
                    add_action('admin_notices', array(&$this, 'cts_damaged_msg'));
                    break;
                case 'edited':
                    break;
                case false:
                    $this->cts_check_htaccess();
                    return;
                    break;
            }
            
        }

        function cts_parse_htaccess_state($htaccess=NULL){
            if (empty($htaccess)) $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            $cts_content_arry = $this->cts_parse_htaccess($htaccess);
            // new ?
            if (! file_exists($htaccess)){
                    $htstate = "new";
            } else {
                // Writeable ?
                if (! is_writable($htaccess)){
                    if (false === chmod($htaccess, 0775)) {
                        $htstate = "locked";
                    } else {
                        $htstate = $this->cts_parse_htaccess_state();
                    }
                } elseif ((strpos($cts_content_arry['plugin_begin'], $this->begin) === false) && (strpos($cts_content_arry['plugin_end'], $this->end) === false)){
                    $htstate = "unedited";   
                } elseif((strpos($cts_content_arry['plugin_begin'], $this->begin) !== false) xor (strpos($cts_content_arry['plugin_end'], $this->end)  !== false)){
                    $htstate = "damaged";
                } else {
                    $htstate = "edited";
                }
            }
            return $htstate;
        }

        function cts_parse_htaccess($htaccess = ''){
            if (empty($htaccess)){
                $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            }
            $cts_content_arry = false;
            $cts_pattern = "/((" . preg_quote($this->begin, '/') . ")|($this->end))/s";
            // Check if New HTACCESS
            if (! file_exists($htaccess)){
                $plugin_content = is_multisite() ? $this->multisite : $this->standalone;
                return array('before_plugin' => "", 'plugin_begin' => '', 'plugin' => '', 'plugin_end' => '', 'after_plugin' => "");
            }
            // Check if CTS written
            $orig_content = file_get_contents($htaccess);
            if (! preg_match($cts_pattern, $orig_content, $cts_exists)){
                $cts_content_arry = array('before_plugin' => "", 'plugin_begin' => '', 'plugin' => '', 'plugin_end' => '', 'after_plugin' => $orig_content);
            } else {
                // Check if damaged HTACCESS
                $before_cts_content_arry = explode($this->begin, $orig_content);
                if (count($before_cts_content_arry) > 1){
                    $after_cts_content_arry = explode($this->end, $before_cts_content_arry[1]);
                    // If CTS is already written 
                    if (count($after_cts_content_arry) > 1) {
                        $cts_content_arry = array('before_plugin' => $before_cts_content_arry[0], 'plugin_begin' => $this->begin, 'plugin' => $after_cts_content_arry[0], 'plugin_end' => $this->end, 'after_plugin' => $after_cts_content_arry[1]);
                    } 
                } 
            }
            return $cts_content_arry;
        }

        function cts_save_backup($htaccess=''){
            if (empty($htaccess)) $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            if (is_multisite()) :
                $backup = get_site_option('cts_orig_backup', false, false);
                $status = get_site_option('cts_orig_backup_status', false, false);
            else :
                $backup = get_option('cts_orig_backup', false);
                $status = get_option('cts_orig_backup_status', false);
            endif;
            $orig_content = file_get_contents($htaccess);
            $serialized = serialize($orig_content);
            if ($status === false){
                if ($backup === false) {
                    if (is_multisite()) add_site_option('cts_orig_backup', $serialized);
                    else add_option('cts_orig_backup', $serialized);
                    if (! empty($orig_content)) {
                        add_action('admin_notices', array(&$this, 'cts_backupsuccess_msg'));
                    }
                } 
                if (is_multisite()) add_site_option('cts_orig_backup_status', date("m-d-Y"));
                else add_option('cts_orig_backup_status', date("m-d-Y"));
            }
            if ($backup !== false && $status !== false) return true; 
            return empty($orig_content) ? false : true;
        }
        
        function cts_restore_backup($htaccess=''){
            if (empty($htaccess)) $htaccess = trailingslashit(ABSPATH) . '.htaccess';
            if (is_multisite()) :
                $backup = get_site_option('cts_orig_backup', false, false);
                $status = get_site_option('cts_orig_backup_status', false, false);
            else :
                $backup = get_option('cts_orig_backup', false);
                $status = get_option('cts_orig_backup_status', false);
            endif;
            if ($backup !== false){
                $orig_content = unserialize($backup);
                if($orig_content){
                    $htcreate = $this->cts_openfile($htaccess, "OVERWRITE", (str_replace("\n\n", "\n", $orig_content)));
                    if ($htcreate !== true) {
                        add_action('admin_notices', array(&$this, 'cts_locked_msg'));
                        return false;
                    } else {
                        $this->cts_backuprestore_msg();
                        return true;
                    }                   
                } else {
                    $this->cts_backupnotfound_msg();
                    return false;
                }
            } else {
                $this->cts_backupnotfound_msg();
                return false;
            }
        }

        
        
        function cts_backupsuccess_msg(){
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("A backup has been made of the htaccess file.", false);
            }
        }

        function cts_locked_msg()
        {
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("Sorry, the site .htaccess file appears to be unwritable.", true);
            }
        }

        function cts_edited_msg()
        {
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("The site .htaccess has been edited.", false);
            }
        }

        function cts_backuprestore_msg()
        {
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("The backup htaccess has been restored.", false);
            }
        }

        function cts_backupnotfound_msg(){
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("Sorry, a backup of the htaccess file could not be found.", true);
            }
        }

        function cts_damaged_msg()
        {
            // Only show to admins
            if (current_user_can('manage_options')) {
                $this->cts_admin_message("The site .htaccess file appears to be unwriteable, damaged or incomplete.", true);
            }
        }

        function cts_admin_message($message, $errormsg = false)
        {
            if ($errormsg) {
                echo '<div id="message" class="error">';
            }
            else {
                echo '<div id="message" class="updated fade">';
            }

            echo "<p><strong>$message</strong></p></div>";
        }
        
        function cts_openfile($file, $mode, $input) {
            if ($mode == "READ") {
                if (file_exists($file)) {
                    $handle = fopen($file, "r"); 
                    $output = fread($handle, filesize($file));
                    return $output; // output file text
                } else {
                    return false; // failed.
                }
            } elseif ($mode == "PREPEND") {
                if (file_exists($file) && isset($input)) {
                    $read = file_get_contents($file);
                    $handle = fopen($file, "w");
                    $data = $input.$read;
                    if (!fwrite($handle, $data)) {
                        return false; // failed.
                    } else {
                        return true; // success.
                    }
                } else {
                    return false; // failed.
                }
            } elseif ($mode == "APPEND") {
                if (file_exists($file) && isset($input)) {
                    $read = file_get_contents($file);
                    $handle = fopen($file, "w");
                    $data = $read.$input;
                    if (!fwrite($handle, $data)) {
                        return false; // failed.
                    } else {
                        return true; // success.
                    }
                } else {
                    return false; // failed.
                }
            } elseif ($mode == "OVERWRITE") {
                $handle = fopen($file, "w");
                if (!fwrite($handle, $input)) {
                    return false; // failed.
                } else {
                    return true; //success.
                }
            } elseif ($mode == "READ/WRITE") {        
                if (file_exists($file) && isset($input)) {
                    $handle = fopen($file, "r+");
                    $read = fread($handle, filesize($file));
                    $data = $read.$input;
                    if (!fwrite($handle, $data)) {
                        return false; // failed.
                    } else {
                        return true; // success.
                    }
                } else {
                    return false; // failed.
                }
            } else {
                return false; // failed.
            }
            fclose($handle); 
        }
        
        function activate(){
            #$activate = new cts_options();
            #$activate->cts_check_htaccess();
            $stylesheet = file_get_contents(CTS_PLUGIN_PATH . 'default_css.txt');
            $options = get_option('cts_style_options');
            if(empty($options) || !isset($options['cts_client_stylesheet'])){
                $options = array('cts_client_stylesheet' => serialize($stylesheet));
                add_option('cts_style_options', $options);
            }
        }
        
        function deactivate(){
            $deactivate = new cts_options();
            $deactivate->cts_restore_backup();
            if (is_multisite()) {
                delete_site_option('cts_orig_backup', $serialized);
                delete_site_option('cts_orig_backup_status', date("m-d-Y"));
            }
            else {
                delete_option('cts_orig_backup', $serialized);
                delete_option('cts_orig_backup_status', date("m-d-Y"));
            }
        }
        
    }

    register_activation_hook( __FILE__, array('cts_options', 'activate') );
    register_deactivation_hook( __FILE__, array('cts_options', 'deactivate') );

    add_action( 'init', 'cts_options_init');

    function cts_options_init(){
        global $cts_options;
        if (class_exists("cts_options") && !$cts_options) {
            $cts_options = new cts_options();
            $cts_options->cts_check_htaccess();    
        }    
    }


?>
