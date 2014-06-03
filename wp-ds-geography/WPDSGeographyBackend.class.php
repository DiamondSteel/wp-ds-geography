<?php

/**
 * Административная часть плагина
 * 
 * @author DiamondSteel
 * 
 */
class WPDSGeographyBackend
{
    protected $permissions_settings;

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Консруктор
     */
    public function __construct()
    {
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Создаём в Админ-панеле меню и подменю новых объектов.
     */
    public function add_object_admin_menu()
    {
        $page_admin_menu_1 = add_object_page( __( 'Geography', 'wp-ds-geography' ), __( 'Geography', 'wp-ds-geography' ), $this->permissions_settings['capabilities_own'], 'wpds_geography', array( &$this, 'list_all_markers' ), WPDS_GEOGRAPHY_PLUGIN_URL . 'images/icon-admin-menu.png' );
        $page_admin_menu_2 = add_submenu_page( 'wpds_geography', __( 'All markers',    'wp-ds-geography' ), __( 'All markers',    'wp-ds-geography' ), $this->permissions_settings['capabilities_own'], 'wpds_geography',            array( &$this, 'list_all_markers'    ) );
        $page_admin_menu_3 = add_submenu_page( 'wpds_geography', __( 'Add new marker', 'wp-ds-geography' ), __( 'Add new marker', 'wp-ds-geography' ), $this->permissions_settings['capabilities_own'], 'wpds_geography_the_marker', array( &$this, 'add_and_edit_marker' ) );
        $page_admin_menu_4 = add_submenu_page( 'wpds_geography', __( 'All layers',     'wp-ds-geography' ), __( 'All layers',     'wp-ds-geography' ), $this->permissions_settings['capabilities_own'], 'wpds_geography_all_layers', array( &$this, 'list_all_layers'     ) );
        $page_admin_menu_5 = add_submenu_page( 'wpds_geography', __( 'Add new layers', 'wp-ds-geography' ), __( 'Add new layer',  'wp-ds-geography' ), $this->permissions_settings['capabilities_own'], 'wpds_geography_the_layer',  array( &$this, 'add_and_edit_layer'  ) );
        
        /* Отлавдиваем загрузку страницы $page_admin_menu_1 и 2 */
        add_action( 'load-' . $page_admin_menu_1, array( &$this, 'catch_load_page_admin_menu_1' ) );
        
        /* Отлавдиваем загрузку страницы $page_admin_menu_3 */
        add_action( 'load-' . $page_admin_menu_3, array( &$this, 'catch_load_page_admin_menu_3' ) );
        
        /* Отлавдиваем загрузку страницы $page_admin_menu_4 */
        add_action( 'load-' . $page_admin_menu_4, array( &$this, 'catch_load_page_admin_menu_4' ) );
        
        /* Отлавдиваем загрузку страницы $page_admin_menu_5 */
        add_action( 'load-' . $page_admin_menu_5, array( &$this, 'catch_load_page_admin_menu_5' ) );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Отловили загрузку страницы $page_admin_menu_1 и включаем на ней "Настройки экрана"
     */
    public function catch_load_page_admin_menu_1()
    {
        $option = 'per_page';
        $args = array(
                'label' => __( 'Markers', 'wp-ds-geography' ),
                'default' => 20,
                'option' => 'markers_per_page'
        );
        add_screen_option( $option, $args );
        
        add_action( 'admin_head', array( &$this, 'admin_header_page_admin_menu_1' ) );
        
        require_once 'WPDSListTableForListMarkers.class.php';
        global $WPDSListTableForListMarkers;
        $WPDSListTableForListMarkers = new WPDSListTableForListMarkers();
        $WPDSListTableForListMarkers->prepare_items();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Стили для страницы $page_admin_menu_1
     */
    public function admin_header_page_admin_menu_1()
    {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-name { width: 30%; }';
        echo '.wp-list-table .column-icon { width: 52px; vertical-align: middle; }';
        echo '.wp-list-table .column-shortcode { width: 250px; white-space: nowrap; vertical-align: middle; }';
        echo '.wp-list-table .column-shortcode .wpds_geography_shortcode { border: 1px solid #DDDDDD; border-radius: 10px; padding: 10px; }';
        echo '</style>';
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Вывод списка всех маркеров
     * Страница $page_admin_menu_1 и $page_admin_menu_2
     */
    public function list_all_markers()
    {
        global $WPDSListTableForListMarkers;
        $WPDSListTableForListMarkers->display_template();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Отловили загрузку страницы $page_admin_menu_3
     */
    public function catch_load_page_admin_menu_3()
    {
        global $WPDS_Geography_Plugin;
        $WPDS_Geography_Plugin->wpds_load_js_and_css( 'marker' );
        
        wp_enqueue_style( 'wpds-geography-the-marker', WPDS_GEOGRAPHY_PLUGIN_URL . 'css/wpds_geography_backend.css' );
        wp_enqueue_script( array( 'jquery', 'editor', 'thickbox', 'media-upload' ) );
        wp_enqueue_style( 'thickbox' );
        
        require_once 'WPDSAddAndEditMarker.class.php';
        global $WPDSAddAndEditMarker;
        $WPDSAddAndEditMarker = new WPDSAddAndEditMarker();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Редактирование или добавление маркера
     * Страница $page_admin_menu_3
     */
    public function add_and_edit_marker()
    {
        global $WPDSAddAndEditMarker;
        $WPDSAddAndEditMarker->display_template();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Стили для страницы $page_admin_menu_4
     */
    public function admin_header_page_admin_menu_4()
    {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-type { width: 52px; vertical-align: middle; text-align: center; }';
        echo '.wp-list-table .column-name { width: 30%; }';
        echo '.wp-list-table .column-shortcode { width: 24%; white-space: nowrap; vertical-align: middle; }';
        echo '.wp-list-table .column-shortcode .wpds_geography_shortcode { border: 1px solid #DDDDDD; border-radius: 10px; padding: 10px; }';
        echo '</style>';
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Отловили загрузку страницы $page_admin_menu_4 и включаем на ней "Настройки экрана"
     */
    public function catch_load_page_admin_menu_4()
    {
        $option = 'per_page';
        $args = array(
                'label' => __( 'Layers', 'wp-ds-geography' ),
                'default' => 20,
                'option' => 'layers_per_page'
        );
        add_screen_option( $option, $args );

        add_action( 'admin_head', array( &$this, 'admin_header_page_admin_menu_4' ) );
    
        require_once 'WPDSListTableForListLayers.class.php';
        global $WPDSListTableForListLayers;
        $WPDSListTableForListLayers = new WPDSListTableForListLayers();
        $WPDSListTableForListLayers->prepare_items();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Вывод списка всех слоёв
     * Страница $page_admin_menu_4
     */
    public function list_all_layers()
    {
        global $WPDSListTableForListLayers;
        $WPDSListTableForListLayers->display_template();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Отловили загрузку страницы $page_admin_menu_5 (Добавление / изменение слоя)
     */
    public function catch_load_page_admin_menu_5()
    {
        global $WPDS_Geography_Plugin;
        $WPDS_Geography_Plugin->wpds_load_js_and_css( 'layer' );
        
        wp_enqueue_style( 'wpds-geography-the-marker', WPDS_GEOGRAPHY_PLUGIN_URL . 'css/wpds_geography_backend.css' );
    
        require_once 'WPDSAddAndEditLayer.class.php';
        global $WPDSAddAndEditLayer;
        $WPDSAddAndEditLayer = new WPDSAddAndEditLayer();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Редактирование или добавление маркера
     * Страница $page_admin_menu_5
     */
    public function add_and_edit_layer()
    {
        global $WPDSAddAndEditLayer;
        $WPDSAddAndEditLayer->display_template();
    }
}