<?php

/**
 * 
 * @author DiamondSteel
 *
 */
class WPDSAddAndEditMarker
{
    private $permissions_settings; // Проверить почему protected?
    
    private $id;
    private $superposition_element;
    private $attached_to_layer;
    private $form_action;
    private $default_settings;
    
    private $WPDSGenerateMapJS;
    private $salt;

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        $this->default_settings     = get_option( 'wpds_geography_options_markers' );
        
        /*
         * Если нет $_GET['id'] то значит мы хотим создать новый маркер
         */
        if ( ! isset( $_GET['id'] ) or empty( $_GET['id'] ) or ! is_numeric( $_GET[ 'id' ] ) ) {
            $this->form_action = 'add';
            if ( isset( $_GET['attached_to_layer'] ) and is_numeric( $_GET['attached_to_layer'] ) ) {
                $this->attached_to_layer = $_GET['attached_to_layer'];
            }
            $this->initialize_variables( 'default' );
        }
        
        /*
         * Если пришел $_POST[ 'publish' ] - значит мы сохраняем новый маркер
         */
        if ( isset( $_POST[ 'publish' ] ) ) {
            /* валидируем и сохраняем все пришедщие данные потом редиректим на карточку маркерв с get[id]=id где откроется карточка на редактирование маркера */
            $this->publish_marker();
        }
        
        /*
         * Если пришел $_POST[ 'save' ] - значит мы сохраняем имеющийся маркер
         */
        if ( isset( $_POST[ 'save' ] ) ) {
            /* валидируем и сохраняем все пришедщие данные потом редиректим на карточку маркерв с get[id]=id где откроется карточка на редактирование маркера */
            $this->save_marker();
        }
            
        /*
         * Если пришел $_GET['id'] ) - значит мы хотим изменить имеющийся маркер
         */
        if ( isset( $_GET['id'] ) and is_numeric( $_GET[ 'id' ] ) ) {
            $this->form_action = 'edit';
            $this->id = $_GET[ 'id' ];
            $this->initialize_variables();
        }
        
        require_once 'WPDSGenerateMapJS.class.php';
        $this->salt = uniqid();
        $this->WPDSGenerateMapJS = new WPDSGenerateMapJS( $this->salt, $this->superposition_element, 'marker' );
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
     * Сохранение маркера в БД
     */
    private function save_marker()
    {
        if ( check_admin_referer( '{B7C83ACB-A647-4C2B-B147-DE1314AE27B1}', 'wp_nonce_save_marker_field' ) ) {

            $allowed = true;        
            $name      = $this->validation_before_publication( $_POST['name'], 'string' );
            $icon      = $this->validation_before_publication( $_POST['icon'], 'string' );
            $popuptext = $this->validation_before_publication( $_POST['popuptext'], 'string' );

            if ( ! isset( $_POST['layer'] ) or empty( $_POST['layer'] ) ) { $_POST['layer'] = 0; }
            if ( $this->validation_before_publication( $_POST['layer'],     'int' ) ) { $layer     = $_POST['layer'];     } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['marker_id'], 'int' ) ) { $marker_id = $_POST['marker_id']; } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lat'],       'int' ) ) { $lat       = $_POST['lat'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lon'],       'int' ) ) { $lon       = $_POST['lon'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['zoom'],      'int' ) ) { $zoom      = $_POST['zoom'];      } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapwidth'],  'int' ) ) { $mapwidth  = $_POST['mapwidth'];  } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapheight'], 'int' ) ) { $mapheight = $_POST['mapheight']; } else { $allowed = false; }
    
            if ( $this->validation_before_publication( $_POST['layerscontrol'], 'layerscontrol') ) { $layerscontrol = $_POST['layerscontrol']; } else { $allowed = false; }
    
            $openpopup = isset( $_POST['openpopup'] ) ? '1' : '0';
    
            if ( $this->validation_before_publication( $_POST['mapwidthunit'], 'mapwidthunit') ) { $mapwidthunit = $_POST['mapwidthunit']; } else { $allowed = false; }
    
            if ( $allowed == true ) {
                $current_user = wp_get_current_user();
    
                global $wpdb;
    
                $insert = "UPDATE " . WPDS_GEOGRAPHY_TABLE_MARKERS . " SET name          = '{$name}',
                                                                           layerscontrol = '{$layerscontrol}',
                                                                           layer         = '{$layer}',
                                                                           coordinates   = PointFromText( 'POINT( {$lat} {$lon} )' ),
                                                                           icon          = '{$icon}',
                                                                           popuptext     = '{$popuptext}',
                                                                           zoom          = '{$zoom}',
                                                                           openpopup     = '{$openpopup}',
                                                                           mapwidth      = '{$mapwidth}',
                                                                           mapwidthunit  = '{$mapwidthunit}',
                                                                           mapheight     = '{$mapheight}',
                                                                           updatedby     = '{$current_user->ID}',
                                                                           updatedon     = NOW()
                           WHERE id = '{$marker_id}';";
            
                $results = $wpdb->query( $insert );
            
                if ( $results ) {
                    wp_redirect( admin_url( 'admin.php?page=wpds_geography_the_marker&id=' .$marker_id ) );
                    exit;
                }
            } else {
                wp_nonce_ays();
            }
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Публикация нового маркера
     */
    private function publish_marker()
    {
        if ( check_admin_referer( '{B7C83ACB-A647-4C2B-B147-DE1314AE27B1}', 'wp_nonce_save_marker_field' ) ) {

            $allowed = true;
        
            $name       = $this->validation_before_publication( $_POST['name'], 'string' );
            $icon       = $this->validation_before_publication( $_POST['icon'], 'string' );
            $popuptext  = $this->validation_before_publication( $_POST['popuptext'], 'string' );
        
            if ( ! isset( $_POST['layer'] ) or empty( $_POST['layer'] ) ) { $_POST['layer'] = 0; }
            if ( $this->validation_before_publication( $_POST['layer'],     'int' ) ) { $layer     = $_POST['layer'];     } else { $allowed = false; } 
            if ( $this->validation_before_publication( $_POST['lat'],       'int' ) ) { $lat       = $_POST['lat'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['lon'],       'int' ) ) { $lon       = $_POST['lon'];       } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['zoom'],      'int' ) ) { $zoom      = $_POST['zoom'];      } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapwidth'],  'int' ) ) { $mapwidth  = $_POST['mapwidth'];  } else { $allowed = false; }
            if ( $this->validation_before_publication( $_POST['mapheight'], 'int' ) ) { $mapheight = $_POST['mapheight']; } else { $allowed = false; }
        
            if ( $this->validation_before_publication( $_POST['layerscontrol'], 'layerscontrol') ) { $layerscontrol = $_POST['layerscontrol']; } else { $allowed = false; }
        
            $openpopup = isset( $_POST['openpopup'] ) ? '1' : '0';
        
            if ( $this->validation_before_publication( $_POST['mapwidthunit'], 'mapwidthunit') ) { $mapwidthunit = $_POST['mapwidthunit']; } else { $allowed = false; }
        
            if ( $allowed == true ) {
                $current_user = wp_get_current_user();
            
                global $wpdb;
            
                $insert = "INSERT INTO " . WPDS_GEOGRAPHY_TABLE_MARKERS . " ( name,
                                                                              layerscontrol,
                                                                              layer,
                                                                              coordinates,
                                                                              icon,
                                                                              popuptext,
                                                                              zoom,
                                                                              openpopup,
                                                                              mapwidth,
                                                                              mapwidthunit,
                                                                              mapheight,
                                                                              createdby,
                                                                              createdon,
                                                                              updatedby,
                                                                              updatedon )
                           VALUES ( '{$name}',
                                    '{$layerscontrol}',
                                    '{$layer}',
                                    PointFromText( 'POINT( {$lat} {$lon} )' ),
                                    '{$icon}',
                                    '{$popuptext}',
                                    '{$zoom}',
                                    '{$openpopup}',
                                    '{$mapwidth}',
                                    '{$mapwidthunit}',
                                    '{$mapheight}',
                                    '{$current_user->ID}',
                                    NOW(),
                                    '{$current_user->ID}',
                                    NOW() );";
                $results = $wpdb->query( $insert );
                $marker_id = $wpdb->insert_id;
            
                if ( $results ) {
                    wp_redirect( admin_url( 'admin.php?page=wpds_geography_the_marker&id=' .$marker_id ) );
                    exit;
                }
            } else {
                wp_nonce_ays();
            }
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Получение всех параметров маркера
     *
     * @param string $init - default - для нового маркера или пусто для имеющегося маркера
     */
    private function initialize_variables( $init = '' )
    {
        
        if ( $init == 'default' ) {
            $this->superposition_element['marker']['id']            = 0;
            $this->superposition_element['marker']['name']          = '';
            $this->superposition_element['marker']['layerscontrol'] = $this->default_settings['layerscontrol'];
            $this->superposition_element['marker']['layer']         = '';
            $this->superposition_element['marker']['lat']           = $this->default_settings['lat'];
            $this->superposition_element['marker']['lon']           = $this->default_settings['lon'];
            $this->superposition_element['marker']['icon']          = $this->default_settings['icon'];
            $this->superposition_element['marker']['popuptext']     = '';
            $this->superposition_element['marker']['zoom']          = $this->default_settings['zoom'];
            $this->superposition_element['marker']['openpopup']     = $this->default_settings['openpopup'] == 'true' ? '1' : '0';
            $this->superposition_element['marker']['mapwidth']      = $this->default_settings['mapwidth'];
            $this->superposition_element['marker']['mapwidthunit']  = $this->default_settings['mapwidthunit'];
            $this->superposition_element['marker']['mapheight']     = $this->default_settings['mapheight'];
            $this->superposition_element['marker']['createdby']     = '';
            $this->superposition_element['marker']['createdon']     = '';
            $this->superposition_element['marker']['updatedby']     = '';
            $this->superposition_element['marker']['updatedon']     = '';
            
            return;
        }
        
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
        
        if ( empty( $select ) ) { wp_nonce_ays(); }
        
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
        
        $current_user = wp_get_current_user();
        if ( $this->superposition_element['marker']['createdby'] != $current_user->ID ) {
            if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ) {
                wp_nonce_ays();
            }
        }
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_header()
    {
        if ($this->form_action == 'add') {
            $result = __( 'Add Marker', 'wp-ds-geography' );
        }
        if ($this->form_action == 'edit') {
            $result = __( 'Edit Marker', 'wp-ds-geography' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=wpds_geography_the_marker' ) ) . '" class="add-new-h2">' . __( 'Add new marker', 'wp-ds-geography' ) . '</a>';
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
    public function form_date_from_marker()
    {
        if ($this->form_action == 'add') {
            $result = '';
            $result .= '<div class="misc-pub-section">';
            $result .= '<p>' . __( 'This marker hasn’t been published yet.', 'wp-ds-geography' ) . '</p>';
            $result .= '</div>';
        }
        
        if ($this->form_action == 'edit') {
            $result = '';
            $result .= '<div class="misc-pub-section" style="text-align: center;">';
            $result .= '<span class="wpds_geography_shortcode">[wpds_geography marker="' . $this->superposition_element['marker']['id'] . '"]</span>';
            $result .= '</div>';
            
            $createdby_user = get_user_by( 'id', (int)$this->superposition_element['marker']['createdby'] );
            
            $result .= '<div class="misc-pub-section curtime misc-pub-curtime">';
            $result .= '<span id="timestamp">' . __( 'Created:', 'wp-ds-geography' ) . ' ' . $createdby_user->user_login . '<BR />' . $this->superposition_element['marker']['createdon'] . '</span>';
            $result .= '</div>';
            
            $updatedby_user = get_user_by( 'id', (int)$this->superposition_element['marker']['updatedby'] );
            
            $result .= '<div class="misc-pub-section curtime misc-pub-curtime">';
            $result .= '<span id="timestamp">' . __( 'Modified:', 'wp-ds-geography' ) . ' ' . $updatedby_user->user_login . '<BR />' . $this->superposition_element['marker']['updatedon'] . '</span>';
            $result .= '</div>';
        }
        
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_lon()
    {
        if ($this->form_action == 'add') {
            $result = $this->default_settings['lon'];
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['lon'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_lat()
    {
        if ($this->form_action == 'add') {
            $result = $this->default_settings['lat'];
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['lat'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_name()
    {
        if ($this->form_action == 'add') {
            $result = '';
        }
        if ($this->form_action == 'edit') {
            $result =  htmlspecialchars( stripslashes( $this->superposition_element['marker']['name'] ), ENT_QUOTES );
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_zoom()
    {
        if ($this->form_action == 'add') {
            $result = $this->default_settings['zoom'];
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['zoom'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapheight()
    {
        if ($this->form_action == 'add') {
            $result = $this->default_settings['mapheight'];
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['mapheight'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapwidthunit($unit)
    {
        if ($this->form_action == 'add') {
            if ( $unit == $this->default_settings['mapwidthunit'] ) {
                $result = 'checked="checked"';
            } else {
                $result = '';
            }
        }
        if ($this->form_action == 'edit') {
            $result = '';
            if ( $unit == 'px' and $unit == $this->superposition_element['marker']['mapwidthunit'] ) {
                $result = 'checked="checked"';
            }
            if ( $unit == '%' and $unit == $this->superposition_element['marker']['mapwidthunit'] ) {
                $result = 'checked="checked"';
            }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_mapwidth()
    {
        if ($this->form_action == 'add') {
            $result = $this->default_settings['mapwidth'];
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['mapwidth'];
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_delete_link()
    {
        if ($this->form_action == 'add') {
            $result = '';
        }
        if ($this->form_action == 'edit') {
            $result = '';
            $url = wp_nonce_url( '?page=wpds_geography&id=' . $this->superposition_element['marker']['id'] . '&action=delete', '{E5EE1D1D-0C3A-407F-A442-8D65058DD154}' );
            $result .= '<a class="submitdelete deletion" href="' . $url . '">'. __( 'Delete' ) .'</a>';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_openpopup()
    {
        $result = '';
        if ($this->form_action == 'add') {
            if ( $this->default_settings['openpopup'] == 'true'  ) { $result = 'checked="checked"'; }
            if ( $this->default_settings['openpopup'] == 'false' ) { $result = ''; }
        }
        if ($this->form_action == 'edit') {
            if ( $this->superposition_element['marker']['openpopup'] == 1 ){ $result = 'checked="checked"'; }
            if ( $this->superposition_element['marker']['openpopup'] == 0 ){ $result = ''; }
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_submit_button()
    {
        if ($this->form_action == 'add') {
            $result = '';
            $result .= '<input name="publish" type="submit" class="button button-primary button-large" id="publish" value="'. __( 'Publish' ) .'">';
        }
        if ($this->form_action == 'edit') {
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
        
        if ($this->form_action == 'add') {
            if ( $settings_map_source['layerscontrol_list'][$this->default_settings['layerscontrol']]['use'] != 'true' ) { $this->default_settings['layerscontrol'] = 'osm'; }
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ){
                if ( $key == $this->default_settings['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
                if ( $val['use'] == 'true' ) {
                    $result .= '<label for="layerscontrol-' . $key . '">';
                    $result .= '<input type="radio" name="layerscontrol" value="' . $key . '" id="layerscontrol-' . $key . '" ' . $checked . ' />';
                    $result .= $val['name'] . '</label><BR />';
                }
            }
        }
        if ($this->form_action == 'edit') {
            if ( $settings_map_source['layerscontrol_list'][$this->superposition_element['marker']['layerscontrol']]['use'] != 'true' ) { $this->superposition_element['marker']['layerscontrol'] = 'osm'; }
            foreach ( $settings_map_source['layerscontrol_list'] as $key => $val ) {
            if ( $key == $this->superposition_element['marker']['layerscontrol'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
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
    public function form_popuptext()
    {
        if ($this->form_action == 'add') {
            $result = '';
        }
        if ($this->form_action == 'edit') {
            $result = $this->superposition_element['marker']['popuptext'];
        }
        return $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_layers_select()
    {
        global $wpdb;
        $layers = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id, " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . " ORDER BY " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name ASC;", ARRAY_A );
        
        $result = '';
        
        if ( ! empty( $layers ) ) {
            if ($this->form_action == 'add') {
                $result .= '<select name="layer">';
                $result .= '<option value="">' . __( 'Layer is not selected', 'wp-ds-geography' ) . '</option>';
                        foreach ( $layers as $layer ) {
                            $selected = $this->attached_to_layer == $layer['id'] ? $selected = 'selected' : $selected = '';
                            $result .= '<option value="' . $layer['id'] . '" ' . $selected . '>' . $layer['name'] . '</option>';
                        }
                $result .= '</select>';
            }
            if ($this->form_action == 'edit') {
                $result .= '<select name="layer">';
                $result .= '<option value="">' . __( 'Layer is not selected', 'wp-ds-geography' ) . '</option>';
                        foreach ( $layers as $layer ) {
                            $selected = $this->superposition_element['marker']['layer'] == $layer['id'] ? $selected = 'selected' : $selected = '';
                            $result .= '<option value="' . $layer['id'] . '" ' . $selected . '>' . $layer['name'] . '</option>';
                        }
                $result .= '</select>';
            }
        } else {
            $result .= '<select name="layer">';
            $result .= '<option value="" disabled>' . __( 'No available layers', 'wp-ds-geography' ) . '</option>';
            $result .= '</select>';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_icon_list()
    {
        $dir = WPDS_GEOGRAPHY_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'icons';
        $icondir = opendir( $dir );
        while ( false !== ( $file = readdir( $icondir ) ) ) {
            if ( $file != "." AND $file != ".." ) {
                if ( !is_dir( $dir . $file ) ) { $files[] = $file; }
            }
        }
        closedir( $icondir );
        
        natsort( $files );
        
        $default_checked = '';
        $custom_checked  = '';
        if ( $this->form_action == 'add' ) { $this->superposition_element['marker']['icon'] = $this->default_settings['icon']; }
        if ( $this->form_action == 'edit' ) {
            if ( empty( $this->superposition_element['marker']['icon'] ) ) { $this->superposition_element['marker']['icon'] = $this->default_settings['icon']; }
        }

        $result = '';
        foreach ($files as $key => $val) {
            if ( $val == $this->superposition_element['marker']['icon'] ) { $checked = 'checked="checked"'; } else { $checked = ''; }
            $p = strrpos( $val, '.' );
            if ( $p > 0 ) { $id = substr( $val, 0, $p ); } else { $id = $val; }
            $result .= '<div class="markericon">';
            $result .= '<label for="' . $id . '"><img width="32" height="37" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'icons/' . $val . '" /></label><BR />';
            $result .= '<input type="radio" name="icon" value="' . $val . '" id="' . $id . '" ' . $checked . '>';
            $result .= '</div>';
        }
        $result .= '<div class="clear"></div>';
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function form_hidden_input()
    {
        $result = '';
        if ($this->form_action == 'add') {
            $result .= '<input type="hidden" id="marker_id" name="marker_id" value="" />';
        }
        if ($this->form_action == 'edit') {
            $result .= '<input type="hidden" id="marker_id" name="marker_id" value="' . $this->superposition_element['marker']['id'] . '" />';
        }
        echo $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Вывод страницы добавления и редактирования маркера
     */
    public function display_template()
    {
        include 'template-the-marker.php';
    }

} //class

?>