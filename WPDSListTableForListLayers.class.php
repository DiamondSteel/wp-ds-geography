<?php

require_once 'WPDSListTable.class.php';

/**
 * 
 * @author DiamondSteel
 *
 */
class WPDSListTableForListLayers extends WPDSListTable
{
    protected $permissions_settings;
    
    public $all_layers_relationships;
    public $all_layers;
    public $search_string;
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        
        $doaction = $this->current_action();
        if ( ! empty( $doaction ) ) { $this->action_processing( $doaction ); }
        
        parent::__construct( array(
                                'singular' => __( 'layer', 'wp-ds-geography' ),  //singular name of the listed records
                                'plural'   => __( 'layers', 'wp-ds-geography' ), //plural name of the listed records
                                'ajax'     => false                              //does this table support ajax?
                             )
                );
        
        $this->all_layers_relationships = $this->get_layers_relationships_from_db();
        $this->all_layers = $this->get_layers_from_db();

    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    private function action_processing( $action )
    {
        if ( $action == 'delete'){
            if ( isset( $_GET['id'] ) and is_numeric( $_GET['id'] ) and wp_verify_nonce( $_GET['_wpnonce'], '{E5EE1D1D-0C3A-407F-A442-8D65058DD154}' ) ){
                global $wpdb;
                
                $owner = $wpdb->get_var( $wpdb->prepare( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdby FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . " WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id = %d", $_GET['id'] ) );
                $current_user = wp_get_current_user();
                if ( $owner != $current_user->ID ) {
                    if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ){
                        wp_nonce_ays();
                    }
                }
                
                $wpdb->delete( WPDS_GEOGRAPHY_TABLE_LAYERS, array( 'id' => $_GET['id'] ), array( '%d' ) );
                $wpdb->query( "OPTIMIZE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS );
                
                $wpdb->query( "DELETE FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . ".layer_id = " . $_GET['id'] . ";" );
                $wpdb->query( "OPTIMIZE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS );
                
                wp_redirect( admin_url( 'admin.php?page=wpds_geography_all_layers' ) );
                exit;
            } elseif ( isset( $_POST['id'] ) and wp_verify_nonce( $_POST['wp_nonce_delete_layers_field'], '{9A9E534C-4EF7-4793-AAEC-42A84D0D5187}' ) ) {
                global $wpdb;
                
                $checked_layers_prepared = implode( ',', $_POST['id'] );
                $checked_layers = preg_replace( '/[a-z|A-Z| |\=]/', '', $checked_layers_prepared );
                
                if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ){
                    $array_owner_id = $wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdby FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . " WHERE id IN (" . $checked_layers . ")", ARRAY_A  );
                    foreach( $array_owner_id as $val ){ $new_array_owner_id[$val['createdby']] = $val['createdby']; }
                    if ( sizeof($new_array_owner_id) > 1 ) {
                        wp_nonce_ays();
                    } elseif( sizeof($new_array_owner_id) == 1 ) {
                        $id = array_shift($new_array_owner_id);
                        $current_user = wp_get_current_user();
                        if ( $id != $current_user->ID ) { wp_nonce_ays(); }
                    }
                }

                $wpdb->query( "DELETE FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . " WHERE id IN (" . $checked_layers . ")" );
                $wpdb->query( "OPTIMIZE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS );
                
                $related_layer_id = implode( ',', $_POST['id'] );
                $wpdb->query( "DELETE FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS . " WHERE layer_id IN ( " . $related_layer_id . " );" );
                $wpdb->query( "OPTIMIZE TABLE " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS );
                
            } else {
                wp_redirect( admin_url( 'admin.php?page=wpds_geography_all_layers' ) );
                exit;
            }
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    private function get_layers_relationships_from_db()
    {
        global $wpdb;
        $select = (array)$wpdb->get_results( "SELECT * FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS_RELATIONSHIPS, ARRAY_A );
        if ( ! empty( $select ) ) {
            foreach ( $select as $row ){ $result[$row['layer_id']][] = $row['related_layer_id']; }
        } else {
            $result = array();
        }
        
        return $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    private function get_layers_from_db()
    {
        global $wpdb;
        
        $sort = '';
        if ( ! empty( $_GET['orderby'] ) ) {
            if( $_GET['orderby'] == 'name'      ) { $sort = 'ORDER BY ' . WPDS_GEOGRAPHY_TABLE_LAYERS . '.name'; }
            if( $_GET['orderby'] == 'layer'     ) { $sort = 'ORDER BY layer'; }
            if( $_GET['orderby'] == 'createdon' ) { $sort = 'ORDER BY ' . WPDS_GEOGRAPHY_TABLE_LAYERS . '.createdon'; }
        }
        $method = '';
        if ( ! empty( $_GET['order'] ) ) {
            if( $_GET['order'] == 'asc'  ) { $method = 'ASC'; }
            if( $_GET['order'] == 'desc' ) { $method = 'DESC'; }
        }
        
        $current_user = wp_get_current_user();
        if ( ! current_user_can( $this->permissions_settings['capabilities_not_own'] ) ) {
            $permission = WPDS_GEOGRAPHY_TABLE_LAYERS . '.createdby = ' . $current_user->ID;
            $permission_select = 'WHERE ' . $permission;
            $permission_search_select = 'AND ' . $permission;
        } else {
            $permission_select = '';
            $permission_search_select = '';
        }
        
        if ( ! empty( $_REQUEST['s'] ) ) {
            $this->search_string = $_REQUEST['s'];
            $searchtext = '%' . $_REQUEST['s'] . '%';
            $result = (array)$wpdb->get_results( $wpdb->prepare( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id,
                                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name,
                                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdon,
                                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdby
                                                                    FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . "
                                                                   WHERE " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name LIKE %s
                                                                            {$permission_search_select}
                                                                            {$sort} {$method}", $searchtext ), ARRAY_A );
        } else {
            if ($sort == '' and $method == ''){
                $sort = 'ORDER BY ' . WPDS_GEOGRAPHY_TABLE_LAYERS . '.createdon';
                $method = 'DESC';
            }
            $result = (array)$wpdb->get_results( "SELECT " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".id,
                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".name,
                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdon,
                                                         " . WPDS_GEOGRAPHY_TABLE_LAYERS . ".createdby
                                                    FROM " . WPDS_GEOGRAPHY_TABLE_LAYERS . "
                                                            {$permission_select}
                                                            {$sort} {$method}", ARRAY_A );
        }
        return $result;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::no_items()
     */
    public function no_items()
    {
        _e( "It's empty. Try to add a layer.", 'wp-ds-geography' );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::get_sortable_columns()
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array( 'name', false ),
            'createdon' => array( 'createdon', false )
           );
        return $sortable_columns;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::get_columns()
     */
    public function get_columns()
    {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'type'      => __( 'Type', 'wp-ds-geography' ),
            'name'      => __( 'Layer Name', 'wp-ds-geography' ),
            'createdon' => __( 'Created on', 'wp-ds-geography' ),
            'createdby' => __( 'Created by', 'wp-ds-geography' ),
            'shortcode' => __( 'Shortcode', 'wp-ds-geography' )
        );
        return $columns;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца cb (чекбокс)
     * @param unknown $item
     * @return string
     */
    public function column_cb( $item )
    {
        return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item['id'] );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца type
     *
     * @param array $item
     * @return string
     */
    public function column_type( $item )
    {
        if ( array_key_exists( $item['id'], $this->all_layers_relationships ) ) {
            $result = '<img width="20" height="20" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'images/multi_layers.png" />';
        } else {
            $result = '<img width="20" height="20" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'images/mono_layers.png" />';
        }

        return $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца name
     * 
     * @param array $item
     * @return string
     */
    public function column_name( $item )
    {
        $url = wp_nonce_url( '?page=wpds_geography_all_layers&id=' . $item['id'] . '&action=delete', '{E5EE1D1D-0C3A-407F-A442-8D65058DD154}' );
        $actions = array(
                'edit'   => '<a href="?page=wpds_geography_the_layer&id=' . $item['id'] . '">' . __( 'Edit' ) . '</a>',
                'delete' => '<a href="' . $url . '">' . __( 'Delete' ) . '</a>'
        );
        $name = '<strong><a class="row-title" href="?page=wpds_geography_the_layer&id=' . $item['id'] . '">' . htmlspecialchars( stripslashes( $item['name'] ), ENT_QUOTES ) . '</a></strong>';
        return sprintf( '%1$s %2$s', $name, $this->row_actions( $actions ) );
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function column_createdby( $item )
    {
        $createdby_user = get_user_by( 'id', (int)$item['createdby'] );
        return $createdby_user->user_login; 
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца shortcode
     *
     * @param array $item
     * @return string
     */
    public function column_shortcode( $item )
    {
        return '<span class="wpds_geography_shortcode">[wpds_geography layer="' . $item['id'] . '"]</span>';
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Если класс WPDSListTable не нашел функцию вида column_название-столбца
     * то вызывается эта функция - по умолчанию
     * 
     * @param unknown $item
     * @param unknown $column_name
     * @return unknown|mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'cb':
            case 'type':
            case 'name':
            case 'createdon':
            case 'createdby':
            case 'shortcode':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::get_bulk_actions()
     */
    public function get_bulk_actions()
    {
        $actions = array(
                       'delete' => __( 'Delete' )
                   );
        return $actions;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::prepare_items()
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $user     = get_current_user_id();
        $screen   = get_current_screen();
        $option   = $screen->get_option( 'per_page', 'option' );
        $per_page = get_user_meta( $user, $option, true );
        if ( empty ( $per_page ) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }

        $current_page = $this->get_pagenum();
        $total_items = count( $this->all_layers );
        $this->found_data = array_slice( $this->all_layers, ( ( $current_page-1 ) * $per_page ), $per_page );

        $this->set_pagination_args( array(
                                        'total_items' => $total_items,
                                        'per_page'    => $per_page
                                    )
        );
        $this->items = $this->found_data;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Вывод страницы со списком маркеров
     */
    public function display_template()
    {
        include 'template-list-layers.php';
    }
} //class

?>