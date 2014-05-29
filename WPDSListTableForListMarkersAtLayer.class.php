<?php

require_once 'WPDSListTable.class.php';

/**
 * 
 * @author DiamondSteel
 *
 */
class WPDSListTableForListMarkersAtLayer extends WPDSListTable
{
    protected $permissions_settings;
    
    public $layer_id;
    public $all_markers;
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function __construct( $layer_id, $all_markers )
    {
        if ( empty( $layer_id ) ) { exit; }
        $this->layer_id = $layer_id;
        $this->all_markers = $all_markers;
        $this->permissions_settings = get_option( 'wpds_geography_options_permissions' );
        
        parent::__construct( array(
                                'singular'  => __( 'marker', 'wp-ds-geography' ),  //singular name of the listed records
                                'plural'    => __( 'markers', 'wp-ds-geography' ), //plural name of the listed records
                                'ajax'      => false                               //does this table support ajax?
                             )
                );
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::no_items()
     */
    public function no_items()
    {
        _e( "It's empty. Try to add a marker.", 'wp-ds-geography' );
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::get_columns()
     */
    public function get_columns()
    {
        $columns = array(
            'markername' => __( 'Marker Name', 'wp-ds-geography' ),
            'layer'      => __( 'Layer', 'wp-ds-geography' ),
            'icon'       => __( 'Icon', 'wp-ds-geography' ),
            'createdon'  => __( 'Created on', 'wp-ds-geography' ),
            'createdby'  => __( 'Created by', 'wp-ds-geography' ),
        );
        return $columns;
    }

    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца name
     * 
     * @param array $item
     * @return string
     */
    public function column_markername( $item )
    {
        $actions = array(
                'edit'   => '<a href="?page=wpds_geography_the_marker&id=' . $item['id'] . '">' . __( 'Edit' ) . '</a>',
        );
        $markername = '<strong><a class="row-title" href="?page=wpds_geography_the_marker&id=' . $item['id'] . '">' . htmlspecialchars( stripslashes( $item['markername'] ), ENT_QUOTES ) . '</a></strong>';
        return sprintf('%1$s %2$s', $markername, $this->row_actions($actions) );
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца layer
     * 
     * @param array $item
     * @return string
     */
    public function column_layer( $item )
    {
        if ( ! empty( $item['layer'] ) ){
            $result = '<a href="?page=wpds_geography_the_layer&id=' . $item['layer_id'] . '">' . htmlspecialchars( stripslashes( $item['layer'] ), ENT_QUOTES ) . '</a>';
        } else {
            $result = '';
        }
        return $result;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * Функция для обработки значений столбца icon
     * @param array $item
     * @return string
     */
    public function column_icon( $item )
    {
        if ( ! empty( $item['icon'] ) and file_exists( WPDS_GEOGRAPHY_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $item['icon'] ) ) {
            $marker = '<img width="32" height="37" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'icons/' . $item['icon'] . '" />';
        } else {
            $marker = '<img width="32" height="37" src="' . WPDS_GEOGRAPHY_PLUGIN_URL . 'icons/0-default.png" />';
        }
        return '<div style="width: 51px; height: 37px; text-align: center; background: url(' . WPDS_GEOGRAPHY_PLUGIN_URL . 'images/default-shadow.png) no-repeat;">' . $marker . '</div>';
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function column_createdby( $item )
    {
        $createdby_user = get_user_by( 'id', (int)$item['createdby'] );
        return $createdby_user->user_login; 
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
            case 'name':
            case 'layer':
            case 'icon':
            case 'createdon':
            case 'createdby':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    public function get_bulk_actions() {
        $actions = array();
        return $actions;
    }
    
    #-------------------------------------------------------------------------------------------------------------------------#
    /**
     * (non-PHPdoc)
     * @see WPDSListTable::prepare_items()
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->all_markers;
    }
} //class

?>