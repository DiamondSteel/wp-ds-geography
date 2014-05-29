<?php

/**
 * 
 * @author DiamondSteel
 *
 */
class WPDSGeographyFrontend
{
    private $id;
    private $type;
    private $superposition_element;
    private $marker_list;
    private $js;

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     * 
     * @param string $type - marker | layer
     * @param int $id
     */
    public function __construct( $type, $id )
    {
        $this->id   = $id;
        $this->type = $type;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    public function get_map()
    {
        global $WPDS_Geography_Plugin;
        $WPDS_Geography_Plugin->wpds_load_js_and_css( $this->type );
        
        wp_enqueue_style( 'wpds_geography_frontend', WPDS_GEOGRAPHY_PLUGIN_URL . 'css/wpds_geography_frontend.css' );
        
        require_once 'WPDSGenerateMapJS.class.php';
        
        if ( $this->type == 'marker' ) {
            if ( $this->marker_initialize_variables() ) {
                $salt = uniqid();
                $WPDSGenerateMapJS = new WPDSGenerateMapJS( $salt, $this->superposition_element, $this->type );
                $map = $WPDSGenerateMapJS->get_map();
                $this->js = $WPDSGenerateMapJS->get_js();
                add_action( 'wp_footer', array( &$this, 'print_footer_js' ), 100 );
            } else {
                $map = '';
            }
        }
        
        if ( $this->type == 'layer' ) {
            if ( $this->layer_initialize_variables() ) {
                $salt = uniqid();
                $WPDSGenerateMapJS = new WPDSGenerateMapJS( $salt, $this->superposition_element, $this->type, $this->marker_list );
                $map = $WPDSGenerateMapJS->get_map();
                $this->js = $WPDSGenerateMapJS->get_js();
                add_action( 'wp_footer', array( &$this, 'print_footer_js' ), 100 );
            } else {
                $map = '';
            }
        }
        
        return $map;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function print_footer_js()
    {
        echo $this->js;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function layer_initialize_variables()
    {
        $settings_markers_list = get_option( 'wpds_geography_options_markers_list' );
        
        global $wpdb;
        
        $select = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".layerscontrol,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".zoom,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".mapwidth,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".mapwidthunit,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".mapheight,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".fitbounds,
                                                  X( " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".coordinates ) AS lat,
                                                  Y( " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".coordinates ) AS lon,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdby,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdon,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".updatedby,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".updatedon,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".listmarkers,
                                                     " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".clustering
                                                FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . "
                                               WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id = {$this->id}", ARRAY_A );
        
        if ( empty( $select ) ) { return false; }
        
        $this->superposition_element['layer']['id']            = $select[0]['id'];
        $this->superposition_element['layer']['name']          = $select[0]['name'];
        $this->superposition_element['layer']['layerscontrol'] = $select[0]['layerscontrol'];
        $this->superposition_element['layer']['zoom']          = $select[0]['zoom'];
        $this->superposition_element['layer']['mapwidth']      = $select[0]['mapwidth'];
        $this->superposition_element['layer']['mapwidthunit']  = $select[0]['mapwidthunit'];
        $this->superposition_element['layer']['mapheight']     = $select[0]['mapheight'];
        $this->superposition_element['layer']['fitbounds']     = $select[0]['fitbounds'];
        $this->superposition_element['layer']['lat']           = $select[0]['lat'];
        $this->superposition_element['layer']['lon']           = $select[0]['lon'];
        $this->superposition_element['layer']['createdby']     = $select[0]['createdby'];
        $this->superposition_element['layer']['createdon']     = $select[0]['createdon'];
        $this->superposition_element['layer']['updatedby']     = $select[0]['updatedby'];
        $this->superposition_element['layer']['updatedon']     = $select[0]['updatedon'];
        $this->superposition_element['layer']['listmarkers']   = $select[0]['listmarkers'];
        $this->superposition_element['layer']['clustering']    = $select[0]['clustering'];
        
        $relationships = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".related_layer_id
                                                       FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . "
                                                      WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".layer_id = " . $this->id . "
                                                   ORDER BY " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".related_layer_id ASC", ARRAY_A );
        
        if ( empty( $relationships ) ) {
            $multilayer = '';
        } else {
            foreach ( $relationships as $val ) { $new_select_relationships[] = $val['related_layer_id']; }
            $related_layer_id = implode( ',', $new_select_relationships );
            $multilayer = ',' . $related_layer_id;
        }
        
        $this->marker_list = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".id,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".name AS markername,
                                                                " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id AS layer_id,
                                                                " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name AS layer,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".icon,
                                                             X( " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".coordinates ) AS lat,
                                                             Y( " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".coordinates ) AS lon,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".popuptext,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".createdon,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".createdby,
                                                                " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".updatedon
                                                           FROM " . WPDS_GEOGRAPHY_TABLE_MARKERS . "
                                                      LEFT JOIN " . WPDS_GEOGRAPHY_TABLE_LAYERS . " ON " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".layer = " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id
                                                          WHERE " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".layer IN ( {$this->id}{$multilayer} )
                                                       ORDER BY " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".{$settings_markers_list['listmarkers_sort']} {$settings_markers_list['listmarkers_order']}", ARRAY_A );
        
        return true;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function marker_initialize_variables()
    {
        global $wpdb;
        
        $select = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".id,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".name,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".layerscontrol,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".layer,
                                                  X( " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".coordinates ) AS lat,
                                                  Y( " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".coordinates ) AS lon,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".icon,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".popuptext,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".zoom,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".openpopup,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".mapwidth,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".mapwidthunit,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".mapheight,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".createdby,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".createdon,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".updatedby,
                                                     " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".updatedon
                                                FROM " . WPDS_GEOGRAPHY_TABLE_MARKERS . "
                                               WHERE " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".id = {$this->id};", ARRAY_A );
        
        if ( empty( $select ) ) { return false; }
        
        $this->superposition_element['marker']['id']            = $select[0]['id'];
        $this->superposition_element['marker']['name']          = $select[0]['name'];
        $this->superposition_element['marker']['layerscontrol'] = $select[0]['layerscontrol'];
        $this->superposition_element['marker']['layer']         = $select[0]['layer'];
        $this->superposition_element['marker']['lat']           = $select[0]['lat'];
        $this->superposition_element['marker']['lon']           = $select[0]['lon'];
        $this->superposition_element['marker']['icon']          = $select[0]['icon'];
        $this->superposition_element['marker']['popuptext']     = $select[0]['popuptext'];
        $this->superposition_element['marker']['zoom']          = $select[0]['zoom'];
        $this->superposition_element['marker']['openpopup']     = $select[0]['openpopup'];
        $this->superposition_element['marker']['mapwidth']      = $select[0]['mapwidth'];
        $this->superposition_element['marker']['mapwidthunit']  = $select[0]['mapwidthunit'];
        $this->superposition_element['marker']['mapheight']     = $select[0]['mapheight'];
        $this->superposition_element['marker']['createdby']     = $select[0]['createdby'];
        $this->superposition_element['marker']['createdon']     = $select[0]['createdon'];
        $this->superposition_element['marker']['updatedby']     = $select[0]['updatedby'];
        $this->superposition_element['marker']['updatedon']     = $select[0]['updatedon'];
        
        return true;
    }
}

?>