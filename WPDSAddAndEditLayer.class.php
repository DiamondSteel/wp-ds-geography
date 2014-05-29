<?php

/**
 * 
 * @author DiamondSteel
 *
 */
class WPDSAddAndEditLayer
{
    private $permissions_settings;
    
    private $id;
    private $superposition_element;
    private $form_action;
    private $default_settings;
    
    private $WPDSGenerateMapJS;
    private $salt;
    
    private $relationships;
    
    public  $marker_list;

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        $this->default_settings     = get_option( 'wpds_geography_options_layers' );
        
        $this->marker_list = array();
        
        /*
         * Если нет $_GET['id'] то значит мы хотим создать новый слой
         */
        if ( ! isset( $_GET['id'] ) or empty( $_GET['id'] ) or ! is_numeric( $_GET[ 'id' ] ) ) {
            $this->form_action = 'add';
            $this->initialize_variables( 'default' );
        }
        
        /*
         * Если пришел $_POST[ 'publish' ] - значит мы сохраняем новый слой
         */
        if ( isset( $_POST[ 'publish' ] ) ) {
            /* валидируем и сохраняем все пришедщие данные потом редиректим на карточку слоя с get[id]=id где откроется карточка на редактирование слоя */
            $this->publish_layer();
        }
        
        /*
         * Если пришел $_POST[ 'save' ] - значит мы сохраняем имеющийся слой
         */
        if ( isset( $_POST[ 'save' ] ) ) {
            /* валидируем и сохраняем все пришедщие данные потом редиректим на карточку слоя с get[id]=id где откроется карточка на редактирование слоя */
            $this->save_layer();
        }
            
        /*
         * Если пришел $_GET['id'] ) - значит мы хотим изменить имеющийся слой
         */
        if ( isset( $_GET['id'] ) and is_numeric( $_GET[ 'id' ] ) ) {
            $this->form_action = 'edit';
            $this->id = $_GET[ 'id' ];
            $this->initialize_variables();
        }
        
        require_once 'WPDSGenerateMapJS.class.php';
        $this->salt = uniqid();
        $this->WPDSGenerateMapJS = new WPDSGenerateMapJS( $this->salt, $this->superposition_element, 'layer', $this->marker_list );
        $this->WPDSGenerateMapJS->backend_script = TRUE;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Небольшая валидация данных перед записью в БД
     * 
     * @param string $field - данные пришедшие от пользователя 
     * @param string $type - тип данных
     * @return Ambigous <boolean, string>
     */
    private function validation_before_publication( $field, $type )
    {
        if ( $type == 'string' ) {
            $result = mysql_real_escape_string( $field );
        }
        if ( $type == 'int' ) {
            if ( is_numeric( $field ) ) { $result = true; } else { $result = false; }
        }
        if ( $type == 'layerscontrol' ) {
            $settings_map_source = get_option( 'wpds_geography_options_map_source' );
            if ( array_key_exists( $field, $settings_map_source['layerscontrol_list'] ) ) { $result = true; } else { $result = false; }
        }
        if ( $type == 'boolean' ) {
            if ( $field == 0 or $field == 1 ) { $result = true; } else { $result = false; }
        }
        if ( $type == 'mapwidthunit' ) {
            if ( $field == 'px' or $field == '%' ) { $result = true; } else { $result = false; }
        }
        return $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Сохранение слоя в БД
     */
    private function save_layer()
    {
        if ( check_admin_referer( '{381d0df8-6178-4e75-9a7f-66a5ed9372dd}', 'wp_nonce_save_layer_field' ) ) {

            $allowed = true;        
            $name = $this->validation_before_publication( $_POST['name'], 'string' );
        
            if ( $this->validation_before_publication( $_POST['layer_id'],  'int' ) ) { $layer_id  = $_POST['layer_id'];  } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lat'],       'int' ) ) { $lat       = $_POST['lat'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lon'],       'int' ) ) { $lon       = $_POST['lon'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['zoom'],      'int' ) ) { $zoom      = $_POST['zoom'];      } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapwidth'],  'int' ) ) { $mapwidth  = $_POST['mapwidth'];  } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapheight'], 'int' ) ) { $mapheight = $_POST['mapheight']; } else { $allowed = false; }
    
            if ( $this->validation_before_publication( $_POST['layerscontrol'], 'layerscontrol') ) { $layerscontrol = $_POST['layerscontrol']; } else { $allowed = false; }
    
            if ( $this->validation_before_publication( $_POST['mapwidthunit'], 'mapwidthunit') ) { $mapwidthunit = $_POST['mapwidthunit']; } else { $allowed = false; }
            
            $fitbounds   = isset( $_POST['fitbounds']   ) ? '1' : '0';
            $listmarkers = isset( $_POST['listmarkers'] ) ? '1' : '0';
            $clustering  = isset( $_POST['clustering']  ) ? '1' : '0';
            
            if ( ! empty( $_POST['mlayer'] ) ) {
                foreach ( $_POST['mlayer'] as $val ) {
                    if ( $this->validation_before_publication( $val, 'int') ) { $mlayer[] = $val; } else { $allowed = false; }
                }
            } else {
                $mlayer = array();
            }
            
            if ( $allowed == true ) {
                $current_user = wp_get_current_user();
    
                global $wpdb;
    
                $insert = "UPDATE " . WPDS_GEOGRAPHY_TABLE_LAYERS . " SET name          = '{$name}',
                                                                          layerscontrol = '{$layerscontrol}',
                                                                          zoom          = '{$zoom}',
                                                                          mapwidth      = '{$mapwidth}',
                                                                          mapwidthunit  = '{$mapwidthunit}',
                                                                          mapheight     = '{$mapheight}',
                                                                          fitbounds     = '{$fitbounds}',
                                                                          coordinates   = PointFromText( 'POINT( {$lat} {$lon} )' ),
                                                                          updatedby     = '{$current_user->ID}',
                                                                          updatedon     = NOW(),
                                                                          listmarkers   = '{$listmarkers}',
                                                                          clustering    = '{$clustering}'
                                                                      WHERE id = '{$layer_id}' ";
            
                $results = $wpdb->query( $insert );
            
                if ( $results ) {
                    $delete_relationships = "DELETE FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".layer_id = " . $layer_id . ";";
                    $wpdb->query( $delete_relationships );
                    $wpdb->query( "OPTIMIZE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS );
                    if ( ! empty( $mlayer ) ) {
                        $values = '';
                        foreach ( $mlayer as $val ){ $values .= "( '" . $layer_id . "', " . $val . " ),"; }
                        $values = rtrim( $values, ',' );
                        $insert_relationships = "INSERT INTO " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " ( layer_id, related_layer_id ) VALUES " . $values . ";";
                        $wpdb->query( $insert_relationships );
                    }
                    wp_redirect( admin_url( 'admin.php?page=wpds_geography_the_layer&id=' .$layer_id ) );
                    exit;
                }
            } else {
                wp_nonce_ays();
            }
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Публикация нового слоя
     */
    private function publish_layer()
    {
        if ( check_admin_referer( '{381d0df8-6178-4e75-9a7f-66a5ed9372dd}', 'wp_nonce_save_layer_field' ) ) {
            
            $allowed = true;
            $name = $this->validation_before_publication( $_POST['name'], 'string' );
            
            if ( $this->validation_before_publication( $_POST['lat'],       'int' ) ) { $lat       = $_POST['lat'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lon'],       'int' ) ) { $lon       = $_POST['lon'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['zoom'],      'int' ) ) { $zoom      = $_POST['zoom'];      } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapwidth'],  'int' ) ) { $mapwidth  = $_POST['mapwidth'];  } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapheight'], 'int' ) ) { $mapheight = $_POST['mapheight']; } else { $allowed = false; }
            
            if ( $this->validation_before_publication( $_POST['layerscontrol'], 'layerscontrol') ) { $layerscontrol = $_POST['layerscontrol']; } else { $allowed = false; }
            
            if ( $this->validation_before_publication( $_POST['mapwidthunit'], 'mapwidthunit') ) { $mapwidthunit = $_POST['mapwidthunit']; } else { $allowed = false; }
            
            $fitbounds   = isset( $_POST['fitbounds']   ) ? '1' : '0';
            $listmarkers = isset( $_POST['listmarkers'] ) ? '1' : '0';
            $clustering  = isset( $_POST['clustering']  ) ? '1' : '0';
            
            if ( ! empty( $_POST['mlayer'] ) ) {
                foreach ( $_POST['mlayer'] as $val ) {
                    if ( $this->validation_before_publication( $val, 'int') ) { $mlayer[] = $val; } else { $allowed = false; }
                }
            } else {
                $mlayer = array();
            }
            
            if ( $allowed == true ) {
                $current_user = wp_get_current_user();
            
                global $wpdb;
            
                $insert = "INSERT INTO " . WPDS_GEOGRAPHY_TABLE_LAYERS . " ( name,
                                                                             layerscontrol,
                                                                             zoom,
                                                                             mapwidth,
                                                                             mapwidthunit,
                                                                             mapheight,
                                                                             fitbounds,
                                                                             coordinates,
                                                                             createdby,
                                                                             createdon,
                                                                             updatedby,
                                                                             updatedon,
                                                                             listmarkers,
                                                                             clustering )
                           VALUES ( '{$name}',
                                    '{$layerscontrol}',
                                    '{$zoom}',
                                    '{$mapwidth}',
                                    '{$mapwidthunit}',
                                    '{$mapheight}',
                                    '{$fitbounds}',
                                    PointFromText( 'POINT( {$lat} {$lon} )' ),
                                    '{$current_user->ID}',
                                    NOW(),
                                    '{$current_user->ID}',
                                    NOW(),
                                    '{$listmarkers}',
                                    '{$clustering}' );";
                $results = $wpdb->query( $insert );
                $layer_id = $wpdb->insert_id;
            
                if ( $results ) {
                    if ( ! empty( $mlayer ) ) {
                        $values = '';
                        foreach ( $mlayer as $val ){ $values .= "( '" . $layer_id . "', " . $val . " ),"; }
                        $values = rtrim( $values, ',' );
                        $insert_relationships = "INSERT INTO " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " ( layer_id, related_layer_id ) VALUES " . $values . ";";
                        $wpdb->query( $insert_relationships );
                    }
                    wp_redirect( admin_url( 'admin.php?page=wpds_geography_the_layer&id=' .$layer_id ) );
                    exit;
                }
            } else {
                wp_nonce_ays();
            }
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Получение всех параметров слоя
     * 
     * @param string $init - default - для нового слоя или пусто для имеющегося слоя
     */
    private function initialize_variables( $init = '' )
    {
        if ( $init == 'default' ) {
            $this->superposition_element['layer']['id']            = 0;
            $this->superposition_element['layer']['name']          = '';
            $this->superposition_element['layer']['layerscontrol'] = $this->default_settings['layerscontrol'];
            $this->superposition_element['layer']['zoom']          = $this->default_settings['zoom'];
            $this->superposition_element['layer']['mapwidth']      = $this->default_settings['mapwidth'];
            $this->superposition_element['layer']['mapwidthunit']  = $this->default_settings['mapwidthunit'];
            $this->superposition_element['layer']['mapheight']     = $this->default_settings['mapheight'];
            $this->superposition_element['layer']['fitbounds']     = $this->default_settings['fitbounds'] == 'true' ? '1' : '0';
            $this->superposition_element['layer']['lat']           = $this->default_settings['lat'];
            $this->superposition_element['layer']['lon']           = $this->default_settings['lon'];
            $this->superposition_element['layer']['createdby']     = '';
            $this->superposition_element['layer']['createdon']     = '';
            $this->superposition_element['layer']['updatedby']     = '';
            $this->superposition_element['layer']['updatedon']     = '';
            $this->superposition_element['layer']['listmarkers']   = $this->default_settings['listmarkers'] == 'true' ? '1' : '0';
            $this->superposition_element['layer']['clustering']    = $this->default_settings['clustering'] == 'true' ? '1' : '0';
        
            return;
        }
        
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

        if ( empty( $select ) ) { wp_nonce_ays(); }
        
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
        
        $current_user = wp_get_current_user();
        
        if ( $this->superposition_element['layer']['createdby'] != $current_user->ID ) {
            if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ){
                wp_nonce_ays();
            }
        }
        
        $this->relationships = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".related_layer_id
                                                             FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . "
                                                            WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".layer_id = " . $this->id . "
                                                         ORDER BY " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".related_layer_id ASC", ARRAY_A );
        
        if ( empty( $this->relationships ) ) {
            $multilayer = '';
        } else {
            foreach ( $this->relationships as $val ) { $new_select_relationships[] = $val['related_layer_id']; }
            $related_layer_id = implode( ',', $new_select_relationships );
            $multilayer = ',' . $related_layer_id;
        }
        
        if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ) {
            $permission = 'AND ' . WPDS_GEOGRAPHY_TABLE_MARKERS . '.createdby = ' . $current_user->ID;
        } else {
            $permission = '';
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
                                                                   {$permission}
                                                       ORDER BY " . WPDS_GEOGRAPHY_TABLE_MARKERS . ".name ASC", ARRAY_A );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_header()
    {
        if ( $this->form_action == 'add' ) {
            $result = __( 'Add Layer', 'wp-ds-geography' );
        }
        if ( $this->form_action == 'edit' ) {
            $result = __( 'Edit Layer', 'wp-ds-geography' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=wpds_geography_the_layer' ) ) . '" class="add-new-h2">' . __( 'Add new layer', 'wp-ds-geography' ) . '</a>';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_map()
    {
        $result = $this->WPDSGenerateMapJS->get_map();
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_js()
    {
    $result = $this->WPDSGenerateMapJS->get_js();
    echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_date_from_layer()
    {
        if ( $this->form_action == 'add' ) {
            $result = '';
            $result .= '<div class="misc-pub-section">';
            $result .= '<p>' . __( 'This layer hasn’t been published yet.', 'wp-ds-geography' ) . '</p>';
            $result .= '</div>';
        }
        
        if ( $this->form_action == 'edit' ) {
            $result = '';
            $result .= '<div class="misc-pub-section" style="text-align: center;">';
            $result .= '<span class="wpds_geography_shortcode">[wpds_geography layer="' . $this->superposition_element['layer']['id'] . '"]</span>';
            $result .= '</div>';
            
            $createdby_user = get_user_by( 'id', (int)$this->superposition_element['layer']['createdby'] );
            
            $result .= '<div class="misc-pub-section curtime misc-pub-curtime">';
            $result .= '<span id="timestamp">' . __( 'Created:', 'wp-ds-geography' ) . ' ' . $createdby_user->user_login . '<BR />' . $this->superposition_element['layer']['createdon'] . '</span>';
            $result .= '</div>';
            
            $updatedby_user = get_user_by( 'id', (int)$this->superposition_element['layer']['updatedby'] );
            
            $result .= '<div class="misc-pub-section curtime misc-pub-curtime">';
            $result .= '<span id="timestamp">' . __( 'Modified:', 'wp-ds-geography' ) . ' ' . $updatedby_user->user_login . '<BR />' . $this->superposition_element['layer']['updatedon'] . '</span>';
            $result .= '</div>';
        }
        
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_lon()
    {
        if ( $this->form_action == 'add' ) {
            $result = $this->default_settings['lon'];
        }
        if ( $this->form_action == 'edit' ) {
            $result = $this->superposition_element['layer']['lon'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_lat()
    {
        if ( $this->form_action == 'add' ) {
            $result = $this->default_settings['lat'];
        }
        if ( $this->form_action == 'edit' ) {
            $result = $this->superposition_element['layer']['lat'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_name()
    {
        if ( $this->form_action == 'add' ) {
            $result = '';
        }
        if ( $this->form_action == 'edit' ) {
            $result =  htmlspecialchars( stripslashes( $this->superposition_element['layer']['name'] ), ENT_QUOTES );
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_zoom()
    {
        if ( $this->form_action == 'add' ) {
            $result = $this->default_settings['zoom'];
        }
        if ( $this->form_action == 'edit' ) {
            $result = $this->superposition_element['layer']['zoom'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapheight()
    {
        if ( $this->form_action == 'add' ) {
            $result = $this->default_settings['mapheight'];
        }
        if ( $this->form_action == 'edit' ) {
            $result = $this->superposition_element['layer']['mapheight'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapwidthunit( $unit )
    {
        if ( $this->form_action == 'add' ) {
            if ( $unit == $this->default_settings['mapwidthunit'] ) {
                $result = 'checked="checked"';
            } else {
                $result = '';
            }
        }
        if ( $this->form_action == 'edit' ) {
            $result = '';
            if ( $unit == 'px' and $unit == $this->superposition_element['layer']['mapwidthunit'] ) {
                $result = 'checked="checked"';
            }
            if ( $unit == '%' and $unit == $this->superposition_element['layer']['mapwidthunit'] ) {
                $result = 'checked="checked"';
            }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapwidth()
    {
        if ( $this->form_action == 'add' ) {
            $result = $this->default_settings['mapwidth'];
        }
        if ( $this->form_action == 'edit' ) {
            $result = $this->superposition_element['layer']['mapwidth'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_delete_link()
    {
        if ( $this->form_action == 'add' ) {
            $result = '';
        }
        if ( $this->form_action == 'edit' ) {
            $result = '';
            $url = wp_nonce_url( '?page=wpds_geography_all_layers&id=' . $this->superposition_element['layer']['id'] . '&action=delete', '{E5EE1D1D-0C3A-407F-A442-8D65058DD154}' );
            $result .= '<a class="submitdelete deletion" href="' . $url . '">'. __( 'Delete' ) .'</a>';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_submit_button()
    {
        if ( $this->form_action == 'add' ) {
            $result = '';
            $result .= '<input name="publish" type="submit" class="button button-primary button-large" id="publish" value="'. __( 'Publish' ) .'">';
        }
        if ( $this->form_action == 'edit' ) {
            $result = '';
            $result .= '<input name="save" type="submit" class="button button-primary button-large" id="save" value="'. __( 'Update' ) .'">';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_layers_control()
    {
        $settings_map_source = get_option( 'wpds_geography_options_map_source' );
        
        $result = '';
        
        if ( $this->form_action == 'add' ) {
            if ( $settings_map_source['layerscontrol_list'][$this->default_settings['layerscontrol']]['use'] != 'true' ) { $this->default_settings['layerscontrol'] = 'osm'; }
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
                if ( $key == $this->default_settings['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
                if ( $val['use'] == 'true' ) {
                    $result .= '<label for="layerscontrol-' . $key . '">';
                    $result .= '<input type="radio" name="layerscontrol" value="' . $key . '" id="layerscontrol-' . $key . '" ' . $checked . ' />';
                    $result .= $val['name'] . '</label><BR />';
                }
            }
        }
        if ( $this->form_action == 'edit' ) {
            if ( $settings_map_source['layerscontrol_list'][$this->superposition_element['layer']['layerscontrol']]['use'] != 'true' ) { $this->superposition_element['layer']['layerscontrol'] = 'osm'; }
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
                if ( $key == $this->superposition_element['layer']['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
                if ( $val['use'] == 'true' ) {
                    $result .= '<label for="layerscontrol-' . $key . '">';
                    $result .= '<input type="radio" name="layerscontrol" value="' . $key . '" id="layerscontrol-' . $key . '" ' . $checked . ' />';
                    $result .= $val['name'] . '</label><BR />';
                }
            }
        }
        
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_layers_list()
    {
        $current_user = wp_get_current_user();
        if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ) {
            $permission_select = 'WHERE ' . WPDS_GEOGRAPHY_TABLE_LAYERS . '.createdby = ' . $current_user->ID;
        } else {
            $permission_select = '';
        }

        global $wpdb;
        $select_layers = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id,
                                                            " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name
                                                       FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . "
                                                               {$permission_select}
                                                   ORDER BY " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name ASC", ARRAY_A );
        $result = '';
        
        if ( $this->form_action == 'add' ) {
            if ( ! empty( $select_layers ) ) {
                foreach ( $select_layers as $row ) {
                    $result .= '<label for="mlayer-' . $row['id'] . '"><input type="checkbox" name="mlayer[]" value="' . $row['id'] . '" id="mlayer-' . $row['id'] . '" />' . $row['name'] . '</label><BR />';
                }
            } else {
                $result = '<p>' . __( 'No available layers', 'wp-ds-geography' ) . '</p>';
            }
        }
        if ( $this->form_action == 'edit' ) {

            if ( empty( $select_layers ) ) {
                $result = '<p>' . __( 'No available layers', 'wp-ds-geography' ) . '</p>';
                echo $result;
                return;
            }
            
            if ( empty( $this->relationships ) ) {
                foreach ( $select_layers as $row ) {
                    if ( $this->superposition_element['layer']['id'] != $row['id'] ) {
                        $result .= '<label for="mlayer-' . $row['id'] . '"><input type="checkbox" name="mlayer[]" value="' . $row['id'] . '" id="mlayer-' . $row['id'] . '" />' . $row['name'] . '</label><BR />';
                    }
                }
            } else {
                foreach( $this->relationships as $val ){ $new_select_relationships[] = $val['related_layer_id']; }
                foreach ( $select_layers as $row ) {
                    if ( $this->superposition_element['layer']['id'] != $row['id'] ) {
                        $checked = in_array( $row['id'], $new_select_relationships ) ? 'checked="checked" ' : '';
                        $result .= '<label for="mlayer-' . $row['id'] . '"><input type="checkbox" name="mlayer[]" value="' . $row['id'] . '" id="mlayer-' . $row['id'] . '" ' . $checked . '/>' . $row['name'] . '</label><BR />';
                    }
                }
            }
        }
        if ( empty( $result ) ) {
            $result = '<p>' . __( 'No available layers', 'wp-ds-geography' ) . '</p>';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_fitbounds()
    {
        $result = '';
        if ($this->form_action == 'add'){
            if ( $this->default_settings['fitbounds'] == 'true'  ) { $result = 'checked="checked"'; }
            if ( $this->default_settings['fitbounds'] == 'false' ) { $result = ''; }
        }
        if ($this->form_action == 'edit'){
            if ( $this->superposition_element['layer']['fitbounds'] == 1 ){ $result = 'checked="checked"'; }
            if ( $this->superposition_element['layer']['fitbounds'] == 0 ){ $result = ''; }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_clustering()
    {
        $settings_maps = get_option( 'wpds_geography_options_maps' );
        
        $result = '';
        
        if ( $settings_maps['clustering_mode'] == 'false' ) {
            $result = 'disabled="disabled"';
            echo $result;
            return;
        }

        if ($this->form_action == 'add'){
            if ( $this->default_settings['clustering'] == 'true'  ) { $result = 'checked="checked"'; }
            if ( $this->default_settings['clustering'] == 'false' ) { $result = ''; }
        }
        if ($this->form_action == 'edit'){
            if ( $this->superposition_element['layer']['clustering'] == 1 ) { $result = 'checked="checked"'; }
            if ( $this->superposition_element['layer']['clustering'] == 0 ) { $result = ''; }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_listmarkers()
    {
        $result = '';
        if ($this->form_action == 'add'){
            if ( $this->default_settings['listmarkers'] == 'true'  ) { $result = 'checked="checked"'; }
            if ( $this->default_settings['listmarkers'] == 'false' ) { $result = ''; }
        }
        if ($this->form_action == 'edit'){
            if ( $this->superposition_element['layer']['listmarkers'] == 1 ){ $result = 'checked="checked"'; }
            if ( $this->superposition_element['layer']['listmarkers'] == 0 ){ $result = ''; }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_markers_at_layer()
    {
        $result = '';
        
        if ($this->form_action == 'add') {
            
        }
        if ($this->form_action == 'edit') {
            $result .= '<h2>';
            $result .=  __( 'Markers are shown on this map', 'wp-ds-geography' );
            $result .= ' <a href="' . esc_url( admin_url( 'admin.php?page=wpds_geography_the_marker&attached_to_layer=' . $this->superposition_element['layer']['id'] ) ) . '" class="add-new-h2">' . __( 'Add new marker', 'wp-ds-geography' ) . '</a>';
            $result .= '</h2>';
            echo $result;
            require_once 'WPDSListTableForListMarkersAtLayer.class.php';
            $WPDSListTableForListMarkersAtLayer = new WPDSListTableForListMarkersAtLayer( $this->superposition_element['layer']['id'], $this->marker_list );
            $WPDSListTableForListMarkersAtLayer->prepare_items();
            $WPDSListTableForListMarkersAtLayer->display();
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_hidden_input()
    {
        $result = '';
        if ( $this->form_action == 'add' ) {
            $result .= '<input type="hidden" id="layer_id" name="layer_id" value="" />';
        }
        if ( $this->form_action == 'edit' ) {
            $result .= '<input type="hidden" id="layer_id" name="layer_id" value="' . $this->superposition_element['layer']['id'] . '" />';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Вывод страницы добавления и редактирования маркера
     */
    public function display_template()
    {
        include 'template-the-layer.php';
    }

} //class

?>