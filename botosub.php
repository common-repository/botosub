<?php
/*
Plugin Name: Botosub - Newsletters By Facebook Messenger Chatbot
Plugin URI: https://www.botosub.com/
Description: Send Newsletters directly to Facebook Messenger users
Version: 1.5.3
Author: Botosub Dev
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( '!' );


add_action('wp_enqueue_scripts', 'botosub_load_jssdk', 1);
add_action('wp_footer', 'botosub_headercode', 90);
add_action('admin_init', 'botosub_admin_init');
add_action('admin_menu', 'botosub_plugin_menu');
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

add_action(  'transition_post_status',  'on_all_status_transitions', 10, 3 );

$botosub_newsletter_text = "Subscribe to our Newsletters from Messenger Chatbot!";

function botosub_admin_init()
{
    register_setting('bs_botosub_options', 'botosub_page_id');
    register_setting('bs_botosub_options', 'botosub_fb_lang');
    register_setting('bs_botosub_options', 'botosub_text');
    register_setting('bs_botosub_options', 'botosub_text_color');
    register_setting('bs_botosub_options', 'botosub_text_style');
    register_setting('bs_botosub_options', 'botosub_box_bg_color');
    register_setting('bs_botosub_options', 'botosub_switch_color');
    register_setting('bs_botosub_options', 'botosub_plugin_type');
    register_setting('bs_botosub_options', 'botosub_bar_type');
    
    register_setting('bs_botosub_options', 'botosub_sc_title');
    register_setting('bs_botosub_options', 'botosub_sc_title_color');
    register_setting('bs_botosub_options', 'botosub_sc_desc');
    register_setting('bs_botosub_options', 'botosub_sc_desc_color');
    register_setting('bs_botosub_options', 'botosub_sc_bg_color');
    register_setting('bs_botosub_options', 'botosub_sc_img');

    register_setting('bs_botosub_options', 'botosub_mod_title');
    register_setting('bs_botosub_options', 'botosub_mod_title_color');
    register_setting('bs_botosub_options', 'botosub_mod_desc');
    register_setting('bs_botosub_options', 'botosub_mod_desc_color');
    register_setting('bs_botosub_options', 'botosub_mod_bg_color');
    register_setting('bs_botosub_options', 'botosub_mod_img');
    register_setting('bs_botosub_options', 'botosub_mod_img_pos');
    register_setting('bs_botosub_options', 'botosub_mod_img_when');
    register_setting('bs_botosub_options', 'botosub_mod_img_when_val');
    register_setting('bs_botosub_options', 'botosub_mod_img_again');
    
    register_setting('bs_botosub_options', 'botosub_bar_enabled');
    register_setting('bs_botosub_options', 'botosub_scode_enabled');
    register_setting('bs_botosub_options', 'botosub_mod_enabled');
    
    register_setting('bs_botosub_options', 'botosub_asend_post');
    register_setting('bs_botosub_options', 'botosub_asend_page');    
    register_setting('bs_botosub_options', 'botosub_key');
}

function load_custom_wp_admin_style() {
    wp_enqueue_style("botosub-admin-style", plugins_url('/css/adminStyle.css', __FILE__));
    wp_enqueue_script("botosub-admin-plugin", plugins_url('/src/adminScript.js', __FILE__));
}

function botosub_load_jssdk()
{
    $lang = "";
    if (get_option('botosub_fb_lang')==''){ $lang = "en_US"; } else { $lang = esc_attr( get_option('botosub_fb_lang') ); }

    wp_enqueue_style("botosub-plugin", plugins_url('/css/botosubPlugin.css', __FILE__));
    wp_enqueue_script("botosub-plugin", plugins_url('/src/botosubPlugin.min.js', __FILE__));

}

function botosub_plugin_menu()
{
    add_options_page('Botosub', 'Botosub', 'manage_options', 'bs_botosub_options', 'botosub_plugin_options');
}


// AUTO SEND
function on_all_status_transitions( $new_status, $old_status, $post ) {

    $postEnabled = (get_option('botosub_asend_post') === FALSE || get_option('botosub_asend_post') === "") ? false : true;
    $pageEnabled = (get_option('botosub_asend_page') === FALSE || get_option('botosub_asend_page') === "") ? false : true;
    $botosubKey = (get_option('botosub_key') === FALSE) ? false : get_option('botosub_key');
    
    if ((($old_status === "new" || $old_status === "draft" || $old_status === "pending") && $new_status === "publish") && (($post->post_type === "post" && postEnabled) || ($post->post_type === "page" && pageEnabled)) && $botosubKey) {

        global $wp_version;
        $url = "https://www.botosub.com/serv";
        $img = get_the_post_thumbnail_url($post) . "";
        $content = get_permalink($post) . "";

        $body_params = array(
            'm' => 'brp',
            'key' => $botosubKey,
            'data' => array(array("title" => $post->post_title, "item_url" => $content, "image_url" => $img))
        );

        $response = wp_remote_post( $url, array(
            'method' => 'POST',
            'timeout' => 15,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => false,
            'headers' => array("content-type" => "application/json; charset=utf-8"),
            'body' => json_encode($body_params),
            'cookies' => array()
            )
        );
    }
}


function botosub_plugin_options()
{
?>

<div class="wrap">
    <h2>Botosub - Newsletters From Messenger Chatbot</h2>
    <div id="botosub-panel" class="welcome-panel">
        <h2>Welcome to Botosub</h2>
        <a class="welcome-panel-close" href="#" onclick="btsWelcHide()" aria-label="Dismiss the welcome panel">Dismiss</a>
        <ol style="margin-left:20px;">
            <li>Register your Facebook Page in <a target="_blank" href="https://www.botosub.com/">Botosub</a>.</li>
            <li><strong>Add this plugin to your website so that users can sign up with a single click. Existing Facebook page users should also click &#34;Send to Messenger&#34; to receive newsletters.</strong></li>
            <li>Send newsletters in <a target="_blank" href="https://www.botosub.com/">Botosub</a>.</li>
        </ol>
    </div>
    <p>Visit <a target="_blank" href="https://www.botosub.com/">www.botosub.com</a> for more.</p>
    <p><a target="_blank" href="https://www.botosub.com/wp-doc.html">Here</a> is more info on wordpress plugin.</p>
    <form method="post" action="options.php">
        <?php settings_fields('bs_botosub_options'); ?>

        <table class="form-table">
            
            <tr>
                <th colspan="2" style="padding-left: 25px; padding-top: 35px; padding-bottom: 15px;">
                    <div style="font-size:1.5em;">Required configuration</div>
                </th>
            </tr>

            <tr valign="top" style="background-color: #fbdcdc;">
                <th scope="row" style="vertical-align: middle; width: 23%; padding-left: 15px;">Facebook Page ID</th>
                <td style="padding-right: 20px; width:27%;"><input type="text" name="botosub_page_id" onfocus="focus_fbid(this)"
                           value="<?php echo get_option('botosub_page_id'); ?>" required/>
                <small>In order to get Page ID, visit your Facebook Page, click About section then scroll to the bottom.</small></td>
                <th scope="row" style="vertical-align: middle; width: 23%;"><label for="botosub_fb_lang">Language</label></th>
                <td style="width:27%; padding:0;">
                    <input type="text" size="10" placeholder="en_US" name="botosub_fb_lang" value="<?php echo esc_attr( get_option('botosub_fb_lang') ); ?>" /> <small>All supported languages are available at <a target="_blank" href="https://www.botosub.com/facebookLocales.json">here</a>. Some of them are en_US for English (US), de_DE for German, ar_AR for Arabic</small>
                </td>
            </tr>
            
            <tr>
                <th colspan="2" style="padding-left: 25px; padding-top: 35px; padding-bottom: 15px;">
                    <div style="font-size:1.5em;">Automatically send newsletters</div>
                </th>
            </tr>
            
            <tr valign="top" style="background-color: #faf2cc;">
                <!-- botosub key -->
                <th scope="row" colspan="2" style="padding-left: 15px;">Botosub key is required to send newsletters automatically when a <strong>post</strong>/<strong>page</strong> is published.</th>
                <td colspan="2" style="padding:0;">
                    <input type="text" name="botosub_key" onfocus="focus_key(this)" value="<?php echo esc_attr( get_option('botosub_key') ); ?>" style="width: 55%;"/> <small>The key for your page is at the top of the <a target="_blank" href="https://www.botosub.com/dashboard.html">Botosub dashboard</a>. Login &amp; select FB page. Then copy the key at the top, fill this box and press &#x22;Save Changes&#x22; button.</small>
                </td>
            </tr>
            <tr valign="top" style="background-color: #faf2cc;">
                <!-- auto send post -->
                <th scope="row" style="padding-left: 15px;">Automatically send newsletter when a <strong>post</strong> is published.</th>
                <td>
                    <label class="btsSwitch">
                      <input id="botosub_asend_post" name="botosub_asend_post" type="checkbox">
                      <span class="btsSlider btsRound"></span>
                    </label>
                </td>
                <!-- auto send page -->
                <th scope="row">Automatically send newsletter when a <strong>page</strong> is published.</th>
                <td>
                    <label class="btsSwitch">
                      <input id="botosub_asend_page" name="botosub_asend_page" type="checkbox">
                      <span class="btsSlider btsRound"></span>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th colspan="2" style="padding-left: 25px; padding-top: 35px; padding-bottom: 15px;">
                    <div style="font-size:1.5em;">Bar, Modal, Inline</div>
                </th>
            </tr>
            
            <tr valign="top" style="background-color: white;">
                <th scope="row" style="padding-left: 15px;">Plugin Type</th>
                <td>
                    <?php
                    $tab_location_default = 'Modal';

                    $tab_location = (get_option('botosub_plugin_type') == FALSE) ? $tab_location_default : get_option('botosub_plugin_type');

                    ?>

                    <select id="botosub_plugin_type" name="botosub_plugin_type" onchange="botosubChangeHandler()" style="width:40%">
                      <option value="Bar" <?php if ($tab_location == "Top" || $tab_location == "Bottom" || $tab_location == "Bar") echo "selected"; ?>>Bar</option>
                      <option value="Shortcode" <?php if ($tab_location == "Shortcode") echo "selected"; ?>>Inline</option>
                      <option value="Modal" <?php if ($tab_location == "Modal") echo "selected"; ?>>Modal</option>
                    </select>
                </td>

                <!-- bar -->
                <th id="botosub_bar_enabled_title" class="botosub_bar_class" scope="row">Disabled</th>
                <td class="botosub_bar_class">
                    <label class="btsSwitch">
                      <input id="botosub_bar_enabled" name="botosub_bar_enabled" type="checkbox" onchange="return enabledChTitle(this.id);">
                      <span class="btsSlider btsRound"></span>
                    </label>
                </td>

                <!-- scode -->
                <th id="botosub_scode_enabled_title" class="botosub_scode_class" scope="row">Disabled</th>
                <td class="botosub_scode_class">
                    <label class="btsSwitch">
                      <input id="botosub_scode_enabled" name="botosub_scode_enabled" type="checkbox" onchange="return enabledChTitle(this.id);">
                      <span class="btsSlider btsRound"></span>
                    </label>
                </td>

                <!-- modal -->
                <th id="botosub_mod_enabled_title" class="botosub_mod_class" scope="row">Disabled</th>
                <td class="botosub_mod_class">
                    <label class="btsSwitch">
                      <input id="botosub_mod_enabled" name="botosub_mod_enabled" type="checkbox" onchange="return enabledChTitle(this.id);">
                      <span class="btsSlider btsRound"></span>
                    </label>
                </td>

            </tr>
            
            <tr valign="top" style="background-color: white;">
                <!-- bar -->
                <th class="botosub_bar_class" scope="row" style="padding-left: 15px;">Bar Type</th>
                <td class="botosub_bar_class">
                    <?php
                    $bar_type = (get_option('botosub_bar_type') == FALSE) ? 'Bottom' : get_option('botosub_bar_type');
                    ?>

                    <select id="botosub_bar_type" name="botosub_bar_type" style="width:40%">
                      <option value="Top" <?php if ($bar_type == "Top") echo "selected"; ?>>Top</option>
                      <option value="Bottom" <?php if ($bar_type == "Bottom") echo "selected"; ?>>Bottom</option>
                    </select>
                </td>
                
                <th class="botosub_bar_class" scope="row">Text</th>
                <td class="botosub_bar_class">
                    <input type="text" name="botosub_text" style="width:70%;" placeholder="<?php echo $botosub_newsletter_text; ?>" value="<?php echo get_option('botosub_text'); ?>" />
                </td>

            </tr>
            
            <tr valign="top" style="background-color: white;">
                <!-- bar -->
                <th class="botosub_bar_class" scope="row" style=" padding-left: 15px;">Text Color</th>
                <td class="botosub_bar_class"><input type="text" name="botosub_text_color" placeholder="#ffffff" style="width:70%;"
                           value="<?php echo get_option('botosub_text_color'); ?>"/></td>

                <th class="botosub_bar_class" scope="row">Text Style</th>
                <td class="botosub_bar_class">
                    <input type="text" name="botosub_text_style" style="width:70%;" placeholder="font-weight: bold;padding-right: 44px;display: block;" value="<?php echo get_option('botosub_text_style'); ?>" />
                </td>

                <!-- scode -->
                <th class="botosub_scode_class" scope="row" style=" padding-left: 15px;">Title</th>
                <td class="botosub_scode_class">
                    <input type="text" name="botosub_sc_title" style="width:70%;" placeholder="<?php echo $botosub_newsletter_text; ?>" value="<?php echo get_option('botosub_sc_title'); ?>" />
                </td>
                
                <th class="botosub_scode_class" scope="row">Description</th>
                <td class="botosub_scode_class">
                    <input type="text" name="botosub_sc_desc" style="width:70%;" value="<?php echo get_option('botosub_sc_desc'); ?>" />
                </td>

                <!-- modal -->
                <th class="botosub_mod_class" scope="row" style="vertical-align: middle; padding-left: 15px;">When will the plugin pop up</th>
                <td class="botosub_mod_class">
                    <?php $mod_img_when_val = (get_option('botosub_mod_img_when_val') == FALSE) ? "30" : get_option('botosub_mod_img_when_val'); ?>
                    <input type="text" id="botosub_mod_img_when_val" name="botosub_mod_img_when_val" value="<?php echo $mod_img_when_val; ?>" style="margin-right:10px; width:10%" maxlength="2"/>
                    <?php $mod_img_when = (get_option('botosub_mod_img_when') === FALSE) ? "1" : get_option('botosub_mod_img_when'); ?>
                    <select id="botosub_mod_img_when" name="botosub_mod_img_when" style="width:55%" onchange="botosubWhenChHandler()">
                      <option value="0" <?php if ($mod_img_when == "0" || $mod_img_when == "") echo "selected"; ?>>Page Loaded</option>
                      <option value="1" <?php if ($mod_img_when == "1") echo "selected"; ?>>Exit Intent</option>
                      <option value="2" <?php if ($mod_img_when == "2") echo "selected"; ?>>Percent Scrolled</option>
                      <option value="3" <?php if ($mod_img_when == "3") echo "selected"; ?>>After XX Seconds</option>
                    </select>
                </td>
                
                <th class="botosub_mod_class" scope="row" style="vertical-align: middle;">When will the plugin pop up after closed by the user</th>
                <td class="botosub_mod_class">
                    <?php $mod_img_again_val = (get_option('botosub_mod_img_again_val') === FALSE) ? "10" : get_option('botosub_mod_img_again_val'); ?>
                    <input type="text" id="botosub_mod_img_again_val" name="botosub_mod_img_again_val" value="<?php echo $mod_img_again_val; ?>" style="margin-right:10px; width:10%" maxlength="2"/>
                    <?php $mod_img_again = (get_option('botosub_mod_img_again') == FALSE) ? "0" : get_option('botosub_mod_img_again'); ?>
                    <select id="botosub_mod_img_again" name="botosub_mod_img_again" style="width:55%" onchange="botosubAgainChHandler()">
                      <option value="0" <?php if ($mod_img_again == "0" || $mod_img_again == "") echo "selected"; ?>>Always</option>
                      <option value="1" <?php if ($mod_img_again == "1") echo "selected"; ?>>Never</option>
                      <option value="2" <?php if ($mod_img_again == "2") echo "selected"; ?>>Minutes later</option>
                      <option value="3" <?php if ($mod_img_again == "3") echo "selected"; ?>>Hours later</option>
                      <option value="4" <?php if ($mod_img_again == "4") echo "selected"; ?>>Days later</option>
                    </select>
                </td>

            </tr>
            <tr valign="top" style="background-color: white;">
                
                <!-- bar -->
                <th class="botosub_bar_class" scope="row" style=" padding-left: 15px;">Switch Color</th>
                <td class="botosub_bar_class">
                    <input type="text" name="botosub_switch_color" placeholder="#000" value="<?php echo get_option('botosub_switch_color'); ?>" />
                </td>

                <th class="botosub_bar_class" scope="row">Background Color</th>
                <td class="botosub_bar_class"><input type="text" name="botosub_box_bg_color" placeholder="#fed136" style="width:70%;"
                           value="<?php echo get_option('botosub_box_bg_color'); ?>"/></td>
                
                <!-- scode -->
                <th class="botosub_scode_class" scope="row" style=" padding-left: 15px;">Image URL</th>
                <td class="botosub_scode_class">
                    <input type="text" name="botosub_sc_img" placeholder="" style="width:70%;" value="<?php echo get_option('botosub_sc_img'); ?>" />
                </td>
                
                <th class="botosub_scode_class" scope="row" style="vertical-align: middle;">Shortcode</th>
                <td class="botosub_scode_class"><span style="margin-bottom: 20px; font-size: 21px; font-weight: 300; line-height: 1.4;">[botosub_newsletters]</span></td>
                
                <!-- modal -->
                <th class="botosub_mod_class" scope="row" style=" padding-left: 15px;">Title</th>
                <td class="botosub_mod_class">
                    <input type="text" name="botosub_mod_title" style="width:70%;" placeholder="<?php echo $botosub_newsletter_text; ?>" value="<?php echo get_option('botosub_mod_title'); ?>" />
                </td>
                
                <th class="botosub_mod_class" scope="row">Description</th>
                <td class="botosub_mod_class">
                    <input type="text" name="botosub_mod_desc" style="width:70%;" value="<?php echo get_option('botosub_mod_desc'); ?>" />
                </td>

            </tr>
            <tr valign="top" style="background-color: white;">
                <!-- bar -->
                
                <!-- scode -->
                <th class="botosub_scode_class" scope="row" style=" padding-left: 15px;">Title Color</th>
                <td class="botosub_scode_class"><input type="text" name="botosub_sc_title_color" placeholder="#333" style="width:70%;"
                           value="<?php echo get_option('botosub_sc_title_color'); ?>"/></td>
                
                <th class="botosub_scode_class" scope="row">Description Color</th>
                <td class="botosub_scode_class"><input type="text" name="botosub_sc_desc_color" placeholder="#333" style="width:70%;"
                           value="<?php echo get_option('botosub_sc_desc_color'); ?>"/></td>

                <!-- modal -->
                <th class="botosub_mod_class" scope="row" style=" padding-left: 15px;">Image URL</th>
                <td class="botosub_mod_class">
                    <input type="text" name="botosub_mod_img" style="width:70%" placeholder="" value="<?php echo get_option('botosub_mod_img'); ?>" />
                </td>
                
                <th class="botosub_mod_class" scope="row">Image Position</th>
                <td class="botosub_mod_class">
                    <?php $mod_img_pos = (get_option('botosub_mod_img_pos') === FALSE) ? "" : get_option('botosub_mod_img_pos'); ?>
                    <select name="botosub_mod_img_pos" style="width:70%">
                      <option value="0" <?php if ($mod_img_pos == "0" || $mod_img_pos == "") echo "selected"; ?>>Above Title</option>
                      <option value="1" <?php if ($mod_img_pos == "1") echo "selected"; ?>>Above Description</option>
                      <option value="2" <?php if ($mod_img_pos == "2") echo "selected"; ?>>Below Description</option>
                    </select>
                </td>

            </tr>

            <tr valign="top" style="background-color: white;">

                <!-- scode -->
                <th class="botosub_scode_class" scope="row" style=" padding-left: 15px;">Background Color</th>
                <td class="botosub_scode_class">
                    <input type="text" name="botosub_sc_bg_color" placeholder="#ffffff" style="width:70%;" value="<?php echo get_option('botosub_sc_bg_color'); ?>" />
                </td>
                
                <!-- empty -->
                <th class="botosub_scode_class" scope="row" style=" padding-left: 15px;"></th>
                <td class="botosub_scode_class"></td>
                
                <!-- modal -->
                <th class="botosub_mod_class" scope="row" style=" padding-left: 15px;">Title Color</th>
                <td class="botosub_mod_class"><input type="text" name="botosub_mod_title_color" placeholder="#333" style="width:70%;"
                           value="<?php echo get_option('botosub_mod_title_color'); ?>"/></td>

                <th class="botosub_mod_class" scope="row">Description Color</th>
                <td class="botosub_mod_class"><input type="text" name="botosub_mod_desc_color" placeholder="#666" style="width:70%;"
                           value="<?php echo get_option('botosub_mod_desc_color'); ?>"/></td>
                
            </tr>
            
            <tr valign="top" style="background-color: white;">

                <!-- modal -->
                <th class="botosub_mod_class" scope="row" style=" padding-left: 15px;">Background Color</th>
                <td class="botosub_mod_class">
                    <input type="text" name="botosub_mod_bg_color" placeholder="#ffffff" style="width:70%;" value="<?php echo get_option('botosub_mod_bg_color'); ?>" />
                </td>

                <!-- empty -->
                <th class="botosub_mod_class" scope="row" style="padding-left: 15px; display: table-cell;"></th>
                <td class="botosub_mod_class" style="display: table-cell;"></td>

            </tr>
            
        </table>
        
        <p class="submit" style="text-align: center; width: 90%;"><input type="submit" class="button-primary" value="<?php echo 'Save Changes'; ?>"/></p>
    </form>
    <br/><br/>
    
</div>

<style>
    th {
        padding-left: 15px;
    }


</style>

<script>


    var fbidimg = 'https://www.botosub.com/img/plugin/wp/fbid_320.gif';
    var keyimg = 'https://www.botosub.com/img/plugin/wp/key_320.gif';
    var barimg = 'https://www.botosub.com/img/plugin/wp/bar_320.gif';
    var inlineimg = 'https://www.botosub.com/img/plugin/wp/inline_320.gif';
    var modalimg = 'https://www.botosub.com/img/plugin/wp/modal_320.gif';

    function focus_fbid(elem) {

        showToast("fbidc", 18500, "bottomCenter", fbidimg);
    }

    function focus_key(elem) {            

        showToast("keyc", 13000, "topCenter", keyimg);
    }
    
    function focus_bar(elem) {            

        showToast("barc", 7000, "topCenter", barimg);
    }

    function focus_inline(elem) {            

        showToast("inlinec", 28000, "topCenter", inlineimg);
    }
    
    function focus_modal(elem) {            

        showToast("modalc", 4000, "topCenter", modalimg);
    }


    function showToast(tkey, timeout, tpos, imgPath) {

        var fbidc = localStorage.getItem(tkey);

        if (fbidc) {
            localStorage.setItem(tkey, Number(fbidc) + 1);

            if (Number(fbidc) + 1 > 1) {
                return;
            }
        }
        else {
            localStorage.setItem(tkey, "1");
        }

        iziToast.show({
            id: 'haduken',
            theme: 'dark',
            icon: 'icon-contacts',
            timeout: timeout,
            position: tpos,
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            progressBarColor: 'rgb(0, 255, 184)',
            image: imgPath,
            imageWidth: 354,
            layout: 2,
            iconColor: 'rgb(0, 255, 184)'
        });
    }


    btsWelcomePanel();
    function btsWelcomePanel() {
        var welc = localStorage.getItem("btswelc");
        if (welc) {
            document.getElementById('botosub-panel').style.display = "none";
        }
    }

    function btsWelcHide() {
        document.getElementById('botosub-panel').style.display = "none";
        localStorage.setItem("btswelc", "0");
    }

    function botosubChangeHandler(init) {

        var displBar = "", displSCode = "", displModal = "";
        var val = document.getElementById('botosub_plugin_type').value;


        if ( val === 'Shortcode' ) {
            displBar = "none";
            displSCode = "table-cell";
            displModal = "none";
            if (!init) {
                focus_inline();
            }
        } else if ( val === 'Modal' ) {
            displBar = "none";
            displSCode = "none";
            displModal = "table-cell";
            if (!init) {
                focus_modal();
            }
        } else if ( val === 'None' ) {
            displBar = "none";
            displSCode = "none";
            displModal = "none";
        } else {
            displBar = "table-cell";
            displSCode = "none";
            displModal = "none";
            if (!init) {
                focus_bar();
            }
        }

        var barElements = document.getElementsByClassName("botosub_bar_class");
        for (var ind = 0; ind < barElements.length; ind++) {
            barElements[ind].style.display = displBar;
        }

        var scodeElements = document.getElementsByClassName("botosub_scode_class");
        for (var ind = 0; ind < scodeElements.length; ind++) {
            scodeElements[ind].style.display = displSCode;
        }
        
        var modElements = document.getElementsByClassName("botosub_mod_class");
        for (var ind = 0; ind < modElements.length; ind++) {
            modElements[ind].style.display = displModal;
        }



    }
    
    function botosubWhenChHandler() {

        var val = document.getElementById('botosub_mod_img_when').value;
        var whenValElement = document.getElementById('botosub_mod_img_when_val');

       if ( val === '2' || val === '3' ) {
           whenValElement.disabled = false;
           whenValElement.style.display = "inline-block";
       } else {
           whenValElement.disabled = true;
           whenValElement.style.display = "none";
       }
        
    }
    
    function botosubAgainChHandler() {

        var val = document.getElementById('botosub_mod_img_again').value;
        var againValElement = document.getElementById('botosub_mod_img_again_val');

       if ( val === '2' || val === '3' || val === '4' ) {
           againValElement.disabled = false;
           againValElement.style.display = "inline-block";
       } else {
           againValElement.disabled = true;
           againValElement.style.display = "none";
       }
    }
    
    
    btsInit();
    
    function btsInit() {

        botosubChangeHandler(true);
        botosubWhenChHandler();
        botosubAgainChHandler();


        var botosub_bar_enabled = <?php echo (get_option('botosub_bar_enabled') === FALSE || get_option('botosub_bar_enabled') === "") ? "false" : "true"; ?>;
        var botosub_scode_enabled = <?php echo (get_option('botosub_scode_enabled') === FALSE || get_option('botosub_scode_enabled') === "") ? "false" : "true"; ?>;
        var botosub_mod_enabled = <?php echo (get_option('botosub_mod_enabled') === FALSE || get_option('botosub_mod_enabled') === "") ? "false" : "true"; ?>;

        if (botosub_bar_enabled) {
            document.getElementById("botosub_bar_enabled").checked = true;
            enabledChTitle("botosub_bar_enabled");
        }
        if (botosub_scode_enabled) {
            document.getElementById("botosub_scode_enabled").checked = true;
            enabledChTitle("botosub_scode_enabled");
        }
        if (botosub_mod_enabled) {
            document.getElementById("botosub_mod_enabled").checked = true;
            enabledChTitle("botosub_mod_enabled");
        }
        
        var botosub_asend_post = <?php echo (get_option('botosub_asend_post') === FALSE || get_option('botosub_asend_post') === "") ? "false" : "true"; ?>;
        var botosub_asend_page = <?php echo (get_option('botosub_asend_page') === FALSE || get_option('botosub_asend_page') === "") ? "false" : "true"; ?>;

        if (botosub_asend_post) {
            document.getElementById("botosub_asend_post").checked = true;
        }
        if (botosub_asend_page) {
            document.getElementById("botosub_asend_page").checked = true;
        }
    }

    function enabledChTitle(id) {

        if (document.getElementById(id).checked) {
            document.getElementById(id + "_title").innerHTML = "Enabled";
        }
        else {
            document.getElementById(id + "_title").innerHTML = "Disabled";
        }
    }
    
</script>
    <?php

}


function botosub_headercode()
{
//    $form_action = apply_filters('mctb_form_action', null);

    $lang = "";
    $botosub_page_id = get_option("botosub_page_id");

    if (get_option('botosub_fb_lang')==''){ $lang = "en_US"; } else { $lang = esc_attr( get_option('botosub_fb_lang') ); }

    $tab_location_default = 'Modal';
    $tab_location = (get_option('botosub_plugin_type') == FALSE) ? $tab_location_default : get_option('botosub_plugin_type');

    $bar_enabled = (get_option('botosub_bar_enabled') === FALSE || get_option('botosub_bar_enabled') === "") ? "false" : "true";
    $scode_enabled = (get_option('botosub_scode_enabled') === FALSE || get_option('botosub_scode_enabled') === "") ? "false" : "true";
    $mod_enabled = (get_option('botosub_mod_enabled') === FALSE || get_option('botosub_mod_enabled') === "") ? "false" : "true";
    
    ?>
    <script>

        window.botosubVars = {};
        window.botosubVars.botosub_bar_enabled = <?php echo $bar_enabled; ?>;
        window.botosubVars.botosub_scode_enabled = <?php echo $scode_enabled; ?>;
        window.botosubVars.botosub_mod_enabled = <?php echo $mod_enabled; ?>;

        window.botosubVars.page_id = <?php echo $botosub_page_id; ?>;

        // All supported languages available at https://www.botosub.com/facebookLocales.json
        window.botosubVars.lang = "<?php echo $lang; ?>";

    </script>
    <?php


    if ($bar_enabled === "true") {
    ?>
    
    <script>

        window.botosubVars.botosub_bar_position = "";
        var botosub_tab_location = "<?php echo (get_option('botosub_bar_type') == FALSE) ? 'Bottom' : get_option('botosub_bar_type'); ?>";
        if (botosub_tab_location === "Top") {
            window.botosubVars.botosub_bar_position = "top";
        } else {
            window.botosubVars.botosub_bar_position = "bottom";
        }

        window.botosubVars.botosub_bar_bg_color = "<?php
        $myOption_1 = '#fed136';
        $myOption_2 = (get_option('botosub_box_bg_color') == FALSE) ? $myOption_1 : get_option('botosub_box_bg_color');
        echo $myOption_2;
        ?>";
        
        window.botosubVars.botosub_bar_text_style = "<?php echo (get_option('botosub_text_style') == FALSE) ? "font-weight:bold; padding-right: 10px;" : get_option('botosub_text_style'); ?>";
        
        window.botosubVars.botosub_bar_text_color = "<?php echo get_option('botosub_text_color'); ?>";
        
        window.botosubVars.botosub_bar_text = "<?php
                    $myOption = (get_option('botosub_text') == FALSE) ? $botosub_newsletter_text : get_option('botosub_text');
                    echo $myOption;
                    ?>";

        window.botosubVars.botosub_bar_sw_color = "<?php $switchColor = (get_option('botosub_switch_color') == FALSE) ? '#666' : get_option('botosub_switch_color'); ?>";

    </script>

    <?php
    }
    if ($mod_enabled === "true") {
    ?>

    <script>


        window.botosubVars.botosub_mod_title = "<?php echo (get_option('botosub_mod_title') == FALSE) ? $botosub_newsletter_text : get_option('botosub_mod_title'); ?>";
        window.botosubVars.botosub_mod_title_color = "<?php echo (get_option('botosub_mod_title_color') == FALSE) ? "#666" : get_option('botosub_mod_title_color'); ?>";

        window.botosubVars.botosub_mod_desc = "<?php echo (get_option('botosub_mod_desc') == FALSE) ? "" : get_option('botosub_mod_desc'); ?>";
        window.botosubVars.botosub_mod_desc_color = "<?php echo (get_option('botosub_mod_desc_color') == FALSE) ? "" : get_option('botosub_mod_desc_color'); ?>";
        
        window.botosubVars.botosub_mod_img_when = "<?php echo (get_option('botosub_mod_img_when') == FALSE) ? "0" : get_option('botosub_mod_img_when'); ?>";
        window.botosubVars.botosub_mod_img_when_val = <?php echo (get_option('botosub_mod_img_when_val') == FALSE) ? 30 : get_option('botosub_mod_img_when_val'); ?>;

        window.botosubVars.botosub_mod_img_again_val = <?php echo (get_option('botosub_mod_img_again_val') == FALSE) ? "10" : get_option('botosub_mod_img_again_val'); ?>;

        window.botosubVars.botosub_mod_img_again = "<?php echo (get_option('botosub_mod_img_again') == FALSE) ? "0" : get_option('botosub_mod_img_again'); ?>";

        window.botosubVars.botosub_mod_img = "<?php echo (get_option('botosub_mod_img') == FALSE) ? "" : get_option('botosub_mod_img'); ?>";

        window.botosubVars.botosub_mod_img_pos = "<?php echo (get_option('botosub_mod_img_pos') == FALSE) ? "0" : get_option('botosub_mod_img_pos'); ?>";

    </script>

    <?php

    }
    if ($scode_enabled === "true") {
    ?>

        <script>

            <?php $botosub_newsletter_text = "Subscribe to our Newsletters from Messenger Chatbot!"; ?>
            // PLUGIN TITLE
            window.botosubVars.botosub_sc_title = "<?php echo (get_option('botosub_sc_title') == FALSE) ? $botosub_newsletter_text : get_option('botosub_sc_title'); ?>";

            window.botosubVars.sc_title_color = "<?php echo (get_option('botosub_sc_title_color') == FALSE) ? "" : get_option('botosub_sc_title_color'); ?>";
            window.botosubVars.sc_desc = "<?php echo (get_option('botosub_sc_desc') == FALSE) ? "" : get_option('botosub_sc_desc'); ?>";
            window.botosubVars.sc_desc_color = "<?php echo (get_option('botosub_sc_desc_color') == FALSE) ? "" : get_option('botosub_sc_desc_color'); ?>";
            window.botosubVars.sc_bg_color = "<?php echo (get_option('botosub_sc_bg_color') == FALSE) ? "" : get_option('botosub_sc_bg_color'); ?>";
            window.botosubVars.sc_img = "<?php echo (get_option('botosub_sc_img') == FALSE) ? "" : get_option('botosub_sc_img'); ?>";

        </script>
    <?php
    }

}

// SHORTCODE
function botosub_letter_shortcode_init() {
    // Add Shortcode
    function botosub_letter_shortcode( $atts , $content = null ) {

        $scode_enabled = (get_option('botosub_scode_enabled') === FALSE || get_option('botosub_scode_enabled') === "") ? "false" : "true";

        if ($scode_enabled == 'true') {
            return "<div class=\"botosubInline\"></div>";
        }
    }
    add_shortcode( 'botosub_newsletters', 'botosub_letter_shortcode' );
}
add_action('init', 'botosub_letter_shortcode_init');
