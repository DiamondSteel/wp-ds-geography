<?php if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'template-list-layers.php' ) { die(); } ?>

<?php global $WPDSListTableForListLayers; ?>

<div class="wrap">
    <h2>
        <?php _e( 'Layers', 'wp-ds-geography' ) ?>
        <?php echo ' <a href="' . esc_url( admin_url( 'admin.php?page=wpds_geography_the_layer' ) ) . '" class="add-new-h2">' . __( 'Add new layer', 'wp-ds-geography' ) . '</a>'; ?>
        <?php
        if ( ! empty( $WPDSListTableForListLayers->search_string ) ){
            printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', htmlspecialchars( stripslashes( $WPDSListTableForListLayers->search_string ) ) );
        }
        ?>
    </h2>    

        <form action="" method="get">
        <input type="hidden" name="page" value="wpds_geography">
        <?php $WPDSListTableForListLayers->search_box( __( 'Search' ), 'search_id' ); ?>
        </form>
        
        <form method="post">
        <?php $WPDSListTableForListLayers->display(); ?>
        
        <?php wp_nonce_field( '{9A9E534C-4EF7-4793-AAEC-42A84D0D5187}', 'wp_nonce_delete_layers_field' ); ?>
        </form>
</div>