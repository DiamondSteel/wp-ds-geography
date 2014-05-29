<?php

/**
 *
 * @author DiamondSteel
 *
 */
class WPDSGenerateMapJS
{
    private $salt;
    private $superposition_element;
    private $type;
    private $marker_list;
    
    private $permissions_settings;
    
    public $backend_script;
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     * 
     * @param string $salt - рандомная строка
     * @param array $superposition_element - массив с данными слоя или маркера
     * @param string $type - layer | marker
     * @param array $marker_list - массив маркеров прикреплённых к слою
     */
    public function __construct( $salt, $superposition_element, $type, $marker_list = false )
    {
        $this->salt = $salt;
        $this->superposition_element = $superposition_element;
        $this->type = $type;
        $this->marker_list = $marker_list;
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Формирование HTML карты
     * 
     * @return string
     */
    public function get_map()
    {
        $settings_map = get_option( 'wpds_geography_options_maps' );
        
        $salt = $this->salt;
        
        $map = "<div id=\"map_" . $salt . "\" style=\"width: " . $this->superposition_element[$this->type]['mapwidth'] . $this->superposition_element[$this->type]['mapwidthunit'] . "; height: " . $this->superposition_element[$this->type]['mapheight'] . "px;\"></div>" . PHP_EOL;
        
        if ( $this->backend_script == FALSE ) {
            $current_user = wp_get_current_user();
            if ( $this->superposition_element[$this->type]['createdby'] == $current_user->ID or current_user_can( $this->permissions_settings['capabilities_not_own'] ) ) {
                $map .= '<div class="wpds_geography_edit_panel">';
                $map .= '<a href="' . esc_url( admin_url( 'admin.php?page=wpds_geography_the_' . $this->type ) ) . '&id=' . $this->superposition_element[$this->type]['id'] .'">';
                $map .= '<img width="16" height="16" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'images/edit.png" alt="' . __( 'Edit', 'wp-ds-geography' ) . '" title="' . __( 'Edit', 'wp-ds-geography' ) . '"/>';
                $map .= '</a>';
                $map .= '</div>';
            }
        }
        
        if ( $this->type == 'layer' ) {
            $settings_markers_list = get_option( 'wpds_geography_options_markers_list' );
            if ( $this->backend_script == TRUE ) { $settings_markers_list['listmarkers_limit'] = 3; }
            
            $i = 1;
            if ( $this->superposition_element[$this->type]['listmarkers'] == 1 ) {
                $map .= "<table class=\"wpds_geography_list_marker\" style=\"width: " . $this->superposition_element[$this->type]['mapwidth'] . $this->superposition_element[$this->type]['mapwidthunit'] . ";\">";
                foreach ( $this->marker_list as $marker ) {
                    $map .= "<tr valign=\"top\" class=\"wpds_geography_list_marker_item\">";
                    if ( $settings_markers_list['listmarkers_mflag'] == 'true' ) {
                        $map .= "<td class=\"wpds_geography_list_marker_icon\"><img width=\"32\" height=\"37\" src=\"" . WPDS_GEOGRAPHY_PLUGIN_URL . "icons/" . $marker['icon'] . "\" /></td>";
                    }
                    $map .= "<td class=\"wpds_geography_list_marker_name_and_popup\">";
                    if ( $settings_map['clustering_mode'] == 'true' and $this->superposition_element[$this->type]['clustering'] == 1 ) {
                        $map .= htmlspecialchars( stripslashes( $marker['markername'] ), ENT_QUOTES );
                        $afer_map = '';
                    } else {
                        $map .= "<span class=\"wpds_geography_list_marker_name\"><a href=\"#\" onclick=\"wpds_" . $salt . "(marker_" . $marker['id'] . "_" . $salt . "); return false;\">" . htmlspecialchars( stripslashes( $marker['markername'] ), ENT_QUOTES ) . "</a></span>";
                        $afer_map = '';
                        $afer_map .= "<script type=\"text/javascript\">" . PHP_EOL;
                        $afer_map .= "/* //<![CDATA[ */" . PHP_EOL;
                        $afer_map .= "function wpds_" . $salt . "(marker){" . PHP_EOL;
                        $afer_map .= "    marker.openPopup();" . PHP_EOL;
                        $afer_map .= "    jQuery('html, body').animate({scrollTop:jQuery('#map_" . $salt . "').offset().top-100}, 'slow');" . PHP_EOL;
                        $afer_map .= "}" . PHP_EOL;
                        $afer_map .= "/* //]]> */" . PHP_EOL;
                        $afer_map .= "</script>" . PHP_EOL;
                    }
                    if ( $settings_markers_list['listmarkers_mpopup'] == 'true' ) {
                        $map .= "<div class=\"wpds_geography_list_marker_popuptext\">" . stripslashes( preg_replace( '/(\015\012)|(\015)|(\012)/','<BR />', $marker['popuptext'] ) ) . "</div><div style=\"clear:both;\"></div>";
                    }
                    $map .= "</td>";
                    if ( $settings_markers_list['listmarkers_mcreatedon'] == 'true' or $settings_markers_list['listmarkers_mupdatedon'] == 'true' ) {
                        $map .= "<td class=\"wpds_geography_list_marker_date\">";
                        if ( $settings_markers_list['listmarkers_mcreatedon'] == 'true' ) { $map .= "<div class=\"wpds_geography_list_marker_date_string\">" . $marker['createdon'] . "</div>"; }
                        if ( $settings_markers_list['listmarkers_mupdatedon'] == 'true' ) { $map .= "<div class=\"wpds_geography_list_marker_date_string\">" . $marker['updatedon'] . "</div>"; }
                        $map .= "</td>";
                    }
                    if ( $settings_markers_list['listmarkers_mcreatedby'] == 'true' ) {
                        $createdby_user = get_user_by( 'id', (int)$marker['createdby'] );
                        $map .= "<td class=\"wpds_geography_list_marker_author\">" . $createdby_user->user_login . "</td>";
                    }
                    $map .= "</tr>";
                    $i++;
                    if ( $i > $settings_markers_list['listmarkers_limit'] ) { break; }
                }
                $map .= "</table>" . PHP_EOL;
            
                $map .= $afer_map;
            
                if ( $i > $settings_markers_list['listmarkers_limit'] ) {
                    $map .= "<p><i>Достигнут лимит на количество маркеров в списке - " . $settings_markers_list['listmarkers_limit'] . ".</i></p>";
                }
            }
        }
        return $map;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Формирование JS карты
     * 
     * @return string
     */
    public function get_js()
    {
        $settings_map        = get_option( 'wpds_geography_options_maps' );
        $settings_map_source = get_option( 'wpds_geography_options_map_source' );
        $settings_popup      = get_option( 'wpds_geography_options_popup' );
    
        $salt = $this->salt;
    
        $js = "";
        $js .= "<script type=\"text/javascript\">" . PHP_EOL;
        $js .= "/* //<![CDATA[ */" . PHP_EOL;
        $js .= "jQuery( document ).ready( function() {" . PHP_EOL;
    
        $js .= "var lat_" . $salt . " = \"" . $this->superposition_element[$this->type]['lat'] . "\"" . PHP_EOL;
        $js .= "var lon_" . $salt . " = \"" . $this->superposition_element[$this->type]['lon'] . "\"" . PHP_EOL;
        $js .= "var url_to_shadow_icon_" . $salt . " = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "images/default-shadow.png';" . PHP_EOL;
    
        if ( $this->type == 'marker' ) {
            $js .= "var url_to_marker_icon_" . $salt . " = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "icons/" . $this->superposition_element[$this->type]['icon'] . "';" . PHP_EOL;
        }
        if ( $this->type == 'layer' ) {
            $js .= "var url_to_folder_marker_icons_" . $salt . " = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "icons/';" . PHP_EOL;
        }
    
        $js .= "var location_" . $salt . " = new L.LatLng( lat_" . $salt . ", lon_" . $salt . " );" . PHP_EOL;
    
        $js .= "var map_" . $salt . " = new L.Map( 'map_" . $salt . "', { center: location_" . $salt . ", dragging: " . $settings_map['dragging'] . ", touchZoom: " . $settings_map['touchZoom'] . ", scrollWheelZoom: " . $settings_map['scrollWheelZoom'] . ", doubleClickZoom: " . $settings_map['doubleClickZoom'] . ", boxzoom: " . $settings_map['boxzoom'] . ", trackResize: " . $settings_map['trackResize'] . ", worldCopyJump: " . $settings_map['worldCopyJump'] . ", closePopupOnClick: " . $settings_map['closePopupOnClick'] . ", keyboard: " . $settings_map['keyboard'] . ", keyboardPanOffset: " . $settings_map['keyboardPanOffset'] . ", keyboardZoomOffset: " . $settings_map['keyboardZoomOffset'] . ", inertia: " . $settings_map['inertia'] . ", inertiaDeceleration: " . $settings_map['inertiaDeceleration'] . ", inertiaMaxSpeed: " . $settings_map['inertiaMaxSpeed'] . ", zoomControl: " . $settings_map['zoomControl'] . ( $settings_map['fullscreen_mode'] == 'true' ? ', fullscreenControl: true' : '' ) . " } );" . PHP_EOL;
        $js .= "map_" . $salt . ".attributionControl.setPrefix('<a href=\"http://wpds.ru/\" target=\"_blank\" title=\"WPDS\">WPDS</a> for <a href=\"http://diamondsteel.ru/\" target=\"_blank\" title=\"DiamondSteel-Art\">DiamondSteel-Art</a> (<a href=\"http://www.leafletjs.com\" target=\"_blank\" title=\"Leaflet Maps Marker is based on the javascript library Leaflet maintained by Vladimir Agafonkin and Cloudmade\">Leaflet</a>)');" . PHP_EOL;
    
        $js .= "var PointIcon_" . $salt . " = L.Icon.extend( { options: { shadowUrl: url_to_shadow_icon_" . $salt . ", iconSize: [32, 37], shadowSize: [41, 41], iconAnchor: [16, 36], shadowAnchor: [16, 43], popupAnchor: [0, -37] } } );" . PHP_EOL;
        
        if ( $this->backend_script == TRUE and $this->type == 'layer' ) {
            $js .= "var LayerTargetPointIcon = L.Icon.extend( {" . PHP_EOL;
            $js .= "    options: {" . PHP_EOL;
            $js .= "        iconSize: [48, 48]," . PHP_EOL;
            $js .= "        iconAnchor: [24, 24]" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "} );" . PHP_EOL;
            
            $js .= "var url_to_target_icon = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "images/target.png';" . PHP_EOL;
            $js .= "var targetpointicon = new LayerTargetPointIcon( { iconUrl: url_to_target_icon } );" . PHP_EOL;
        }
        
    
        if ( $this->type == 'marker' ) {
            $js .= "var pointicon_" . $salt . " = new PointIcon_" . $salt . "( { iconUrl: url_to_marker_icon_" . $salt . " } );" . PHP_EOL;
        }
    
        $js .= "var osm_" . $salt . " = new L.TileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' );" . PHP_EOL;
        if ( $settings_map_source['googleUse'] == 'true' ) {
            $js .= "var googleLayer_roadmap_" . $salt . " = new L.Google( \"ROADMAP\", { mmid: 'googleLayer_roadmap', detectRetina: " . $settings_map['detectRetina'] . " } );" . PHP_EOL;
            $js .= "var googleLayer_satellite_" . $salt . " = new L.Google( \"SATELLITE\", { mmid: 'googleLayer_satellite', detectRetina: " . $settings_map['detectRetina'] . " } );" . PHP_EOL;
            $js .= "var googleLayer_hybrid_" . $salt . " = new L.Google( \"HYBRID\", { mmid: 'googleLayer_hybrid', detectRetina: " . $settings_map['detectRetina'] . " } );" . PHP_EOL;
        }
        if ( $settings_map_source['yandexUse'] == 'true' ){
            $js .= "var yandex_" . $salt . " = new L.Yandex();" . PHP_EOL;
        }
        if ( $settings_map_source['dgisUse'] == 'true' ){
            $js .= "var dgis_" . $salt . " = new L.DGis();" . PHP_EOL;
        }
    
        $js .= "layersControl_" . $salt . " = new L.Control.Layers( {";
        $layers = '';
        foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ){
            if ( $val['use'] == 'true' ){
                $layers .= "'" . $val['name'] . "':" . $key . "_" . $salt . ",";
            }
        }
        $layers = rtrim( $layers, ',' );
        $js .= $layers;
        $js .= " }, {} );" . PHP_EOL;
    
        $js .= "map_" . $salt . ".addControl( layersControl_" . $salt . " );" . PHP_EOL;
    
        if ( $settings_map['scaleControl'] == 'true' ) {
            $js .= "L.control.scale( { position: '" . $settings_map['scale_control_position'] . "', maxWidth: " . $settings_map['scale_maxwidth'] . ", metric: " . $settings_map['metric'] . ", imperial: " . $settings_map['imperial'] . ", updateWhenIdle: " . $settings_map['updateWhenIdle'] . " } ).addTo( map_" . $salt . " );" . PHP_EOL;
        }
    
        if ( $settings_map['scrollWheelZoomControl'] == 'true' ) {
            $js .= "map_" . $salt . ".addControl( new ControlZoomMode() );" . PHP_EOL;
        }
    
        if ( $settings_map_source['layerscontrol_list'][$this->superposition_element[$this->type]['layerscontrol']]['use'] == 'true' ) {
            $js .= "map_" . $salt . ".addLayer( " . $this->superposition_element[$this->type]['layerscontrol'] . "_" . $salt . " );" . PHP_EOL;
        } else {
            $js .= "map_" . $salt . ".addLayer( osm_" . $salt . " );" . PHP_EOL;
        }
    
        if ( $this->type == 'layer' ) {
            if ( $settings_map['clustering_mode'] == 'true' ){
                $js .= "var clustergroup_" . $salt . " = new L.MarkerClusterGroup( { zoomToBoundsOnClick: " . $settings_map['clustering_zoomToBoundsOnClick'] . ", showCoverageOnHover: " . $settings_map['clustering_showCoverageOnHover'] . ", spiderfyOnMaxZoom: " . $settings_map['clustering_spiderfyOnMaxZoom'] . ", singleMarkerMode: " . $settings_map['clustering_singleMarkerMode'] . ", spiderfyDistanceMultiplier: " . $settings_map['clustering_spiderfyDistanceMultiplier'] . ", disableClusteringAtZoom: " . $settings_map['clustering_disableClusteringAtZoom'] . ", maxClusterRadius: " . $settings_map['clustering_maxClusterRadius'] . " } );" . PHP_EOL;
            }
        }
        
        if ( $this->backend_script == TRUE and $this->type == 'layer' ) {
            $js .= "var marker_target = new L.Marker( location_" . $salt . ", { draggable: true, icon: targetpointicon } ).addTo( map_" . $salt . " );" . PHP_EOL;
        }
    
        if ( $this->type == 'marker' ) {
            if ( $this->backend_script == TRUE ) { $draggable = "draggable: true"; } else { $draggable = "draggable: false"; }
            $js .= "marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . " = new L.Marker( location_" . $salt . ", { " . $draggable . ", icon: pointicon_" . $salt . " } ).addTo( map_" . $salt . " );" . PHP_EOL;
            
            if ( $this->backend_script == TRUE and empty( $this->superposition_element[$this->type]['name'] ) ) {
                $js .= "var popupheader_" . $salt . " = '<div style=\"font-weight: bold; border-bottom: 1px solid #f0f0e7; padding-bottom: 5px; margin-bottom: 5px;\">" . __( 'Marker name', 'wp-ds-geography' ) . " (<i> " . __( 'available after marker publication', 'wp-ds-geography' ) . "</div>';" . PHP_EOL;
            } else {
                $js .= "var popupheader_" . $salt . " = '<div style=\"font-weight: bold; border-bottom: 1px solid #f0f0e7; padding-bottom: 5px; margin-bottom: 5px;\">" . htmlspecialchars( stripslashes( $this->superposition_element[$this->type]['name'] ), ENT_QUOTES ) . "</div>';" . PHP_EOL;
            }
            
            if ( $this->backend_script == TRUE and empty( $this->superposition_element[$this->type]['popuptext'] ) ) {
                $js .= "var popuptext_" . $salt . " = \"" . __( 'Popup message text', 'wp-ds-geography' ) . "<BR />(<i> " . __( 'available after marker publication', 'wp-ds-geography' ) . "\";" . PHP_EOL;
            } else {
                $js .= "var popuptext_" . $salt . " = \"" . preg_replace( '/(\015\012)|(\015)|(\012)/','<BR />', $this->superposition_element[$this->type]['popuptext'] ) . "\";" . PHP_EOL;
            }
            $js .= "marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".bindPopup( popupheader_" . $salt . " + popuptext_" . $salt . " + '<div style=\"clear:both;\"></div>', { maxWidth: " . $settings_popup['maxWidth'] . ", minWidth: " . $settings_popup['minWidth'] . ", maxHeight: " . $settings_popup['maxHeight'] . ", autoPan: " . $settings_popup['autoPan'] . ", closeButton: " . $settings_popup['closeButton'] . ", autoPanPadding: new L.Point( " . $settings_popup['autoPanPadding_x'] . ", " . $settings_popup['autoPanPadding_y'] . " ), offset: new L.Point( " . $settings_popup['offset_x'] . ", " . $settings_popup['offset_y'] . " ) } );" . PHP_EOL;
    
            $js .= "map_" . $salt . ".setView( location_" . $salt . ", " . $this->superposition_element[$this->type]['zoom'] . " );" . PHP_EOL;
    
            if ( $this->superposition_element[$this->type]['openpopup'] == '1' ) {
                $js .= "marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".openPopup();" . PHP_EOL;
            }
        }
    
        if ( $this->type == 'layer' ) {
            $js .= "var arrayOfLatLngs_" . $salt . " = [];" . PHP_EOL;
    
            foreach ( $this->marker_list as $marker ) {
                $js .= "var icon_for_marker_" . $marker['id'] . "_" . $salt . " = new PointIcon_" . $salt . "( { iconUrl: url_to_folder_marker_icons_" . $salt . " + '" . $marker['icon'] . "' } );" . PHP_EOL;
                $js .= "marker_" . $marker['id'] . "_" . $salt . " = new L.Marker( new L.LatLng( " . $marker['lat'] . ", " . $marker['lon'] . " ), { icon: icon_for_marker_" . $marker['id'] . "_" . $salt . " } );" . PHP_EOL;
                $js .= "var popuptext_" . $marker['id'] . "_" . $salt . " = '<div style=\"font-weight: bold; border-bottom: 1px solid #f0f0e7; padding-bottom: 5px; margin-bottom: 5px;\">" . htmlspecialchars( stripslashes( $marker['markername'] ), ENT_QUOTES ) . "</div>" . ( preg_replace( '/(\015\012)|(\015)|(\012)/','<BR />', $marker['popuptext'] ) ) . "';" . PHP_EOL;
                $js .= "marker_" . $marker['id'] ."_" . $salt . ".bindPopup( popuptext_" . $marker['id'] . "_" . $salt . " + '<div style=\"clear:both;\"></div>', { maxWidth: " . $settings_popup['maxWidth'] . ", minWidth: " . $settings_popup['minWidth'] . ", maxHeight: " . $settings_popup['maxHeight'] .", autoPan: " . $settings_popup['autoPan'] . ", closeButton: " . $settings_popup['closeButton'] . ", autoPanPadding: new L.Point( " . $settings_popup['autoPanPadding_x'] . ", " . $settings_popup['autoPanPadding_y'] . " ), offset: new L.Point( " . $settings_popup['offset_x'] . ", " . $settings_popup['offset_y'] . " ) } );" . PHP_EOL;
                $js .= "arrayOfLatLngs_" . $salt . ".push([" . $marker['lat'] . ", " . $marker['lon'] . "]);" . PHP_EOL;
            }
    
            $group = '';
            $clustergroup = '';
            foreach ( $this->marker_list as $marker ) {
                $group .= "marker_" . $marker['id'] . "_" . $salt . ",";
                if ( $settings_map['clustering_mode'] == 'true' ){ $clustergroup .= "clustergroup_" . $salt . ".addLayer( marker_" . $marker['id'] . "_" . $salt . " );" . PHP_EOL; }
            }
            $group = rtrim( $group, ',' );
    
            $js .= "var group_" . $salt . " = L.layerGroup( [" . $group . "] );" . PHP_EOL;
            $js .= $clustergroup;
    
            $js .= "if ( arrayOfLatLngs_" . $salt . ".length == 0 ) { arrayOfLatLngs_" . $salt . ".push([lat_" . $salt . ", lon_" . $salt . "]); }" . PHP_EOL;
            $js .= "var bounds_" . $salt . " = new L.LatLngBounds(arrayOfLatLngs_" . $salt . ");" . PHP_EOL;
    
            if ( $settings_map['clustering_mode'] == 'true' and $this->superposition_element[$this->type]['clustering'] == 1 ){
                $js .= "map_" . $salt . ".addLayer( clustergroup_" . $salt . " );" . PHP_EOL;
            } else {
                $js .= "map_" . $salt . ".addLayer( group_" . $salt . " );" . PHP_EOL;
            }
    
            if( $this->superposition_element[$this->type]['fitbounds'] == 1 ) {
                $js .= "map_" . $salt . ".fitBounds( bounds_" . $salt . " ).invalidateSize();" . PHP_EOL;
            } else {
                $js .= "map_" . $salt . ".setView( location_" . $salt . ", " . $this->superposition_element[$this->type]['zoom'] . " );" . PHP_EOL;
            }
        }
        
        //-------------------------//
        
        if ( $this->backend_script == TRUE ) {
            
            $js .= "var mapElement = jQuery( '#map_" . $salt . "' );" . PHP_EOL;
            $js .= "var mapWidth   = jQuery( '#mapwidth' );" . PHP_EOL;
            $js .= "var mapHeight  = jQuery( '#mapheight' );" . PHP_EOL;
            $js .= "var lat        = jQuery( '#lat' );" . PHP_EOL;
            $js .= "var lon        = jQuery( '#lon' );" . PHP_EOL;
            $js .= "var zoom       = jQuery( '#zoom' );" . PHP_EOL;
            
            $js .= "map_" . $salt . ".on( 'moveend', function( e ) { document.getElementById( 'zoom' ).value = map_" . $salt . ".getZoom(); } );" . PHP_EOL;

            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
                if ( $val['use'] == 'true' ) { $js .= "var layerscontrol_" . $key . " = jQuery('#layerscontrol-" . $key . "');" . PHP_EOL; }
            }
            $js .= "map_" . $salt . ".on( 'baselayerchange', function( e ) {" . PHP_EOL;
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ){
                if ( $val['use'] == 'true' ) {
                    $js .= "    if ( map_" . $salt . ".hasLayer( " . $key . "_" . $salt . " ) ) {\n";
                    $js .= "        if ( ! layerscontrol_" . $key . ".prop( \"checked\" ) ) {". PHP_EOL;
                    foreach ( $settings_map_source['layerscontrol_list'] as $key2 => $val2 ) {
                        if ( $val2['use'] == 'true' ) {
                            if ( $key == $key2 ) {
                                $js .= "            layerscontrol_" . $key2 . ".attr( 'checked', true );". PHP_EOL;
                            } else {
                                $js .= "            layerscontrol_" . $key2 . ".removeAttr( 'checked' );". PHP_EOL;
                            }
                        }
                    }
                    $js .= "            return;" . PHP_EOL;
                    $js .= "        }" . PHP_EOL;
                    $js .= "    }" . PHP_EOL;
                }
            }
            $js .= "} );" . PHP_EOL;

            $js .= "jQuery( 'input:radio[name=layerscontrol]' ).click( function() {" . PHP_EOL;
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
                if ( $val['use'] == 'true' ) {
                    $js .= "    if ( map_" . $salt . ".hasLayer( " . $key . "_" . $salt . " ) ) { map_" . $salt . ".removeLayer( " . $key . "_" . $salt . " ); }" . PHP_EOL;
                }
            }
            $js .= "    var layerscontrolval = jQuery( 'input:radio[name=layerscontrol]:checked' ).val();" . PHP_EOL;
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
                if ( $val['use'] == 'true' ) {
                    $js .= "    if ( layerscontrolval == '" . $key . "' ) { map_" . $salt . ".addLayer( " . $key . "_" . $salt . " ); }" . PHP_EOL;
                }
            }
            $js .= "} );" . PHP_EOL;

            $js .= "zoom.on( 'blur', function( e ) {" . PHP_EOL;
            $js .= "    if( isNaN( zoom.val() ) ) {" . PHP_EOL;
            $js .= "        alert( 'Недействительный формат! Пожалуйста, используйте только числа!' );" . PHP_EOL;
            $js .= "    } else {" . PHP_EOL;
            $js .= "        map_" . $salt . ".setZoom( zoom.val() );" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "} );" . PHP_EOL;

            $js .= "jQuery( 'input:text[name=lat]' ).blur( function( e ) {" . PHP_EOL;
            $js .= "    if( isNaN( lat.val() ) ) {" . PHP_EOL;
            $js .= "        alert( 'Недействительный формат! Пожалуйста, используйте только числа и точки (не запятые) в качестве разделителя дробных чисел!' );" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "} );" . PHP_EOL;
            
            $js .= "jQuery( 'input:text[name=lon]' ).blur( function( e ) {" . PHP_EOL;
            $js .= "    if( isNaN( lon.val() ) ) {" . PHP_EOL;
            $js .= "        alert( 'Недействительный формат! Пожалуйста, используйте только числа и точки (не запятые) в качестве разделителя дробных чисел!' );" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "} );" . PHP_EOL;

            $js .= "mapWidth.blur( function() {" . PHP_EOL;
            $js .= "    if( ! isNaN( mapWidth.val() ) ) {" . PHP_EOL;
            $js .= "        mapElement.css( \"width\", mapWidth.val() + jQuery( 'input:radio[name=mapwidthunit]:checked' ).val() );" . PHP_EOL;
            $js .= "        map_" . $salt . ".invalidateSize();" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "} );" . PHP_EOL;
            
            $js .= "jQuery( 'input:radio[name=mapwidthunit]' ).click( function() {" . PHP_EOL;
            $js .= "    mapElement.css( \"width\", mapWidth.val() + jQuery( 'input:radio[name=mapwidthunit]:checked').val() );" . PHP_EOL;
            $js .= "    map_" . $salt . ".invalidateSize();" . PHP_EOL;
            $js .= "});" . PHP_EOL;
            
            $js .= "mapHeight.blur( function() {" . PHP_EOL;
            $js .= "    if( ! isNaN( mapHeight.val() ) ) {" . PHP_EOL;
            $js .= "        mapElement.css( \"height\", mapHeight.val() + \"px\" );" . PHP_EOL;
            $js .= "        map_" . $salt . ".invalidateSize();" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "});" . PHP_EOL;
            
            if ( $this->type == 'marker' ) {
                $js .= "map_" . $salt . ".on( 'click', function( e ) {" . PHP_EOL;
                $js .= "    map_" . $salt . ".setView( e.latlng, map_" . $salt . ".getZoom() );" . PHP_EOL;
                $js .= "    document.getElementById( 'lat' ).value = e.latlng.lat.toFixed( 6 );" . PHP_EOL;
                $js .= "    document.getElementById( 'lon' ).value = e.latlng.lng.toFixed( 6 );" . PHP_EOL;
                $js .= "    marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".setLatLng( e.latlng );" . PHP_EOL;
                $js .= "});" . PHP_EOL;
                
                $js .= "marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".on( 'dragend', function( ) {" . PHP_EOL;
                $js .= "    var latlng = marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".getLatLng();" . PHP_EOL;
                $js .= "    document.getElementById( 'lat' ).value = latlng.lat.toFixed( 6 );" . PHP_EOL;
                $js .= "    document.getElementById( 'lon' ).value = latlng.lng.toFixed( 6 );" . PHP_EOL;
                $js .= "} );" . PHP_EOL;
                
                $js .= "jQuery('input:checkbox[name=openpopup]').click(function() {" . PHP_EOL;
                $js .= "    if(jQuery('input:checkbox[name=openpopup]').is(':checked')) {" . PHP_EOL;
                $js .= "        marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".openPopup();" . PHP_EOL;
                $js .= "    } else {" . PHP_EOL;
                $js .= "        marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".closePopup();" . PHP_EOL;
                $js .= "    }" . PHP_EOL;
                $js .= "});" . PHP_EOL;
                
                $js .= "jQuery( 'input:text[name=lat],input:text[name=lon]' ).blur( function( e ) {" . PHP_EOL;
                $js .= "    var markerLocation = new L.LatLng( lat.val(), lon.val() );" . PHP_EOL;
                $js .= "    marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".setLatLng( markerLocation );" . PHP_EOL;
                $js .= "    map_" . $salt . ".setView( markerLocation, map_" . $salt . ".getZoom() );" . PHP_EOL;
                $js .= "} );" . PHP_EOL;
                
                $js .= "jQuery( 'input:radio[name=icon]' ).click( function() {" . PHP_EOL;
                $js .= "    var url_to_default_marker_icon = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "icons/0-default.png';" . PHP_EOL;
                $js .= "    var url_to_folder_custom_marker_icon = '" . WPDS_GEOGRAPHY_PLUGIN_URL . "icons/';" . PHP_EOL;
                $js .= "    var filename = jQuery( 'input:radio[name=icon]:checked').val();" . PHP_EOL;
                $js .= "    var url_to_custom_marker_icon;" . PHP_EOL;
                $js .= "    if ( filename.length == 0 ) {" . PHP_EOL;
                $js .= "        url_to_custom_marker_icon = url_to_default_marker_icon;" . PHP_EOL;
                $js .= "    } else {" . PHP_EOL;
                $js .= "        url_to_custom_marker_icon = url_to_folder_custom_marker_icon + filename;" . PHP_EOL;
                $js .= "    }" . PHP_EOL;
                $js .= "    var customicon = new PointIcon_" . $salt . "( { iconUrl: url_to_custom_marker_icon } );" . PHP_EOL;
                $js .= "    marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".setIcon( customicon );" . PHP_EOL;
                $js .= "});" . PHP_EOL;
                
            }
            
            if ( $this->type == 'layer' ) {
                $js .= "marker_target.on( 'dragend', function( ) {" . PHP_EOL;
                $js .= "    var latlng = marker_target.getLatLng();" . PHP_EOL;
                $js .= "    document.getElementById( 'lat' ).value = latlng.lat.toFixed( 6 );" . PHP_EOL;
                $js .= "    document.getElementById( 'lon' ).value = latlng.lng.toFixed( 6 );" . PHP_EOL;
                $js .= "} );" . PHP_EOL;
                
                $js .= "map_" . $salt . ".on( 'click', function( e ) {" . PHP_EOL;
                $js .= "    map_" . $salt . ".setView( e.latlng, map_" . $salt . ".getZoom() );" . PHP_EOL;
                $js .= "    document.getElementById( 'lat' ).value = e.latlng.lat.toFixed( 6 );" . PHP_EOL;
                $js .= "    document.getElementById( 'lon' ).value = e.latlng.lng.toFixed( 6 );" . PHP_EOL;
                $js .= "    marker_target.setLatLng( e.latlng );" . PHP_EOL;
                $js .= "});" . PHP_EOL;
                
                if ( $settings_map['clustering_mode'] == 'true' ) {
                    $js .= "jQuery('input:checkbox[name=clustering]').click(function() {" . PHP_EOL;
                    $js .= "    if(jQuery('input:checkbox[name=clustering]').is(':checked')) {" . PHP_EOL;
                    $js .= "        map_" . $salt . ".removeLayer( group_" . $salt . " );" . PHP_EOL;
                    $js .= "        map_" . $salt . ".addLayer( clustergroup_" . $salt . " );" . PHP_EOL;
                    $js .= "        map_" . $salt . ".invalidateSize();" . PHP_EOL;
                    $js .= "    } else {" . PHP_EOL;
                    $js .= "        map_" . $salt . ".removeLayer( clustergroup_" . $salt . " );" . PHP_EOL;
                    $js .= "        map_" . $salt . ".addLayer( group_" . $salt . " );" . PHP_EOL;
                    $js .= "        map_" . $salt . ".invalidateSize();" . PHP_EOL;
                    $js .= "    }" . PHP_EOL;
                    $js .= "});" . PHP_EOL;
                }
                
                $js .= "jQuery('input:checkbox[name=fitbounds]').click(function() {" . PHP_EOL;
                $js .= "    if(jQuery('input:checkbox[name=fitbounds]').is(':checked')) {" . PHP_EOL;
                $js .= "        map_" . $salt . ".fitBounds(bounds_" . $salt . ").invalidateSize();" . PHP_EOL;
                $js .= "    }" . PHP_EOL;
                $js .= "});" . PHP_EOL;
                
                $js .= "jQuery( 'input:text[name=lat],input:text[name=lon]' ).blur( function( e ) {" . PHP_EOL;
                $js .= "    var markerLocation = new L.LatLng( lat.val(), lon.val() );" . PHP_EOL;
                $js .= "    marker_target.setLatLng( markerLocation );" . PHP_EOL;
                $js .= "    map_" . $salt . ".setView( markerLocation, map_" . $salt . ".getZoom() );" . PHP_EOL;
                $js .= "} );" . PHP_EOL;
            }
            
            
            if ( $settings_map_source['googleUse'] != 'true' ){ wp_enqueue_script( 'leaflet-google-api', 'http://maps.google.com/maps/api/js?libraries=places&amp;sensor=true', '', '' ); }
            $js .= "gLoader = function() {" . PHP_EOL;
            $js .= "    function initAutocomplete() {" . PHP_EOL;
            $js .= "        var input = document.getElementById('address');" . PHP_EOL;
            $js .= "        var autocomplete = new google.maps.places.Autocomplete(input);" . PHP_EOL;
            $js .= "        input.onfocus = function(){ };" . PHP_EOL;
            $js .= "        google.maps.event.addListener(autocomplete, 'place_changed', function() {" . PHP_EOL;
            $js .= "            var place = autocomplete.getPlace();" . PHP_EOL;
            $js .= "            var markerLocation = new L.LatLng(place.geometry.location.lat(), place.geometry.location.lng());" . PHP_EOL;
            if ( $this->type == 'marker' ) {
            $js .= "            marker_" . $this->superposition_element[$this->type]['id'] . "_" . $salt . ".setLatLng(markerLocation);" . PHP_EOL;
            }
            if ( $this->type == 'layer' ) {
                $js .= "            marker_target.setLatLng(markerLocation);" . PHP_EOL;
            }
            $js .= "            map_" . $salt . ".setView(markerLocation, map_" . $salt . ".getZoom());" . PHP_EOL;
            $js .= "            document.getElementById('lat').value = place.geometry.location.lat().toFixed(6);" . PHP_EOL;
            $js .= "            document.getElementById('lon').value = place.geometry.location.lng().toFixed(6);" . PHP_EOL;
            $js .= "        });" . PHP_EOL;
            $js .= "        var input = document.getElementById('address');" . PHP_EOL;
            $js .= "        google.maps.event.addDomListener(input, 'keydown', " . PHP_EOL;
            $js .= "        function(e) {" . PHP_EOL;
            $js .= "            if (e.keyCode == 13) {" . PHP_EOL;
            $js .= "                if (e.preventDefault) {" . PHP_EOL;
            $js .= "                    e.preventDefault();" . PHP_EOL;
            $js .= "                } else { //info:  Since the google event handler framework does not handle early IE versions, we have to do it by our self. :-(" . PHP_EOL;
            $js .= "                    e.cancelBubble = true;" . PHP_EOL;
            $js .= "                    e.returnValue = false;" . PHP_EOL;
            $js .= "                }" . PHP_EOL;
            $js .= "            }" . PHP_EOL;
            $js .= "        });" . PHP_EOL;
            $js .= "    }" . PHP_EOL;
            $js .= "    return { autocomplete:initAutocomplete }" . PHP_EOL;
            $js .= "}();" . PHP_EOL;
            $js .= "gLoader.autocomplete();" . PHP_EOL;
            
            
        }
        //-------------------------//
    
        $js .= "});" . PHP_EOL;
        $js .= "/* //]]> */" . PHP_EOL;
        $js .= "</script>" . PHP_EOL;
    
        return $js;
    }
}

?>