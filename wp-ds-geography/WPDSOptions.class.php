<?php

/**
 * Работа с настройками плагина
 * @author DiamondSteel
 * 
 */
class WPDSOptions
{
    private $tabs;
    private $current_tab;
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function __construct( )
    {
        $this->tabs = array( 'permissions'  => __( 'Permissions',  'wp-ds-geography' ),
                             'maps'         => __( 'Maps',         'wp-ds-geography' ),
                             'map_source'   => __( 'Map source',   'wp-ds-geography' ),
                             'popup'        => __( 'Popup window', 'wp-ds-geography' ),
                             'markers'      => __( 'Markers',      'wp-ds-geography' ),
                             'layers'       => __( 'Layers',       'wp-ds-geography' ),
                             'markers_list' => __( 'Markers list', 'wp-ds-geography' ),
                             'reset'        => __( 'Reset',        'wp-ds-geography' ),
                             'about'        => __( 'About',        'wp-ds-geography' )
        );
        
        if ( ! isset ( $_GET['tab'] ) ) { $this->current_tab = 'permissions'; } else { $this->current_tab = $_GET['tab']; }
        if ( ! array_key_exists ( $this->current_tab, $this->tabs ) ) { $this->current_tab = 'permissions'; }
        
        if ( isset( $_POST['option_page'] ) ) {
            $this->tab_save_options();
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_save_options()
    {
        if ( $_POST['option_page'] == 'wpds_geography_options_markers' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_markers-options' ) ) {
            $wpds_geography_options_markers['mapwidth']      = $_POST['mapwidth'];
            $wpds_geography_options_markers['mapwidthunit']  = $_POST['mapwidthunit'];
            $wpds_geography_options_markers['mapheight']     = $_POST['mapheight'];
            $wpds_geography_options_markers['zoom']          = $_POST['zoom'];
            $wpds_geography_options_markers['lat']           = $_POST['lat'];
            $wpds_geography_options_markers['lon']           = $_POST['lon'];
            $wpds_geography_options_markers['layerscontrol'] = $_POST['layerscontrol'];
            $wpds_geography_options_markers['openpopup']     = isset( $_POST['openpopup'] ) ? 'true' : 'false';
            $wpds_geography_options_markers['icon']          = $_POST['icon'];
            update_option( 'wpds_geography_options_markers', $wpds_geography_options_markers );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_layers' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_layers-options' ) ) {
            $wpds_geography_options_layers['mapwidth']      = $_POST['mapwidth'];
            $wpds_geography_options_layers['mapwidthunit']  = $_POST['mapwidthunit'];
            $wpds_geography_options_layers['mapheight']     = $_POST['mapheight'];
            $wpds_geography_options_layers['zoom']          = $_POST['zoom'];
            $wpds_geography_options_layers['lat']           = $_POST['lat'];
            $wpds_geography_options_layers['lon']           = $_POST['lon'];
            $wpds_geography_options_layers['fitbounds']     = isset( $_POST['fitbounds'] ) ? 'true' : 'false';
            $wpds_geography_options_layers['clustering']    = isset( $_POST['clustering'] ) ? 'true' : 'false';
            $wpds_geography_options_layers['listmarkers']   = isset( $_POST['listmarkers'] ) ? 'true' : 'false';
            $wpds_geography_options_layers['layerscontrol'] = $_POST['layerscontrol'];
            update_option( 'wpds_geography_options_layers', $wpds_geography_options_layers );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_maps' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_maps-options' ) ) {
            $wpds_geography_options_maps['dragging']                              = isset( $_POST['dragging'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['worldCopyJump']                         = isset( $_POST['worldCopyJump'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['zoomControl']                           = isset( $_POST['zoomControl'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['touchZoom']                             = isset( $_POST['touchZoom'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['scrollWheelZoom']                       = isset( $_POST['scrollWheelZoom'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['scrollWheelZoomControl']                = isset( $_POST['scrollWheelZoomControl'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['doubleClickZoom']                       = isset( $_POST['doubleClickZoom'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['boxzoom']                               = isset( $_POST['boxzoom'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['trackResize']                           = isset( $_POST['trackResize'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['closePopupOnClick']                     = isset( $_POST['closePopupOnClick'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['keyboard']                              = isset( $_POST['keyboard'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['keyboardPanOffset']                     = $_POST['keyboardPanOffset'];
            $wpds_geography_options_maps['keyboardZoomOffset']                    = $_POST['keyboardZoomOffset'];
            $wpds_geography_options_maps['inertia']                               = isset( $_POST['inertia'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['inertiaDeceleration']                   = $_POST['inertiaDeceleration'];
            $wpds_geography_options_maps['inertiaMaxSpeed']                       = $_POST['inertiaMaxSpeed'];
            $wpds_geography_options_maps['scaleControl']                          = isset( $_POST['scaleControl'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['scale_control_position']                = $_POST['scale_control_position'];
            $wpds_geography_options_maps['scale_maxwidth']                        = $_POST['scale_maxwidth'];
            $wpds_geography_options_maps['metric']                                = isset( $_POST['metric'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['imperial']                              = isset( $_POST['imperial'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['updateWhenIdle']                        = isset( $_POST['updateWhenIdle'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['detectRetina']                          = isset( $_POST['detectRetina'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['fullscreen_mode']                       = isset( $_POST['fullscreen_mode'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_mode']                       = isset( $_POST['clustering_mode'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_zoomToBoundsOnClick']        = isset( $_POST['clustering_zoomToBoundsOnClick'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_showCoverageOnHover']        = isset( $_POST['clustering_showCoverageOnHover'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_spiderfyOnMaxZoom']          = isset( $_POST['clustering_spiderfyOnMaxZoom'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_spiderfyDistanceMultiplier'] = $_POST['clustering_spiderfyDistanceMultiplier'];
            $wpds_geography_options_maps['clustering_singleMarkerMode']           = isset( $_POST['clustering_singleMarkerMode'] ) ? 'true' : 'false';
            $wpds_geography_options_maps['clustering_disableClusteringAtZoom']    = $_POST['clustering_disableClusteringAtZoom'];
            $wpds_geography_options_maps['clustering_maxClusterRadius']           = $_POST['clustering_maxClusterRadius'];
            update_option( 'wpds_geography_options_maps', $wpds_geography_options_maps );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_popup' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_popup-options' ) ) {
            $wpds_geography_options_popup['maxWidth']         = $_POST['maxWidth'];
            $wpds_geography_options_popup['minWidth']         = $_POST['minWidth'];
            $wpds_geography_options_popup['maxHeight']        = $_POST['maxHeight'];
            $wpds_geography_options_popup['autoPan']          = isset( $_POST['autoPan'] ) ? 'true' : 'false';
            $wpds_geography_options_popup['autoPanPadding_x'] = $_POST['autoPanPadding_x'];
            $wpds_geography_options_popup['autoPanPadding_y'] = $_POST['autoPanPadding_y'];
            $wpds_geography_options_popup['closeButton']      = isset( $_POST['closeButton'] ) ? 'true' : 'false';
            $wpds_geography_options_popup['offset_x']         = $_POST['offset_x'];
            $wpds_geography_options_popup['offset_y']         = $_POST['offset_y'];
            update_option( 'wpds_geography_options_popup', $wpds_geography_options_popup );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_permissions' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_permissions-options' ) ) {
            $wpds_geography_options_permissions['capabilities_own']     = $_POST['capabilities_own'];
            $wpds_geography_options_permissions['capabilities_not_own'] = $_POST['capabilities_not_own'];
            update_option( 'wpds_geography_options_permissions', $wpds_geography_options_permissions );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_map_source' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_map_source-options' ) ) {
            $wpds_geography_options_map_source['osmUse']              = 'true';
            $wpds_geography_options_map_source['osmName']             = $_POST['osmName'];
            $wpds_geography_options_map_source['yandexUse']           = isset( $_POST['yandexUse'] ) ? 'true' : 'false';
            $wpds_geography_options_map_source['yandexName']          = $_POST['yandexName'];
            $wpds_geography_options_map_source['yandexAPI']           = $_POST['yandexAPI'];
            $wpds_geography_options_map_source['yandexLeafletPlugin'] = $_POST['yandexLeafletPlugin'];
            $wpds_geography_options_map_source['googleUse']           = isset( $_POST['googleUse'] ) ? 'true' : 'false';
            $wpds_geography_options_map_source['googleNameRoadmap']   = $_POST['googleNameRoadmap'];
            $wpds_geography_options_map_source['googleNameSatellite'] = $_POST['googleNameSatellite'];
            $wpds_geography_options_map_source['googleNameHybrid']    = $_POST['googleNameHybrid'];
            $wpds_geography_options_map_source['googleAPI']           = $_POST['googleAPI'];
            $wpds_geography_options_map_source['googleAPIKey']        = $_POST['googleAPIKey'];
            $wpds_geography_options_map_source['googleLeafletPlugin'] = $_POST['googleLeafletPlugin'];
            $wpds_geography_options_map_source['dgisUse']             = isset( $_POST['dgisUse'] ) ? 'true' : 'false';
            $wpds_geography_options_map_source['dgisName']            = $_POST['dgisName'];
            $wpds_geography_options_map_source['dgisAPI']             = $_POST['dgisAPI'];
            $wpds_geography_options_map_source['dgisLeafletPlugin']   = $_POST['dgisLeafletPlugin'];
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
            update_option( 'wpds_geography_options_map_source', $wpds_geography_options_map_source );
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_reset' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_reset-options' ) ) {
            if ( isset( $_POST['reset'] ) ) {
                global $WPDS_Geography_Plugin;
                $WPDS_Geography_Plugin->set_default_settings( 'update' );
                wp_redirect( admin_url( 'options-general.php?page=wpds-geography-options' ) );
                exit;
            }
        }
        if ( $_POST['option_page'] == 'wpds_geography_options_markers_list' and wp_verify_nonce( $_POST['_wpnonce'], 'wpds_geography_options_markers_list-options' ) ) {
            $wpds_geography_options_markers_list['listmarkers_sort']       = $_POST['listmarkers_sort'];
            $wpds_geography_options_markers_list['listmarkers_order']      = $_POST['listmarkers_order'];
            $wpds_geography_options_markers_list['listmarkers_mflag']      = isset( $_POST['listmarkers_mflag'] ) ? 'true' : 'false';
            $wpds_geography_options_markers_list['listmarkers_mpopup']     = isset( $_POST['listmarkers_mpopup'] ) ? 'true' : 'false';
            $wpds_geography_options_markers_list['listmarkers_mcreatedon'] = isset( $_POST['listmarkers_mcreatedon'] ) ? 'true' : 'false';
            $wpds_geography_options_markers_list['listmarkers_mupdatedon'] = isset( $_POST['listmarkers_mupdatedon'] ) ? 'true' : 'false';
            $wpds_geography_options_markers_list['listmarkers_mcreatedby'] = isset( $_POST['listmarkers_mcreatedby'] ) ? 'true' : 'false';
            $wpds_geography_options_markers_list['listmarkers_limit']      = $_POST['listmarkers_limit'];
            update_option( 'wpds_geography_options_markers_list', $wpds_geography_options_markers_list );
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_current_tab()
    {
        $echo_tab_contents = 'tab_contents_' . $this->current_tab;
        if ( method_exists( $this, $echo_tab_contents ) ) { $this->$echo_tab_contents(); }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_permissions()
    {
        $settings = get_option( 'wpds_geography_options_permissions' );
    
        echo '<h3>' . __( 'Setting permissions', 'wp-ds-geography' ) . '</h3>';
    
        if ( $settings['capabilities_own'] == 'activate_plugins'     ) { $checked_own_activate_plugins     = 'checked="checked" '; } else { $checked_own_activate_plugins     = ''; }
        if ( $settings['capabilities_own'] == 'moderate_comments'    ) { $checked_own_moderate_comments    = 'checked="checked" '; } else { $checked_own_moderate_comments    = ''; }
        if ( $settings['capabilities_own'] == 'edit_published_posts' ) { $checked_own_edit_published_posts = 'checked="checked" '; } else { $checked_own_edit_published_posts = ''; }
        if ( $settings['capabilities_own'] == 'edit_posts'           ) { $checked_own_edit_posts           = 'checked="checked" '; } else { $checked_own_edit_posts           = ''; }
        if ( $settings['capabilities_own'] == 'read'                 ) { $checked_own_read                 = 'checked="checked" '; } else { $checked_own_read                 = ''; }
    
        echo '<h4>' . __( 'Adding new, editing and deleting your markers and layers', 'wp-ds-geography' ) . '</h4>';
        echo '<label for="capabilities_own_0"><input type="radio" name="capabilities_own" id="capabilities_own_0" value="activate_plugins" ' . $checked_own_activate_plugins . '/>' . __( 'Administrator', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_own_1"><input type="radio" name="capabilities_own" id="capabilities_own_1" value="moderate_comments" ' . $checked_own_moderate_comments . '/>' . __( 'Editor', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_own_2"><input type="radio" name="capabilities_own" id="capabilities_own_2" value="edit_published_posts" ' . $checked_own_edit_published_posts . '/>' . __( 'Author', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_own_3"><input type="radio" name="capabilities_own" id="capabilities_own_3" value="edit_posts" ' . $checked_own_edit_posts . '/>' . __( 'Contributor', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_own_4"><input type="radio" name="capabilities_own" id="capabilities_own_4" value="read" ' . $checked_own_read . '/>' . __( 'Subscriber', 'wp-ds-geography' ) . '</label><BR />';
    
        if ( $settings['capabilities_not_own'] == 'activate_plugins'     ) { $checked_not_own_activate_plugins     = 'checked="checked" '; } else { $checked_not_own_activate_plugins     = ''; }
        if ( $settings['capabilities_not_own'] == 'moderate_comments'    ) { $checked_not_own_moderate_comments    = 'checked="checked" '; } else { $checked_not_own_moderate_comments    = ''; }
        if ( $settings['capabilities_not_own'] == 'edit_published_posts' ) { $checked_not_own_edit_published_posts = 'checked="checked" '; } else { $checked_not_own_edit_published_posts = ''; }
        if ( $settings['capabilities_not_own'] == 'edit_posts'           ) { $checked_not_own_edit_posts           = 'checked="checked" '; } else { $checked_not_own_edit_posts           = ''; }
        if ( $settings['capabilities_not_own'] == 'read'                 ) { $checked_not_own_read                 = 'checked="checked" '; } else { $checked_not_own_read                 = ''; }
    
        echo '<h4>' . __( 'Editing and deleting markers and layers created by another user', 'wp-ds-geography' ) . '</h4>';
        echo '<label for="capabilities_not_own_0"><input type="radio" name="capabilities_not_own" id="capabilities_not_own_0" value="activate_plugins" ' . $checked_not_own_activate_plugins . '/>' . __( 'Administrator', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_not_own_1"><input type="radio" name="capabilities_not_own" id="capabilities_not_own_1" value="moderate_comments" ' . $checked_not_own_moderate_comments . '/>' . __( 'Editor', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_not_own_2"><input type="radio" name="capabilities_not_own" id="capabilities_not_own_2" value="edit_published_posts" ' . $checked_not_own_edit_published_posts . '/>' . __( 'Author', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_not_own_3"><input type="radio" name="capabilities_not_own" id="capabilities_not_own_3" value="edit_posts" ' . $checked_not_own_edit_posts . '/>' . __( 'Contributor', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="capabilities_not_own_4"><input type="radio" name="capabilities_not_own" id="capabilities_not_own_4" value="read" ' . $checked_not_own_read . '/>' . __( 'Subscriber', 'wp-ds-geography' ) . '</label><BR />';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_maps()
    {
        $settings = get_option( 'wpds_geography_options_maps' );
        
        echo '<h3>' . __( 'Settings map interaction', 'wp-ds-geography' ) . '</h3>';
        echo '<p><i>' . __( 'These settings will be used for all maps.', 'wp-ds-geography' ) . '</i></p>';
        
        echo '<fieldset>';
        echo '<legend>' . __( 'Draggable maps', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="dragging"><input type="checkbox" name="dragging" id="dragging" ' . ( $settings['dragging'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map be draggable with mouse/touch.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="worldCopyJump"><input type="checkbox" name="worldCopyJump" id="worldCopyJump" ' . ( $settings['worldCopyJump'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'With this option enabled, the map tracks when you pan to another &quot;copy&quot; of the world and seamlessly jumps to the original one so that all overlays like markers and vector layers are still visible.', 'wp-ds-geography' ) . '</label><BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Zoom control', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="zoomControl"><input type="checkbox" name="zoomControl" id="zoomControl" ' . ( $settings['zoomControl'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the zoom control is added to the map.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="touchZoom"><input type="checkbox" name="touchZoom" id="touchZoom" ' . ( $settings['touchZoom'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map can be zoomed by touch-dragging with two fingers.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="scrollWheelZoom"><input type="checkbox" name="scrollWheelZoom" id="scrollWheelZoom" ' . ( $settings['scrollWheelZoom'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map can be zoomed by using the mouse wheel.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="scrollWheelZoomControl"><input type="checkbox" name="scrollWheelZoomControl" id="scrollWheelZoomControl" ' . ( $settings['scrollWheelZoomControl'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the zoom mouse wheel control is added to the map.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="doubleClickZoom"><input type="checkbox" name="doubleClickZoom" id="doubleClickZoom" ' . ( $settings['doubleClickZoom'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map can be zoomed in by double clicking on it and zoomed out by double clicking while holding shift.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="boxzoom"><input type="checkbox" name="boxzoom" id="boxzoom" ' . ( $settings['boxzoom'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map can be zoomed to a rectangular area specified by dragging the mouse while pressing shift.', 'wp-ds-geography' ) . '</label><BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<label for="trackResize"><input type="checkbox" name="trackResize" id="trackResize" ' . ( $settings['trackResize'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether the map automatically handles browser window resize to update itself.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="closePopupOnClick"><input type="checkbox" name="closePopupOnClick" id="closePopupOnClick" ' . ( $settings['closePopupOnClick'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Close pop-up windows when the user clicks on the map.', 'wp-ds-geography' ) . '</label><BR />';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Keyboard navigation options', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="keyboard"><input type="checkbox" name="keyboard" id="keyboard" ' . ( $settings['keyboard'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Allows users to navigate the map with keyboard arrows and +/- keys.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<input type="text" id="keyboardPanOffset" name="keyboardPanOffset" value="' . $settings['keyboardPanOffset'] . '" /> ' . __( 'Amount of pixels to pan when pressing an arrow key.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="keyboardZoomOffset" name="keyboardZoomOffset" value="' . $settings['keyboardZoomOffset'] . '" /> ' . __( 'Number of zoom levels to change when pressing + or - key.', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Inertia', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="inertia"><input type="checkbox" name="inertia" id="inertia" ' . ( $settings['inertia'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'If enabled, panning of the map will have an inertia effect where the map builds momentum while dragging and continues moving in the same direction for some time. Feels especially nice on touch devices.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<input type="text" id="inertiaDeceleration" name="inertiaDeceleration" value="' . $settings['inertiaDeceleration'] . '" /> ' . __( 'The rate with which the inertial movement slows down, in pixels/second<sup>2</sup>.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="inertiaMaxSpeed" name="inertiaMaxSpeed" value="' . $settings['inertiaMaxSpeed'] . '" /> ' . __( 'Max speed of the inertial movement, in pixels/second.', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Graduated scale', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="scaleControl"><input type="checkbox" name="scaleControl" id="scaleControl" ' . ( $settings['scaleControl'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Add a graduated scale.', 'wp-ds-geography' ) . '</label><BR /><BR />';
        if ( $settings['scale_control_position'] == 'bottomleft'  ) { $checked_bottomleft  = 'checked="checked" '; } else { $checked_bottomleft  = ''; }
        if ( $settings['scale_control_position'] == 'bottomright' ) { $checked_bottomright = 'checked="checked" '; } else { $checked_bottomright = ''; }
        if ( $settings['scale_control_position'] == 'topright'    ) { $checked_topright    = 'checked="checked" '; } else { $checked_topright    = ''; }
        if ( $settings['scale_control_position'] == 'topleft'     ) { $checked_topleft     = 'checked="checked" '; } else { $checked_topleft     = ''; }
        echo '<label for="scale_control_position_0"><input type="radio" name="scale_control_position" id="scale_control_position_0" value="bottomleft" ' . $checked_bottomleft . '/>' . __( 'Bottom left of the map.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="scale_control_position_1"><input type="radio" name="scale_control_position" id="scale_control_position_1" value="bottomright" ' . $checked_bottomright . '/>' . __( 'Bottom right of the map.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="scale_control_position_2"><input type="radio" name="scale_control_position" id="scale_control_position_2" value="topright" ' . $checked_topright . '/>' . __( 'Top right of the map.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="scale_control_position_3"><input type="radio" name="scale_control_position" id="scale_control_position_3" value="topleft" ' . $checked_topleft . '/>' . __( 'Top left of the map.', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<input type="text" id="scale_maxwidth" name="scale_maxwidth" value="' . $settings['scale_maxwidth'] . '" /> ' . __( 'Maximum width of the control in pixels. The width is set dynamically to show round values (e.g. 100, 200, 500).', 'wp-ds-geography' ) . '<BR /><BR />';
        echo '<label for="metric"><input type="checkbox" name="metric" id="metric" ' . ( $settings['metric'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether to show the metric scale line (m/km).', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="imperial"><input type="checkbox" name="imperial" id="imperial" ' . ( $settings['imperial'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Whether to show the imperial scale line (mi/ft).', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="updateWhenIdle"><input type="checkbox" name="updateWhenIdle" id="updateWhenIdle" ' . ( $settings['updateWhenIdle'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'If true, the control is updated on moveend, otherwise it&prime;s always up-to-date (updated on move).', 'wp-ds-geography' ) . '</label><BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Detection of Retina display', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="detectRetina"><input type="checkbox" name="detectRetina" id="detectRetina" ' . ( $settings['detectRetina'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Optimize maps for display Retina.', 'wp-ds-geography' ) . '</label><BR />';
        echo '</fieldset>';

        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Fullscreen mode', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="fullscreen_mode"><input type="checkbox" name="fullscreen_mode" id="fullscreen_mode" ' . ( $settings['fullscreen_mode'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Add fullscreen button to a maps.', 'wp-ds-geography' ) . '</label><BR />';
        echo '</fieldset>';
        
        echo '<BR />';
        echo '<fieldset>';
        echo '<legend>' . __( 'Marker grouping', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="clustering_mode"><input type="checkbox" name="clustering_mode" id="clustering_mode" ' . ( $settings['clustering_mode'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Allow grouping of markers.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<BR />';
        echo '<label for="clustering_zoomToBoundsOnClick"><input type="checkbox" name="clustering_zoomToBoundsOnClick" id="clustering_zoomToBoundsOnClick" ' . ( $settings['clustering_zoomToBoundsOnClick'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'When you click a cluster we zoom to its bounds.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="clustering_showCoverageOnHover"><input type="checkbox" name="clustering_showCoverageOnHover" id="clustering_showCoverageOnHover" ' . ( $settings['clustering_showCoverageOnHover'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'When you mouse over a cluster it shows the bounds of its markers.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="clustering_spiderfyOnMaxZoom"><input type="checkbox" name="clustering_spiderfyOnMaxZoom" id="clustering_spiderfyOnMaxZoom" ' . ( $settings['clustering_spiderfyOnMaxZoom'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'When you click a cluster at the bottom zoom level we spiderfy it so you can see all of its markers.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<input type="text" id="clustering_spiderfyDistanceMultiplier" name="clustering_spiderfyDistanceMultiplier" value="' . $settings['clustering_spiderfyDistanceMultiplier'] . '" /> ' . __( 'Increase from 1 to increase the distance away from the center that spiderfied markers are placed. Use if you are using big marker icons.', 'wp-ds-geography' ) . '<BR />';
        echo '<label for="clustering_singleMarkerMode"><input type="checkbox" name="clustering_singleMarkerMode" id="clustering_singleMarkerMode" ' . ( $settings['clustering_singleMarkerMode'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( ' If set to true, overrides the icon for all added markers to make them appear as a 1 size cluster', 'wp-ds-geography' ) . '</label><BR />';
        
        echo '<select name="clustering_disableClusteringAtZoom">';
        for ( $i = 1; $i <= 19; $i++ ){
            if ( $settings['clustering_disableClusteringAtZoom'] == $i ) { $selected = ' selected'; } else { $selected = ''; }
            echo '<option value="' . $i . '"'.$selected.'>' . $i . '</option>';
        }
        echo '</select>';
        echo ' ' . __( ' If set, at this zoom level and below markers will not be clustered.', 'wp-ds-geography' ) . '<BR />';
        
        echo '<input type="text" id="clustering_maxClusterRadius" name="clustering_maxClusterRadius" value="' . $settings['clustering_maxClusterRadius'] . '" /> ' . __( 'The maximum radius that a cluster will cover from the central marker (in pixels). ', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';

        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_map_source()
    {
        $settings = get_option( 'wpds_geography_options_map_source' );
    
        echo '<h3>Настройки поставщиков карт</h3>';
        echo '<p><i>' . __( 'These settings will be used for all maps.', 'wp-ds-geography' ) . '</i></p>';
    
        echo '<fieldset>';
        echo '<legend>' . __( 'OpenStreetMap', 'wp-ds-geography' ) . '</legend>';
        echo '<input type="text" id="osmName" name="osmName" value="' . $settings['osmName'] . '" /> ' . __( 'The name of the map.', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';
    
        echo '<BR />';
    
        echo '<fieldset>';
        echo '<legend>' . __( 'Yandex', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="yandexUse"><input type="checkbox" name="yandexUse" id="yandexUse" ' . ( $settings['yandexUse'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Use this provider maps.', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<input type="text" id="yandexName" name="yandexName" value="' . $settings['yandexName'] . '" /> ' . __( 'The name of the map.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="yandexAPI" name="yandexAPI" value="' . $settings['yandexAPI'] . '" class="regular-text" /> ' . __( 'API', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="yandexLeafletPlugin" name="yandexLeafletPlugin" value="' . $settings['yandexLeafletPlugin'] . '" class="regular-text" /> ' . __( 'Plugin for Leaflet', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';
    
        echo '<BR />';
    
        echo '<fieldset>';
        echo '<legend>' . __( 'Google', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="googleUse"><input type="checkbox" name="googleUse" id="googleUse" ' . ( $settings['googleUse'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Use this provider maps.', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<input type="text" id="googleNameRoadmap" name="googleNameRoadmap" value="' . $settings['googleNameRoadmap'] . '" /> ' . __( 'The name of the map. (Roadmap)', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="googleNameSatellite" name="googleNameSatellite" value="' . $settings['googleNameSatellite'] . '" /> ' . __( 'The name of the map. (Satellite)', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="googleNameHybrid" name="googleNameHybrid" value="' . $settings['googleNameHybrid'] . '" /> ' . __( 'The name of the map. (Hybrid)', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="googleAPI" name="googleAPI" value="' . $settings['googleAPI'] . '" class="regular-text" /> ' . __( 'API', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="googleAPIKey" name="googleAPIKey" value="' . $settings['googleAPIKey'] . '" class="regular-text" /> ' . __( 'API key', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="googleLeafletPlugin" name="googleLeafletPlugin" value="' . $settings['googleLeafletPlugin'] . '" class="regular-text" /> ' . __( 'Plugin for Leaflet', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';
    
        echo '<BR />';
    
        echo '<fieldset>';
        echo '<legend>' . __( '2GIS', 'wp-ds-geography' ) . '</legend>';
        echo '<label for="dgisUse"><input type="checkbox" name="dgisUse" id="dgisUse" ' . ( $settings['dgisUse'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Use this provider maps.', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<input type="text" id="dgisName" name="dgisName" value="' . $settings['dgisName'] . '" /> ' . __( 'The name of the map.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="dgisAPI" name="dgisAPI" value="' . $settings['dgisAPI'] . '" class="regular-text" /> ' . __( 'API', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="dgisLeafletPlugin" name="dgisLeafletPlugin" value="' . $settings['dgisLeafletPlugin'] . '" class="regular-text" /> ' . __( 'Plugin for Leaflet', 'wp-ds-geography' ) . '<BR />';
        echo '</fieldset>';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_popup()
    {
        $settings = get_option( 'wpds_geography_options_popup' );
    
        echo '<h3>' . __( 'Settings popup windows.', 'wp-ds-geography' ) . '</h3>';
        echo '<p><i>' . __( 'These settings will be used for all popup windows.', 'wp-ds-geography' ) . '</i></p>';
    
        echo '<input type="text" id="maxWidth" name="maxWidth" value="' . $settings['maxWidth'] . '" /> ' . __( 'Maximum width of popup window in pixels.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="minWidth" name="minWidth" value="' . $settings['minWidth'] . '" /> ' . __( 'Minimum width of popup window in pixels.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="maxHeight" name="maxHeight" value="' . $settings['maxHeight'] . '" /> ' . __( 'If set, a scrollable container of a given height in pixels appears inside popup windows.', 'wp-ds-geography' ) . '<BR /><BR />';
        echo '<label for="closeButton"><input type="checkbox" name="closeButton" id="closeButton" ' . ( $settings['closeButton'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( '&quot;Close&quot; button in popup windows', 'wp-ds-geography' ) . '</label><BR /><BR />';
        
        echo '<label for="autoPan"><input type="checkbox" name="autoPan" id="autoPan" ' . ( $settings['autoPan'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Move map if the popup window doesn’t fit when open.', 'wp-ds-geography' ) . '</label><BR />';
        echo '<input type="text" id="autoPanPadding_x" name="autoPanPadding_x" value="' . $settings['autoPanPadding_x'] . '" /> ' . __( 'Distance in pixels between the popup window and left or right map margin.', 'wp-ds-geography' ) . '<BR />';
        echo '<input type="text" id="autoPanPadding_y" name="autoPanPadding_y" value="' . $settings['autoPanPadding_y'] . '" /> ' . __( 'Distance in pixels between the popup window and upper or lower map margin.', 'wp-ds-geography' ) . '<BR /><BR />';
        
        echo '<input type="text" id="offset_x" name="offset_x" value="' . $settings['offset_x'] . '" /> ' . __( 'Popup window position horizontal shift relative to the marker.', 'wp-ds-geography' ) . '</span><BR />';
        echo '<input type="text" id="offset_y" name="offset_y" value="' . $settings['offset_y'] . '" /> ' . __( 'Popup window position vertical shift relative to the marker.', 'wp-ds-geography' ) . '</span><BR />';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_markers()
    {
        $settings = get_option( 'wpds_geography_options_markers' );
        $settings_map_source = get_option( 'wpds_geography_options_map_source' );
    
        echo '<h3>' . __( 'Default settings for the new marker', 'wp-ds-geography' ) . '</h3>';
        echo '<p><i>' . __( 'These settings can be adjusted individually for each marker.', 'wp-ds-geography' ) . '</i></p>';
    
        echo '<h4>' . __( 'Map size', 'wp-ds-geography' ) . '</h4>';
        echo __( 'Width:', 'wp-ds-geography' ) . ' <input size="3" maxlength="4" type="text" id="mapwidth" name="mapwidth" value="' . $settings['mapwidth'] . '"> ';
        echo '<label for="mapwidthunit_px"><input id="mapwidthunit_px" type="radio" name="mapwidthunit" value="px" ' . ( $settings['mapwidthunit'] == 'px' ?  'checked="checked" ' : '' ) . '/> px &nbsp;</label>';
        echo '<label for="mapwidthunit_percent"><input id="mapwidthunit_percent" type="radio" name="mapwidthunit" value="%" ' . ( $settings['mapwidthunit'] == '%' ?  'checked="checked" ' : '' ) . '/> %</label> &nbsp; &nbsp; &nbsp; ';
        echo __( 'Height:', 'wp-ds-geography' ) . ' <input size="3" maxlength="4" type="text" id="mapheight" name="mapheight" value="' . $settings['mapheight'] . '"> px &nbsp; &nbsp; &nbsp; ';
        echo __( 'Scale:', 'wp-ds-geography' ) . ' ';
        echo '<select name="zoom">';
        for ( $i = 1; $i <= 18; $i++ ){
            if ( $settings['zoom'] == $i ) { $selected = ' selected'; } else { $selected = ''; }
            echo '<option value="' . $i . '"'.$selected.'>' . $i . '</option>';
        }
        echo '</select>';
    
        echo '<h4>' . __( 'Original coordinates', 'wp-ds-geography' ) . '</h4>';
        echo __( 'Latitude:', 'wp-ds-geography' ) . ' <input type="text" id="lat" name="lat" value="' . $settings['lat'] . '" /> &nbsp; &nbsp; &nbsp; ';
        echo __( 'Longitude:', 'wp-ds-geography' ) . ' <input type="text" id="lon" name="lon" value="' . $settings['lon'] . '" />';
    
        echo '<h4>' . __( 'Map source', 'wp-ds-geography' ) . '</h4>';
    
        foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ){
            if ( $key == $settings['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
            if ( $val['use'] == 'true' ){
                echo '<label for="layerscontrol-' . $key . '">';
                echo '<input type="radio" name="layerscontrol" value="' . $key . '" id="layerscontrol-' . $key . '" ' . $checked . ' />';
                echo $val['name'] . '</label><BR />';
            }
        }
    
        echo '<h4>' . __( 'Popup window', 'wp-ds-geography' ) . '</h4>';
        echo '<label for="openpopup"><input type="checkbox" name="openpopup" id="openpopup" ' . ( $settings['openpopup'] == 'true' ? 'checked="checked" ' : '' ) . '/> ' . __( 'Open popup window', 'wp-ds-geography' ) . '</label><BR /><i>' . __( 'If unchecked, the popup window will be shown only after marker is clicked.', 'wp-ds-geography' ) . '</i>';
    
        echo '<h4>' . __( 'Flag', 'wp-ds-geography' ) . '</h4>';
    
        $dir = WPDS_GEOGRAPHY_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'icons';
        $icondir = opendir( $dir );
        while ( false !== ( $file = readdir( $icondir ) ) ) {
            if ( $file != "." AND $file != ".." ) {
                if ( !is_dir( $dir . $file ) ) { $files[] = $file; }
            }
        }
        closedir( $icondir );
        
        natsort( $files );
    
        foreach ($files as $key => $val){
            if ( $val == $settings['icon'] ) { $checked = 'checked="checked" '; } else { $checked = ''; }
            $p = strrpos( $val, '.' );
            if ( $p > 0 ) { $id = substr( $val, 0, $p ); } else { $id = $val; }
            echo '<div class="markericon">';
            echo '<label for="' . $id . '"><img width="32" height="37" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'icons/' . $val . '" /></label><BR />';
            echo '<input type="radio" name="icon" value="' . $val . '" id="' . $id . '" ' . $checked . '>';
            echo '</div>';
        }
        echo '<div class="clear"></div>';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_layers()
    {
        $settings            = get_option( 'wpds_geography_options_layers' );
        $settings_map_source = get_option( 'wpds_geography_options_map_source' );
        $settings_maps       = get_option( 'wpds_geography_options_maps' );
    
        echo '<h3>' . __( 'Default settings for the new layer', 'wp-ds-geography' ) . '</h3>';
        echo '<p><i>' . __( 'These settings can be adjusted individually for each layer.', 'wp-ds-geography' ) . '</i></p>';
    
        echo '<h4>' . __( 'Map settings', 'wp-ds-geography' ) . '</h4>';
    
        echo __( 'Width:', 'wp-ds-geography' ) . ' <input size="3" maxlength="4" type="text" id="mapwidth" name="mapwidth" value="' . $settings['mapwidth'] . '"> ';
        echo '<label for="mapwidthunit_px"><input id="mapwidthunit_px" type="radio" name="mapwidthunit" value="px" ' . ( $settings['mapwidthunit'] == 'px' ?  'checked="checked" ' : '' ) . '/> px &nbsp;</label>';
        echo '<label for="mapwidthunit_percent"><input id="mapwidthunit_percent" type="radio" name="mapwidthunit" value="%" ' . ( $settings['mapwidthunit'] == '%' ?  'checked="checked" ' : '' ) . '/> %</label> &nbsp; &nbsp; &nbsp; ';
        echo __( 'Height:', 'wp-ds-geography' ) . ' <input size="3" maxlength="4" type="text" id="mapheight" name="mapheight" value="' . $settings['mapheight'] . '"> px <BR /><BR /> ';
    
        echo '<fieldset>';
        echo '<p>' . __( 'Set the scale and center of the map', 'wp-ds-geography' ) . '</p>';
        echo __( 'Scale:', 'wp-ds-geography' ) . ' ';
        echo '<select name="zoom">';
        for ( $i = 1; $i <= 18; $i++ ){
            if ( $settings['zoom'] == $i ) { $selected = ' selected'; } else { $selected = ''; }
            echo '<option value="' . $i . '"'.$selected.'>' . $i . '</option>';
        }
        echo '</select><BR />';
        echo __( 'Latitude:', 'wp-ds-geography' ) . ' <input type="text" id="lat" name="lat" value="' . $settings['lat'] . '" /> &nbsp; &nbsp; &nbsp; ';
        echo __( 'Longitude:', 'wp-ds-geography' ) . ' <input type="text" id="lon" name="lon" value="' . $settings['lon'] . '" />';
        echo '<p>' . __( 'or you can', 'wp-ds-geography' ) . '</p>';
        echo '<label for="fitbounds"><input type="checkbox" name="fitbounds" id="fitbounds" ' . ( $settings['fitbounds'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'fit bounds', 'wp-ds-geography' ) . '</label>';
        echo '</fieldset><BR />';
    
        echo '<label for="clustering"><input type="checkbox" name="clustering" id="clustering" ' . ( $settings['clustering'] == 'true' ? 'checked="checked" ' : '' ) . ' ' . ( $settings_maps['clustering_mode'] == 'false' ? 'disabled="disabled" ' : '' ) . '/>' . __( 'Group markers', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<label for="listmarkers"><input type="checkbox" name="listmarkers" id="listmarkers" ' . ( $settings['listmarkers'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Show markers list under the map', 'wp-ds-geography' ) . '</label><BR />';
    
        echo '<h4>' . __( 'Map source', 'wp-ds-geography' ) . '</h4>';
    
        foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ){
            if ( $key == $settings['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
            if ( $val['use'] == 'true' ){
                echo '<label for="layerscontrol-' . $key . '">';
                echo '<input type="radio" name="layerscontrol" value="' . $key . '" id="layerscontrol-' . $key . '" ' . $checked . ' />';
                echo $val['name'] . '</label><BR />';
            }
        }
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_markers_list()
    {
        $settings = get_option( 'wpds_geography_options_markers_list' );
    
        echo '<h3>' . __( 'Settings markers list', 'wp-ds-geography' ) . '</h3>';
        echo '<p><i>' . __( 'These settings will be used for all markers list.', 'wp-ds-geography' ) . '</i></p>';
    
        echo '<h4>' . __( 'Sorting', 'wp-ds-geography' ) . '</h4>';
        echo '<label for="listmarkers_sort-name"><input type="radio" name="listmarkers_sort" value="name" id="listmarkers_sort-name" ' . ( $settings['listmarkers_sort'] == 'name' ?  'checked="checked" ' : '' ) . '/> ' . __( 'by name', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_sort-createdon"><input type="radio" name="listmarkers_sort" value="createdon" id="listmarkers_sort-createdon" ' . ( $settings['listmarkers_sort'] == 'createdon' ?  'checked="checked" ' : '' ) . '/> ' . __( 'by date', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_sort-updatedon"><input type="radio" name="listmarkers_sort" value="createdon" id="listmarkers_sort-updatedon" ' . ( $settings['listmarkers_sort'] == 'updatedon' ?  'checked="checked" ' : '' ) . '/> ' . __( 'by date updated', 'wp-ds-geography' ) . '</label><BR />';
        echo '<BR />';
        echo '<label for="listmarkers_order-asc"><input type="radio" name="listmarkers_order" value="asc" id="listmarkers_order-asc" ' . ( $settings['listmarkers_order'] == 'asc' ?  'checked="checked" ' : '' ) . '/> ' . __( 'by ascending', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_order-desc"><input type="radio" name="listmarkers_order" value="desc" id="listmarkers_order-desc" ' . ( $settings['listmarkers_order'] == 'desc' ?  'checked="checked" ' : '' ) . '/> ' . __( 'by descending', 'wp-ds-geography' ) . '</label><BR />';
    
        echo '<h4>' . __( 'Columns', 'wp-ds-geography' ) . '</h4>';
        echo '<label for="listmarkers_mflag"><input type="checkbox" name="listmarkers_mflag" id="listmarkers_mflag" ' . ( $settings['listmarkers_mflag'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Flag', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_mpopup"><input type="checkbox" name="listmarkers_mpopup" id="listmarkers_mpopup" ' . ( $settings['listmarkers_mpopup'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Popup window', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_mcreatedon"><input type="checkbox" name="listmarkers_mcreatedon" id="listmarkers_mcreatedon" ' . ( $settings['listmarkers_mcreatedon'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Created date', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_mupdatedon"><input type="checkbox" name="listmarkers_mupdatedon" id="listmarkers_mupdatedon" ' . ( $settings['listmarkers_mupdatedon'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Modified date', 'wp-ds-geography' ) . '</label><BR />';
        echo '<label for="listmarkers_mcreatedby"><input type="checkbox" name="listmarkers_mcreatedby" id="listmarkers_mcreatedby" ' . ( $settings['listmarkers_mcreatedby'] == 'true' ? 'checked="checked" ' : '' ) . '/>' . __( 'Author', 'wp-ds-geography' ) . '</label><BR /><BR />';
        echo '<input type="text" id="listmarkers_limit" name="listmarkers_limit" value="' . $settings['listmarkers_limit'] . '" /> ' . __( 'The maximum number of tokens in the list', 'wp-ds-geography' ) . '<BR />';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_reset()
    {
        echo '<h3>' . __( 'Reset settings', 'wp-ds-geography' ) . '</h3>';
    
        echo '<label for="reset"><input type="checkbox" name="reset" id="reset" />' . __( 'Restore Defaults', 'wp-ds-geography' ) . '</label><BR />';
    
        settings_fields( 'wpds_geography_options_' . $this->current_tab );
        submit_button();
    }   

    #-------------------------------------------------------------------------------------------------------------------------#
    public function tab_contents_about()
    {
        echo '<h3>' . __( 'About plugin', 'wp-ds-geography' ) . '</h3>';
        echo '<h4>' . __( 'Used services, technology and image', 'wp-ds-geography' ) . '</h4>';
        echo '<ul>';
        echo '<li>Leaflet, <a href="http://www.leafletjs.com" target="_blank">http://www.leafletjs.com</a></li>';
        echo '<li>Plugin for Leaflet: Leaflet.MarkerCluster, <a href="http://github.com/Leaflet/Leaflet.markercluster" target="_blank">http://github.com/Leaflet/Leaflet.markercluster</a></li>';
        echo '<li>Plugin for Leaflet: Google, Yandex, <a href="http://github.com/shramov/leaflet-plugins" target="_blank">http://github.com/shramov/leaflet-plugins</a></li>';
        echo '<li>Plugin for Leaflet: 2GIS, <a href="http://github.com/emikhalev/leaflet-2gis" target="_blank">http://github.com/emikhalev/leaflet-2gis</a></li>';
        echo '<li>Plugin for Leaflet: Leaflet.Fullscreen, <a href="http://brunob.github.io/leaflet.fullscreen/" target="_blank">http://brunob.github.io/leaflet.fullscreen/</a></li>';
        echo '<li>OpenStreetMap: <a href="http://www.openstreetmap.org/" target="_blank">http://www.openstreetmap.org/</a></li>';
        echo '<li>Yandex Maps: <a href="http://maps.yandex.ru/" target="_blank">http://maps.yandex.ru/</a></li>';
        echo '<li>Google Maps: <a href="http://www.google.com/maps/" target="_blank">http://www.google.com/maps/</a></li>';
        echo '<li>2GIS Maps: <a href="http://2gis.ru/" target="_blank">http://2gis.ru/</a></li>';
        echo '<li>Address autocompletion powered by <a href="http://code.google.com/intl/de-AT/apis/maps/documentation/places/autocomplete.html" target="_blank">Google Places API</a></li>';
        echo '<li><a href="http://mapicons.nicolasmollet.com" target="_blank">Map Icons Collection</a></li>';
        echo '</ul>';
    
        echo '<h4>' . __( 'Authors and Contributors', 'wp-ds-geography' ) . '</h4>';
        echo '<p><a href="http://diamondsteel.ru" target="_blank">DiamondSteel</a></p>';
    
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_generate_tabs()
    {
        foreach( $this->tabs as $tab => $name ){
            $class = ( $tab == $this->current_tab ) ? ' nav-tab-active' : '';
            echo "<a class=\"nav-tab{$class}\" href=\"?page=wpds-geography-options&tab={$tab}\">{$name}</a>";
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Создаём в Админ-панеле в разделе Параметры своё подменю.
     */
    public function add_options_page()
    {
        $options_page = add_options_page( __( 'Geography settings', 'wp-ds-geography' ), __( 'Geography', 'wp-ds-geography' ), 'manage_options', 'wpds-geography-options', array( &$this, 'options_page' ) );
        
        /* Отлавдиваем загрузку страницы $options_page */
        add_action( 'load-' . $options_page, array( &$this, 'catch_load_options_page' ) );
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function options_page() {
        include 'template-wpds-options.php';
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function catch_load_options_page() {
        wp_enqueue_style( 'wpds-geography-the-marker', WPDS_GEOGRAPHY_PLUGIN_URL . 'css/wpds_geography_backend.css' );
    }
}