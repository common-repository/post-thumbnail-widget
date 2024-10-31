<?php
/**
Plugin Name: Post Thumbnail Widget
Plugin URI: http://iworks.pl/wordpress-plugins-post-thumbnail-widget/
Description: Allow to publish post thumbnails on sidebar.
Version: 1.2
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40iworks%2epl&item_name=Post%20Thumbnail%20Widget&no_shipping=0&no_note=1&tax=0&bn=PP%2dDonationsBF&charset=UTF%2d8
 *
 * Post Thumbnail Widget.
 *
 * PHP version 5
 *
 * @category WordpressPlugin
 * @package  PostThumbnailWidget
 * @author   Marcin Pietrzak <marcin@iworks.pl>
 * @license  http://www.gnu.org/licenses/gpl.html GPL
 * @version  SVN: $Id: post-thumbnail-widget.php 291121 2010-09-18 21:08:57Z iworks $
 * @link     http://iworks.pl/wordpress-plugins-post-thumbnail-widget/
 */

/**
 * PostThumbnailWidget
 *
 * @category WordpressPlugin
 * @package  PostThumbnailWidget
 * @author   Marcin Pietrzak <marcin@iworks.pl>
 * @license  http://www.gnu.org/licenses/gpl.html GPL
 * @release  <package_version>
 * @link     <package_link>
 */
abstract class PostThumbnailWidget
{
    public static $id   = 'post-thumbnail-widget';
    public static $name = 'Post Thumbnail Widget';
    public static $fields_config = array('rss-display', 'rss-excerpt-display');
    public static $fields_widget = array('title', 'limit', 'orderby', 'order', 'show-post-title', 'link-post-title');

    /**
     * init()
     *
     * @return null
     *
     */
    static public function init()
    {
        load_plugin_textdomain(PostThumbnailWidget::$id, PLUGINDIR . '/' . self::$id, self::$id . '/languages', self::$id . '/languages');
        add_filter('the_content_rss', array('PostThumbnailWidget', 'rss'), 600);
        add_filter('the_excerpt_rss', array('PostThumbnailWidget', 'rssExcerpt'), 600);
        add_action('admin_menu', array('PostThumbnailWidget', 'addAdminOptionPage'));
        wp_register_sidebar_widget(self::$id, __(self::$name, self::$id), array('PostThumbnailWidget', 'sidebar'), array('description'=>__('Allow to publish post thumbnails on sidebar and on RSS.'), 'class'=>self::$id));
        wp_register_widget_control(self::$id, __(self::$name, self::$id), array('PostThumbnailWidget', 'options'));
        register_uninstall_hook(__FILE__, array(self::$name, 'uninstall'));
        return;
    } /*  init() */

    /**
     * _getOptions()
     *
     * @return array
     *
     */
    static private function _getOptions()
    {
        if (function_exists('get_site_option')) {
            $options = get_site_option(self::$id);
        } else {
            $options = get_option(self::$id);
        }
        /* default plugin options */
        if (!isset($options['plugin-rss-display'])) {
            $options['plugin-rss-display'] = 'on';
        }
        if (!isset($options['plugin-rss-excerpt-display'])) {
            $options['plugin-rss-excerpt-display'] = 'on';
        }
        /* default widget options */
        if (!isset($options['widget-limit'])) {
            $options['widget-limit'] = 1;
        }
        if (!isset($options['widget-orderby'])) {
            $options['widget-orderby'] = 'rand';
        }
        if (!isset($options['widget-order'])) {
            $options['widget-order'] = 'DESC';
        }
        return $options;
    } /*  _getOptions() */

    /**
     * addAdminOptionPage()
     *
     * @return null
     *
     */
    static public function addAdminOptionPage()
    {
        add_submenu_page('edit.php',
            __(self::$name, self::$id),
            __(self::$name, self::$id),
            'manage_options',
            basename(__FILE__),
            array('PostThumbnailWidget', 'displayOptions'));
        return;
    } /*  addAdminOptionPage() */

    /**
     * displayOptions()
     *
     * @return null
     *
     */
    static public function displayOptions()
    {
        if (isset($_POST['action']) &&($_POST['action'] == 'update')) {
            self::_updateOptions();
            echo '<div class="updated"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
        }

        $options = self::_getOptions();
        /* Display admin page */
        ?><div class="wrap">
        <h2><?php _e(self::$name . ' Options', self::$id); ?></h2>
        <form method="post" action="">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="<?php echo self::$id; ?>" />
        <?php
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field(self::$id . '_action_update');
        }?>
        <fieldset class="options">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top"><?php _e('Display post thumbnail in feeds?', self::$id); ?></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="<?php echo self::$id; ?>[plugin-rss-display]" id="plugin-rss-display_on"<?php echo ($options['plugin-rss-display'] == 'on')? ' checked="checked"':''; ?> value="on"  /> <label for="plugin-rss-display_on"><?php _e('Yes'); ?></label></li>
                                <li><input type="radio" name="<?php echo self::$id; ?>[plugin-rss-display]" id="plugin-rss-display_off"<?php echo ($options['plugin-rss-display'] == 'off')? ' checked="checked"':''; ?> value="off" /> <label for="plugin-rss-display_off"><?php _e('No'); ?></label></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><?php _e('Display post thumbnail in feeds with excerpt?', self::$id); ?></th>
                        <td>
                            <ul>
                                <li><input type="radio" name="<?php echo self::$id; ?>[plugin-rss-excerpt-display]" id="plugin-rss-excerpt-display_on"<?php echo ($options['plugin-rss-excerpt-display'] == 'on')?  ' checked="checked"':''; ?> value="on"  /> <label for="plugin-rss-excerpt-display_on"><?php _e('Yes'); ?></label></li>
                                <li><input type="radio" name="<?php echo self::$id; ?>[plugin-rss-excerpt-display]" id="plugin-rss-excerpt-display_off"<?php echo ($options['plugin-rss-excerpt-display'] == 'off')? ' checked="checked"':''; ?> value="off" /> <label for="plugin-rss-excerpt-display_off"><?php _e('No'); ?></label></li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
        <p class="submit"><input type="submit" value="<?php _e('Save Changes'); ?>" /></p>
        </form>
        <h2><?php _e('Donation', self::$id); ?></h2>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_donations">
        <input type="hidden" name="business" value="paypal@iworks.pl">
        <input type="hidden" name="item_name" value="<?php echo self::$name; ?>">
        <input type="hidden" name="no_shipping" value="0">
        <input type="hidden" name="no_note" value="1">
        <input type="hidden" name="tax" value="0">
        <input type="hidden" name="bn" value="PP-DonationsBF">
        <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Donate">
        <img alt="" border="0" src="https://www.paypal.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
            </form>
        </div><?php
        return;
    } /*  displayOptions() */

    /**
     * _updateOptions()
     *
     * @return null
     *
     */
    static private function _updateOptions()
    {
        $options = self::_getOptions(self::$id);
        $new_options = array();
        if (isset($_POST[ 'page_options'])) {
            check_admin_referer(self::$id . '_action_update');
            foreach (self::$fields_config as $key) {
                $field_name = 'plugin-' . $key;
                if (isset($_POST[ self::$id ][ $field_name ])) {
                    $new_options[ $field_name ] = $_POST[ self::$id ][ $field_name ];
                }
            }
            foreach (self::$fields_widget as $key) {
                $field_name = 'widget-' . $key;
                if (isset($options[ $field_name ])) {
                    $new_options[ $field_name ] = $options[ $field_name ];
                }
            }
        } else {
            foreach (self::$fields_config as $key) {
                $field_name = 'plugin-' . $key;
                if (isset($options[ $field_name ])) {
                    $new_options[ $field_name ] = $options[ $field_name ];
                }
            }
            foreach (self::$fields_widget as $key) {
                $field_name = 'widget-' . $key;
                if (isset($_POST[ self::$id ][ $field_name ])) {
                    $new_options[ $field_name ] = $_POST[ self::$id ][ $field_name ];
                }
            }
        }
        if (function_exists('update_site_option') &&(function_exists('is_site_admin') && is_site_admin())) {
            update_site_option(self::$id, $new_options);
        } else {
            update_option(self::$id, $new_options);
        }
        return;
    } /*  _updateOptions() */

    /**
     * show()
     *
     * @return null
     *
     */
    static public function show()
    {
        $options = get_option(self::$id);
        /* build query & query_posts */
        $query = 'meta_key=_thumbnail_id&orderby=';
        $query .= $options['widget-orderby'];
        if ($options['widget-orderby'] != 'rand') {
            $query .= '&order=' . $options['widget-order'];
        }
        query_posts($query);
        /* get limit */
        $limit = 1;
        if (isset($options['widget-limit']) and preg_match('/^\d+$/', $options['widget-limit'])) {
            $limit = $options['widget-limit'];
        }
        global $more;
        $more = 0;
        print '<ol>';
        while (have_posts() and $limit) {
            the_post();
            $post_title = trim(strip_tags(the_title('', '', false)));
            $post_link  = get_permalink();
            $img = get_the_post_thumbnail(get_the_ID(), 'post-thumbnail', array('title'=>$post_title));
            echo '<li>';
            printf('<a href="%s">%s</a>', $post_link, $img);
            if ( self::_isOptionTurnOn('widget-show-post-title') ) {
                if ( self::_isOptionTurnOn('widget-link-post-title') ) {
                    printf('<p><a href="%s">%s</a></p>', $post_link, $post_title);
                } else {
                    printf('<p>%s</p>', $post_title);
                }
            }
            echo '</li>';
            $limit--;
        }
        print '</ol>';
        wp_reset_query();
        return;
    } /*  show() */

    /**
     * sidebar()
     *
     * @param string $args function arguments
     *
     * @return null
     *
     */
    public function sidebar($args)
    {
        extract($args);
        $options = get_option(self::$id);
        echo $before_widget;
        if (isset($options['widget-title']) && $options['widget-title']) {
            print($before_title . $options['widget-title'] . $after_title);
        }
        self::show();
        echo $after_widget;
    } /* sidebar() */

    /**
     * _getOrderbyOptionArray
     *
     * @return array
     */
    static private function _getOrderbyOptionArray()
    {
        return array('rand'=>__('Random'),
            'date'=>__('Date/Time'),
            'title'=>__('Title'),
            'modified'=>__('Last Modified'),
            'comment_count'=>__('Number of Comments', self::$id)
            );
    } /* _getOrderbyOptionArray() */

    /**
     * _getOrderbySelect()
     *
     * @return string
     *
     */
    static private function _getOrderbySelect()
    {
        $options = self::_getOptions();
        $data = self::_getOrderbyOptionArray();
        $select = sprintf('<select name="%s[widget-orderby]">', self::$id);
        foreach ($data as $key => $value) {
            $select .= sprintf('<option value="%s"%s>%s</option>',
                $key,
                ($options['widget-orderby'] == $key)? ' selected="selected"':'',
                $value);
        }
        $select .= '</select>';
        return $select;
    } /* _getOrderbySelect() */

    /**
     * options()
     *
     * @return null
     *
     */
    static public function options()
    {
        if ($_POST[self::$id]) {
            self::_updateOptions();
        }
        $options = self::_getOptions(self::$id);
        /* begin options */
        echo '<div class="widget-content">';
        /* title */
        echo '<p><label for="' . self::$id . '-widget-title">' . __('Title:') . '</label>';
        echo '<input type="text" value="' . $options['widget-title'] . '" name="' . self::$id . '[widget-title]" id="' . self::$id . '-widget-title"
            class="widefat"></p>';
        /* number */
        echo '<p><label for="' . self::$id . '-widget-limit">' . __('How many items would you like to display?') . '</label> ';
        echo '<input type="text" value="' . $options['widget-limit'] . '" name="' . self::$id . '[widget-limit]" id="' . self::$id . '-widget-limit" size="3"></p>';
        /* orderby */
        echo '<p><label for="' . self::$id . '-widget-orderby">' . __('Order images by:') . '</label> ';
        echo self::_getOrderbySelect();
        echo '</p>';
        /* show post title */
        echo '<p>';
        echo '<input class="checkbox" type="checkbox" ';
        if ( self::_isOptionTurnOn('widget-show-post-title')) {
            echo 'checked="checked" ';
        }
        echo 'name="' . self::$id . '[widget-show-post-title]" id="' . self::$id . '-widget-show-post-title" /> ';
        echo '<label for="' . self::$id . '-widget-show-post-title">' . __('Show post title') . '</label>';
        echo '</p>';
        /* link post title */
        echo '<p>';
        echo '<input class="checkbox" type="checkbox" ';
        if ( self::_isOptionTurnOn('widget-link-post-title')) {
            echo 'checked="checked" ';
        }
        echo 'name="' . self::$id . '[widget-link-post-title]" id="' . self::$id . '-widget-link-post-title" /> ';
        echo '<label for="' . self::$id . '-widget-link-post-title">' . __('Link post title') . '</label>';
        echo '</p>';
        /* end options */
        echo '</div>';
        return;
    } /*  options() */

    /**
     * _getIcon()
     *
     * @param string $mode mode for image
     *
     * @return string
     *
     */
    static private function _getIcon($mode = 'full')
    {
        global $post;
        $string =($mode == 'full')? '<p>%s</p>':'%s';
        return sprintf($string, get_the_post_thumbnail($post->ID));
    }

    /**
     * _isOptionTurnOn()
     *
     * @param string $name option name
     *
     * @return bool
     *
     */
    static private function _isOptionTurnOn($name)
    {
        $options = self::_getOptions();
        return (isset($options[ $name ]) && $options[ $name ] == 'on');
    }

    /**
     * rss()
     *
     * @param string $content rss content
     *
     * @return string
     *
     */
    static public function rss($content)
    {
        global $wpdb, $post;
        return (self::_isOptionTurnOn('plugin-rss-display'))?
            self::_getIcon() . $content : $content;
    }

    /**
     * rssExcerpt()
     *
     * @param string $content rss excerpt content
     *
     * @return string
     *
     */
    static public function rssExcerpt($content)
    {
        global $wpdb, $post;
        return (self::_isOptionTurnOn('plugin-rss-excerpt-display'))?
            self::_getIcon('excerpt') . $content : $content;
    }

    public function uninstall()
    {
        delete_option(self::$id);
    }
}
add_action('plugins_loaded', array('PostThumbnailWidget', 'init'));
?>
