<?php
/*
    Plugin Name: WP DS Geography
    Plugin URI: http://wpds.ru/
    Description: Каталог географических точек и местоположений.
    Author: DiamondSteel
    Author URI: http://diamondsteel.ru
    Version: 1.0.0

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'WPDS_GEOGRAPHY_PLUGIN_URL' ) ) { define ( "WPDS_GEOGRAPHY_PLUGIN_URL", plugin_dir_url( __FILE__ ) ); }
if ( ! defined( 'WPDS_GEOGRAPHY_PLUGIN_DIR' ) ) { define ( "WPDS_GEOGRAPHY_PLUGIN_DIR", untrailingslashit( plugin_dir_path( __FILE__ ) ) ); } // Без слеша в конце

global $wpdb;
if ( ! defined( 'WPDS_GEOGRAPHY_TABLE_MARKERS' ) ) { define ( "WPDS_GEOGRAPHY_TABLE_MARKERS", $wpdb->prefix.'wpds_geography_markers' ); }
if ( ! defined( 'WPDS_GEOGRAPHY_TABLE_LAYERS'  ) ) { define ( "WPDS_GEOGRAPHY_TABLE_LAYERS",  $wpdb->prefix.'wpds_geography_layers'  ); }
if ( ! defined( 'WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS' ) ) { define ( "WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS", $wpdb->prefix.'wpds_geography_layers_relationships' ); }

/**
 *
 * @author DiamondSteel
 *
 */
class WPDS_Geography_Plugin
{
    protected $permissions_settings;
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        
        add_action( 'init', array( &$this, 'enable_getext' ) );
        
        add_action( 'admin_menu', array( &$this, 'wpds_geography_backend' ) );
        
        /* Добавляем на страницу создания и изменения поста/страницы скрипт добавляющий в редактор конпку добавления карты */
        add_action( 'admin_print_styles-post.php',     array( &$this, 'catch_load_admin_page_post' ) );
        add_action( 'admin_print_styles-post-new.php', array( &$this, 'catch_load_admin_page_post' ) );
        
        /* Ajax функция. Вызывается при нажатии кнопки добавить карту в wp-редакторе */
        add_action( 'wp_ajax_ajax_get_geography_list', array( &$this, 'ajax_get_geography_list' ) );
        
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'add_links_in_plugin_action' ) );
        
        /* Фильтр срабатывает при сохранении "Настроек экрана" для страниц */
        add_filter( 'set-screen-option', array( &$this, 'set_screen_option_for_page' ), 10, 3 );
        
        /* Регестрирую шорткод [wpds_geography] */
        add_shortcode( 'wpds_geography', array( &$this, 'wpds_geography_shortcode' ) );
        
        register_activation_hook( __FILE__, array( &$this, 'install' ) );
        register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    
    /**
     * Добавление кнопки "Добавить карту" к редактору, рядом с кнопкой добавления медиафайлов.
     */
    public function add_shortcode_button_for_editor()
    {
        echo "<script type=\"text/javascript\">";
        echo "
jQuery( document ).ready( function( $ ) {
    $( '#wpds_geography_map_button' ).remove();
    $( '#wp-content-media-buttons' ).append( '<a style=\'margin-left:5px;\' class=\'button\' title=\'" . __( 'Add map', 'wp-ds-geography' ) . "\' id=\'wpds_geography_map_button\' href=\'#\'><div style=\'float:left;\'><img src=\'" . WPDS_GEOGRAPHY_PLUGIN_URL . "images/icon-admin-menu_b.png\' style=\'padding:0 5px 3px 0;\'></div><div style=\'float:right;padding-top:0px;\'>" . __( 'Add map', 'wp-ds-geography' ) . "</div></a>');
    var info = $( '<div id=\"wpds-geography-modal-list\" style=\'overflow: hidden;\' />' );
    info.html( '<iframe width=\'610\' height=\'410\' scrolling=\'no\' src=\'" . admin_url( 'admin-ajax.php?action=ajax_get_geography_list&wp_nonce=' . wp_create_nonce('{845D3C3A-35DD-4F25-A2D3-F723430ECDFD}') ) . "\' />' );
    info.wpdialog( { title : '" . __( 'Add map', 'wp-ds-geography' ) . "',
                     dialogClass: 'wp-dialog',
                     width : 640,
                     height : 480,
                     modal : true,
                     autoOpen : false,
                     closeOnEscape : true
                   } );
    $( document ).on( 'click', '#wpds_geography_map_button', function( event ) {
        info.wpdialog( 'open' );
        return false;
    });
});
";
        echo "</script>" . PHP_EOL;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    
    /**
     * Отлавдиваем страницу добавления/редактирования страницы/поста
     * Нужно для того, чтобы код кнопки для добавления карты выводился только на этих страницах админки
     */
    public function catch_load_admin_page_post()
    {
        if ( current_user_can( $this->permissions_settings['capabilities_own'] ) ) {
            wp_enqueue_script( array( 'jquery', 'wpdialogs' ) );
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            add_action( 'admin_print_footer_scripts', array( &$this, 'add_shortcode_button_for_editor' ) );
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    
    /**
     * При нажатии на кнопку "Добавить карту" (около редактора страницы/поста) выводим модальное окно со списком имеющихся карт
     */
    public function ajax_get_geography_list()
    {
        if ( ! wp_verify_nonce( $_GET['wp_nonce'], '{845D3C3A-35DD-4F25-A2D3-F723430ECDFD}' ) and ! wp_verify_nonce( $_POST['wp_nonce'], '{845D3C3A-35DD-4F25-A2D3-F723430ECDFD}' ) ){ wp_nonce_ays(); }
        
        global $wpdb;
        
        $l_search = isset( $_POST['s'] ) ? "AND l.name LIKE '%" . mysql_real_escape_string( $_POST['s'] ) . "%'" : '';
        $m_search = isset( $_POST['s'] ) ? "AND m.name LIKE '%" . mysql_real_escape_string( $_POST['s'] ) . "%'" : '';
        
        $sql = "(SELECT l.id, l.name, l.createdon, 'layer'  AS 'type' FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS  . " AS l WHERE l.id != '0' {$l_search})
                 UNION
                (SELECT m.id, m.name, m.createdon, 'marker' AS 'type' FROM " . WPDS_GEOGRAPHY_TABLE_MARKERS . " AS m WHERE m.id != '0' {$m_search})
                 ORDER BY createdon DESC";
        
        $list = $wpdb->get_results( $sql, ARRAY_A );
        
        if( isset( $_POST['s'] ) ){
            $this->build_item_list( $list );
            exit();
        }
        
        echo "<!DOCTYPE html>" . PHP_EOL;
        echo "<html><head>" . PHP_EOL;
        echo "<title>" . __( 'Add map', 'wp-ds-geography' ) . "</title>" . PHP_EOL;
        echo "<script type=\"text/javascript\" src=\"" . site_url() . "/wp-includes/js/jquery/jquery.js\"></script>" . PHP_EOL;
        echo "<script type=\"text/javascript\" src=\"" . WPDS_GEOGRAPHY_PLUGIN_URL . "js/jquery_caret.js\"></script>" . PHP_EOL;
        echo "<link rel=\"stylesheet\" href=\"" . WPDS_GEOGRAPHY_PLUGIN_URL . "css/wpds_geography_list.css\" type=\"text/css\" media=\"all\" />" . PHP_EOL;
        echo "</head><body>" . PHP_EOL;
        echo "<div class=\"wpds_list_search\">" . __( 'Search:', 'wp-ds-geography' ) . " <input type=\"text\" name=\"s\" id=\"wpds_list_search_s\" /></div>";
        echo "<div id=\"wpds_list_container\" class=\"wpds_list_container\">";

        $this->build_item_list( $list );

        echo "</div>";
        echo "<div style=\"margin-top: 10px;\">";
        echo "<div style=\"float:left;\">";
        echo "<input type=\"text\" id=\"wpds_list_result\" name=\"wpds_list_result\" value=\"\" />";
        echo "</div>";
        echo "<div style=\"float:right;\">";
        echo "<a class=\"wpds_list_button hidden\" title=\"" . __( 'Insert map', 'wp-ds-geography' ) . "\" id=\"wpds_list_button_insert\" href=\"#\">" . __( 'Insert map', 'wp-ds-geography' ) . "</a>";
        echo " &nbsp; ";
        echo "<a class=\"wpds_list_button\" title=\"" . __( 'Cancel', 'wp-ds-geography' ) . "\" id=\"wpds_list_button_cancel\" href=\"#\">" . __( 'Cancel', 'wp-ds-geography' ) . "</a>";
        echo "</div>";
        echo "</div>";
        echo "<script type=\"text/javascript\">";
        echo "
jQuery( document ).ready( function( $ ) {
    $( document ).on( 'click touchstart', '.wpds_list_item', function( e ) {
        e.preventDefault();
        var id   = $( this ).find( 'input[name=\"wpds_item_id\"]' ).val();
        var type = $( this ).find( 'input[name=\"wpds_item_type\"]' ).val();
        $( '.wpds_list_item.active' ).removeClass( 'active' );
        $( '#wpds_list_button_insert.hidden' ).removeClass( 'hidden' );
        $( this ).addClass( 'active' );
        $( '#wpds_list_result' ).val( '[wpds_geography ' + type + '=\"' + id + '\"]' );
    } )

    $( '#wpds_list_search_s' ).on( 'keyup', function() {
        var nonce = \"" . wp_create_nonce( '{845D3C3A-35DD-4F25-A2D3-F723430ECDFD}' ) . "\";
        var search = $( this ).val();
        var data = {
                action: 'ajax_get_geography_list',
                wp_nonce: nonce,
                s: search
        };
        $.post( \"" . admin_url() . "admin-ajax.php\", data, function( response ) {
            $( '.wpds_list_item' ).remove();
            $( '#wpds_list_container' ).append( response );
        } );
    } )
    
    $( document ).on( 'click', '#wpds_list_button_cancel', function( event ) {
        $( '.wpds_list_item.active' ).removeClass( 'active' );
        $( '#wpds_list_button_insert' ).addClass( 'hidden' );
        window.parent.jQuery('#wpds-geography-modal-list').wpdialog('close');
    });

    $( document ).on( 'click', '#wpds_list_button_insert', function( event ) {
        $( '#content', parent.document.body ).insertAtCaret( $( '#wpds_list_result' ).val() );
        $( '.wpds_list_item.active' ).removeClass( 'active' );
        $( '#wpds_list_button_insert' ).addClass( 'hidden' );
        window.parent.jQuery( '#wpds-geography-modal-list' ).wpdialog( 'close' );
    });
    
} );
";
        echo "</script>" . PHP_EOL;
        echo "</body></html>";
        exit;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    
    /**
     * Формирование списка карт
     * Вспомогательная функция для ajax_get_geography_list()
     * @param $array - результат SQL запроса получающего список карт
     */
    public function build_item_list( $array )
    {
        foreach ( $array as $td ) {
            echo "<div class=\"wpds_list_item\">";
            echo "<table style=\"width: 100%;\"><tr>";
            echo "<td  class=\"wpds_list_item_tdtype\">";
                echo "<img src=\"" . WPDS_GEOGRAPHY_PLUGIN_URL . "images/list-" . $td['type'] . ".png\" width=\"20\" height=\"20\" border=\"0\" />";
            echo "</td>";
            echo "<td class=\"wpds_list_item_tdname\">";
                echo htmlspecialchars( stripslashes( $td['name'] ), ENT_QUOTES );
            echo "</td>";
            echo "<td class=\"wpds_list_item_tddate\">";
                echo $td['createdon'];
            echo "</td>";
            echo "</tr></table>";
            echo "<input type=\"hidden\" name=\"wpds_item_id\" value=\"" . $td['id'] . "\" />";
            echo "<input type=\"hidden\" name=\"wpds_item_type\" value=\"" . $td['type'] . "\" />";
            echo "</div>";
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    
    /**
     * Обработка шорткода [wpds_geography]
     * @param unknown $atts
     * @return void|string
     */
    public function wpds_geography_shortcode( $atts )
    {
        extract( shortcode_atts( array( 'layer' => 'false', 'marker' => 'false', ), $atts, 'wpds_geography' ) );
        
        if ( $marker == 'false' and $layer == 'false' ) { return; }
        
        if ( is_numeric( $marker ) and $layer == 'false' ) { $type = 'marker'; $id = $marker; }
        elseif ( is_numeric( $layer ) and $marker == 'false' ) { $type = 'layer'; $id = $layer; }
        else { return; }
        
        require_once 'WPDSGeographyFrontend.class.php';
        $WPDSGeographyFrontend = new WPDSGeographyFrontend( $type, $id );
        
        // Получаем код карты (html + JS)
        $map = $WPDSGeographyFrontend->get_map();
        
        if ( empty( $map ) ) { $map = __( 'The ID does not exist', 'wp-ds-geography' ); }
        
        return $map;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для сохранения "Настроек экрана"
     *
     * @param $status -
     * @param $option - Свойство экрана которое хотим сохранить
     * @param $value - Значение которое хотим сохранить для свойства $option
     */
    public function set_screen_option_for_page($status, $option, $value)
    {
        if ( 'markers_per_page' == $option ) return $value;
        if ( 'layers_per_page'  == $option ) return $value;
        return $status;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Добавляем свои ссылки для плагина на странице плагинов
     * 
     * @param $links - имеющиеся ссылки для плагина
     * @return объединение ссылок $links с тем, что хотим добавить
     */
    public function add_links_in_plugin_action( $links )
    {
        return array_merge( $links, array( 'settings' => '<a href="' . admin_url( '/options-general.php?page=wpds-geography-options' ) . '">' . __( 'Settings' ) . '</a>' ) );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Инициализация административной части плагина
     */
    public function wpds_geography_backend()
    {
        require_once 'WPDSGeographyBackend.class.php';
        $WPDSGeographyBackend = new WPDSGeographyBackend();
        
        $WPDSGeographyBackend->add_object_admin_menu();
        
        require_once 'WPDSOptions.class.php';
        $WPDSOptions = new WPDSOptions();
        
        $WPDSOptions->add_options_page();
        
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Добавление в заголовки всех необходимых для плагина скриптов и стилей
     * 
     * @param string $type - при значении "layer" добавит js и css необходимые для карт-слоёв
     */
    public function wpds_load_js_and_css( $type = 'all' ){
        
        $settings_maps       = get_option( 'wpds_geography_options_maps' );
        $settings_map_source = get_option( 'wpds_geography_options_map_source' );
        
        wp_enqueue_script( array( 'jquery' ) );
        
        wp_enqueue_style( 'leaflet', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/leaflet.css' );
        wp_enqueue_script( 'leaflet', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/leaflet.js', '', '0.7.2'  );
        
        if ( $settings_maps['fullscreen_mode'] == 'true' ) {
            wp_enqueue_style( 'fullscreen_mode', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/Control.FullScreen.css' );
            wp_enqueue_script( 'fullscreen_mode', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/Control.FullScreen.js' );
        }
        
        if ( $settings_maps['scrollWheelZoomControl'] == 'true' ) {
            wp_enqueue_style( 'zoom_mode', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/Control.ZoomMode.css' );
            wp_enqueue_script( 'zoom_mode', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/Control.ZoomMode.js' );
        }
        
        if ( $type == 'layer' ) {
            if ( $settings_maps['clustering_mode'] == 'true' ) {
                wp_enqueue_style( 'clustering_mode_a', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/leaflet.markercluster.css' );
                wp_enqueue_script( 'clustering_mode', WPDS_GEOGRAPHY_PLUGIN_URL . 'leaflet/leaflet.markercluster.js' );
            }
        }
        
        if ( $settings_map_source['yandexUse'] == 'true' ) {
            wp_enqueue_script( 'leaflet-yandex-api', 'http://api-maps.yandex.ru/2.0/?load=package.map&lang=ru-RU', '', '2.0' );
            wp_enqueue_script( 'leaflet-yandex-plugin', WPDS_GEOGRAPHY_PLUGIN_URL . $settings_map_source['yandexLeafletPlugin'] );
        }
        if ( $settings_map_source['googleUse'] == 'true' ) {
            if ( $settings_map_source['googleAPIKey'] != '' ) { $google_maps_api_key = '&amp;key=' . $settings_map_source['googleAPIKey']; } else { $google_maps_api_key = ''; }
            wp_enqueue_script( 'leaflet-google-api', 'http://maps.google.com/maps/api/js?libraries=places&amp;sensor=true' . $google_maps_api_key, '', '3.0' );
            wp_enqueue_script( 'leaflet-google-plugin', WPDS_GEOGRAPHY_PLUGIN_URL . $settings_map_source['googleLeafletPlugin'] );
        }
        if ( $settings_map_source['dgisUse'] == 'true' ) {
            wp_enqueue_script( 'leaflet-dgis-api', 'http://maps.api.2gis.ru/1.0', '', '1.0' );
            wp_enqueue_script( 'leaflet-dgis-plugin', WPDS_GEOGRAPHY_PLUGIN_URL . $settings_map_source['dgisLeafletPlugin'] );
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Указываем текстовую область и папку для локализации плагина
     */
    public function enable_getext()
    {
        load_plugin_textdomain( 'wp-ds-geography', '', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Действия при диактивации плагина
     */
    public function deactivate()
    {
        return true;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Действия при установки плагина
     */
    public function install()
    {
        global $wpdb;
        $wpdb->hide_errors();
        
        require_once( ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php' );

        $sql_markers_table = "CREATE TABLE " . WPDS_GEOGRAPHY_TABLE_MARKERS . " (
                             `id`            INT unsigned NOT NULL AUTO_INCREMENT,
                             `name`          VARCHAR(255) NOT NULL,
                             `layerscontrol` VARCHAR(32)  NOT NULL,
                             `layer`         INT          NOT NULL,
                             `coordinates`   POINT        NOT NULL,
                             `icon`          VARCHAR(255) NOT NULL,
                             `popuptext`     TEXT         NOT NULL,
                             `zoom`          INT(2)       NOT NULL,
                             `openpopup`     TINYINT(1)   NOT NULL,
                             `mapwidth`      INT(4)       NOT NULL,
                             `mapwidthunit`  VARCHAR(2)   NOT NULL,
                             `mapheight`     INT(4)       NOT NULL,
                             `createdby`     INT          NOT NULL,
                             `createdon`     DATETIME     NOT NULL,
                             `updatedby`     INT          NOT NULL,
                             `updatedon`     DATETIME     NOT NULL,
                             PRIMARY KEY ( `id` )
                             ) ENGINE = MyISAM DEFAULT CHARSET = utf8;";
        dbDelta( $sql_markers_table );

        $sql_layers_table = "CREATE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS . " (
                            `id`             INT unsigned NOT NULL AUTO_INCREMENT,
                            `name`           VARCHAR(255) NOT NULL,
                            `layerscontrol`  VARCHAR(25)  NOT NULL,
                            `zoom`           INT(2)       NOT NULL,
                            `mapwidth`       INT(4)       NOT NULL,
                            `mapwidthunit`   VARCHAR(2)   NOT NULL,
                            `mapheight`      INT(4)       NOT NULL,
                            `fitbounds`      TINYINT(1)   NOT NULL,
                            `coordinates`    POINT        NOT NULL,
                            `createdby`      INT          NOT NULL,
                            `createdon`      DATETIME     NOT NULL,
                            `updatedby`      INT          NOT NULL,
                            `updatedon`      DATETIME     NOT NULL,
                            `listmarkers`    TINYINT(1)   NOT NULL,
                            `clustering`     TINYINT(1)   NOT NULL,
                            PRIMARY KEY ( `id` )
                            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;";
        dbDelta( $sql_layers_table );
        
        $sql_layers_relationships = "CREATE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " (
                                    `id`               INT unsigned NOT NULL AUTO_INCREMENT,
                                    `layer_id`         INT          NOT NULL,
                                    `related_layer_id` INT          NOT NULL,
                                    PRIMARY KEY ( `id` )
                                    ) ENGINE = MYISAM DEFAULT CHARSET = utf8;";
        dbDelta( $sql_layers_relationships );
        
        $this->set_default_settings( 'install' );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Настройки плагина по умолчанию
     * 
     * @param string $mode (install | update)
     */
    public function set_default_settings( $mode = 'update' )
    {
        $wpds_geography_options_permissions['capabilities_own']     = 'edit_posts';
        $wpds_geography_options_permissions['capabilities_not_own'] = 'moderate_comments';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_permissions' ) ) { add_option( 'wpds_geography_options_permissions', $wpds_geography_options_permissions ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_permissions', $wpds_geography_options_permissions ); }

        $wpds_geography_options_maps['dragging']                              = 'true';
        $wpds_geography_options_maps['worldCopyJump']                         = 'true';
        $wpds_geography_options_maps['zoomControl']                           = 'true';
        $wpds_geography_options_maps['touchZoom']                             = 'true';
        $wpds_geography_options_maps['scrollWheelZoom']                       = 'false';
        $wpds_geography_options_maps['scrollWheelZoomControl']                = 'true';
        $wpds_geography_options_maps['doubleClickZoom']                       = 'true';
        $wpds_geography_options_maps['boxzoom']                               = 'true';
        $wpds_geography_options_maps['trackResize']                           = 'true';
        $wpds_geography_options_maps['closePopupOnClick']                     = 'false';
        $wpds_geography_options_maps['keyboard']                              = 'true';
        $wpds_geography_options_maps['keyboardPanOffset']                     = '80';
        $wpds_geography_options_maps['keyboardZoomOffset']                    = '1';
        $wpds_geography_options_maps['inertia']                               = 'true';
        $wpds_geography_options_maps['inertiaDeceleration']                   = '3000';
        $wpds_geography_options_maps['inertiaMaxSpeed']                       = '1500';
        $wpds_geography_options_maps['scaleControl']                          = 'true';
        $wpds_geography_options_maps['scale_control_position']                = 'bottomleft';
        $wpds_geography_options_maps['scale_maxwidth']                        = '100';
        $wpds_geography_options_maps['metric']                                = 'true';
        $wpds_geography_options_maps['imperial']                              = 'true';
        $wpds_geography_options_maps['updateWhenIdle']                        = 'false';
        $wpds_geography_options_maps['detectRetina']                          = 'true';
        $wpds_geography_options_maps['fullscreen_mode']                       = 'true';
        $wpds_geography_options_maps['clustering_mode']                       = 'false';
        $wpds_geography_options_maps['clustering_zoomToBoundsOnClick']        = 'true';
        $wpds_geography_options_maps['clustering_showCoverageOnHover']        = 'false';
        $wpds_geography_options_maps['clustering_spiderfyOnMaxZoom']          = 'true';
        $wpds_geography_options_maps['clustering_spiderfyDistanceMultiplier'] = '1';
        $wpds_geography_options_maps['clustering_singleMarkerMode']           = 'false';
        $wpds_geography_options_maps['clustering_disableClusteringAtZoom']    = '19';
        $wpds_geography_options_maps['clustering_maxClusterRadius']           = '80';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_maps' ) ) { add_option( 'wpds_geography_options_maps', $wpds_geography_options_maps ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_maps', $wpds_geography_options_maps ); }
        
        $wpds_geography_options_map_source['osmUse']              = 'true';
        $wpds_geography_options_map_source['osmName']             = 'OpenStreetMap';
        $wpds_geography_options_map_source['yandexUse']           = 'true';
        $wpds_geography_options_map_source['yandexName']          = 'Yandex Maps';
        $wpds_geography_options_map_source['yandexAPI']           = 'http://api-maps.yandex.ru/2.0/?load=package.map&lang=ru-RU';
        $wpds_geography_options_map_source['yandexLeafletPlugin'] = 'leaflet/Yandex.js';
        $wpds_geography_options_map_source['googleUse']           = 'true';
        $wpds_geography_options_map_source['googleNameRoadmap']   = 'Google Maps (Roadmap)';
        $wpds_geography_options_map_source['googleNameSatellite'] = 'Google Maps (Satellite)';
        $wpds_geography_options_map_source['googleNameHybrid']    = 'Google Maps (Hybrid)';
        $wpds_geography_options_map_source['googleAPI']           = 'http://maps.google.com/maps/api/js?libraries=places&amp;sensor=true';
        $wpds_geography_options_map_source['googleAPIKey']        = '';
        $wpds_geography_options_map_source['googleLeafletPlugin'] = 'leaflet/Google.js';
        $wpds_geography_options_map_source['dgisUse']             = 'true';
        $wpds_geography_options_map_source['dgisName']            = '2GIS';
        $wpds_geography_options_map_source['dgisAPI']             = 'http://maps.api.2gis.ru/1.0';
        $wpds_geography_options_map_source['dgisLeafletPlugin']   = 'leaflet/dgis.js';
        
        $wpds_geography_options_map_source['layerscontrol_list']  = array( 'osm' => array( 'name' => $wpds_geography_options_map_source['osmName'],
                                                                                            'use' => $wpds_geography_options_map_source['osmUse']
                                                                                          ),
                                                                        'yandex' => array( 'name' => $wpds_geography_options_map_source['yandexName'],
                                                                                            'use' => $wpds_geography_options_map_source['yandexUse']
                                                                                          ),
                                                           'googleLayer_roadmap' => array( 'name' => $wpds_geography_options_map_source['googleNameRoadmap'],
                                                                                            'use' => $wpds_geography_options_map_source['googleUse']
                                                                                          ),
                                                         'googleLayer_satellite' => array( 'name' => $wpds_geography_options_map_source['googleNameSatellite'],
                                                                                            'use' => $wpds_geography_options_map_source['googleUse']
                                                                                          ),
                                                            'googleLayer_hybrid' => array( 'name' => $wpds_geography_options_map_source['googleNameHybrid'],
                                                                                            'use' => $wpds_geography_options_map_source['googleUse']
                                                                                          ),
                                                                          'dgis' => array( 'name' => $wpds_geography_options_map_source['dgisName'],
                                                                                            'use' => $wpds_geography_options_map_source['dgisUse']
                                                                                          )
                                                                          );
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_map_source' ) ) { add_option( 'wpds_geography_options_map_source', $wpds_geography_options_map_source ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_map_source', $wpds_geography_options_map_source ); }

        $wpds_geography_options_popup['maxWidth']         = '350';
        $wpds_geography_options_popup['minWidth']         = '250';
        $wpds_geography_options_popup['maxHeight']        = '200';
        $wpds_geography_options_popup['autoPan']          = 'true';
        $wpds_geography_options_popup['autoPanPadding_x'] = '5';
        $wpds_geography_options_popup['autoPanPadding_y'] = '5';
        $wpds_geography_options_popup['closeButton']      = 'true';
        $wpds_geography_options_popup['offset_x']         = '0';
        $wpds_geography_options_popup['offset_y']         = '-30';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_popup' ) ) { add_option( 'wpds_geography_options_popup', $wpds_geography_options_popup ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_popup', $wpds_geography_options_popup ); }

        $wpds_geography_options_markers['mapwidth']      = '100';
        $wpds_geography_options_markers['mapwidthunit']  = '%';
        $wpds_geography_options_markers['mapheight']     = '400';
        $wpds_geography_options_markers['zoom']          = '10';
        $wpds_geography_options_markers['lat']           = '55.008451';
        $wpds_geography_options_markers['lon']           = '82.935885';
        $wpds_geography_options_markers['layerscontrol'] = 'osm';
        $wpds_geography_options_markers['openpopup']     = 'false';
        $wpds_geography_options_markers['icon']          = '0-default.png';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_markers' ) ) { add_option( 'wpds_geography_options_markers', $wpds_geography_options_markers ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_markers', $wpds_geography_options_markers ); }

        $wpds_geography_options_layers['mapwidth']      = '100';
        $wpds_geography_options_layers['mapwidthunit']  = '%';
        $wpds_geography_options_layers['mapheight']     = '400';
        $wpds_geography_options_layers['zoom']          = '10';
        $wpds_geography_options_layers['lat']           = '55.008451';
        $wpds_geography_options_layers['lon']           = '82.935885';
        $wpds_geography_options_layers['fitbounds']     = 'false';
        $wpds_geography_options_layers['clustering']    = 'false';
        $wpds_geography_options_layers['listmarkers']   = 'false';
        $wpds_geography_options_layers['layerscontrol'] = 'osm';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_layers' ) ) { add_option( 'wpds_geography_options_layers', $wpds_geography_options_layers ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_layers', $wpds_geography_options_layers ); }

        $wpds_geography_options_markers_list['listmarkers_sort']       = 'name';
        $wpds_geography_options_markers_list['listmarkers_order']      = 'asc';
        $wpds_geography_options_markers_list['listmarkers_mflag']      = 'true';
        $wpds_geography_options_markers_list['listmarkers_mpopup']     = 'true';
        $wpds_geography_options_markers_list['listmarkers_mcreatedon'] = 'false';
        $wpds_geography_options_markers_list['listmarkers_mupdatedon'] = 'false';
        $wpds_geography_options_markers_list['listmarkers_mcreatedby'] = 'false';
        $wpds_geography_options_markers_list['listmarkers_limit']      = '50';
        if ( $mode == 'install' ) { if ( ! get_option( 'wpds_geography_options_markers_list' ) ) { add_option( 'wpds_geography_options_markers_list', $wpds_geography_options_markers_list ); } }
        if ( $mode == 'update'  ) { update_option( 'wpds_geography_options_markers_list', $wpds_geography_options_markers_list ); }
    }
}

global $WPDS_Geography_Plugin;
$WPDS_Geography_Plugin = new WPDS_Geography_Plugin();

?>