<?php if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'template-the-marker.php' ) { die(); } ?>

<?php global $WPDSAddAndEditMarker; ?>
<form method="post">
<div class="wrap">
    <h2>
        <?php $WPDSAddAndEditMarker->form_header(); ?>
    </h2>    
    <BR />
    <div class="metabox-holder has-right-sidebar">
        <div class="inner-sidebar">
            
            <div class="postbox">
                <h3><span><?php _e( 'Publish' ); ?></span></h3>
                <div class="inside" style="margin: 0; padding: 0;">
                    <div class="submitbox" id="submitpost">
                        <?php $WPDSAddAndEditMarker->form_date_from_marker(); ?>
                        <div id="major-publishing-actions">
                            <div id="delete-action">
                                <?php $WPDSAddAndEditMarker->form_delete_link(); ?>
                            </div>

                            <div id="publishing-action">
                                <span class="spinner" style="display: none;"></span>
                                <?php $WPDSAddAndEditMarker->form_submit_button(); ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="postbox">
                <h3><span><?php _e( 'Map settings', 'wp-ds-geography' ); ?></span></h3>
                <div class="inside">
                    <p>
                        <?php _e( 'Width:', 'wp-ds-geography' ); ?> <input size="3" maxlength="4" type="text" id="mapwidth" name="mapwidth" value="<?php $WPDSAddAndEditMarker->form_mapwidth(); ?>">
                                <label for="mapwidthunit_px"><input id="mapwidthunit_px" type="radio" name="mapwidthunit" value="px" <?php $WPDSAddAndEditMarker->form_mapwidthunit( 'px' ); ?> /> px &nbsp;</label>
                                <label for="mapwidthunit_percent"><input id="mapwidthunit_percent" type="radio" name="mapwidthunit" value="%" <?php $WPDSAddAndEditMarker->form_mapwidthunit( '%' ); ?> /> %</label><BR />
                        <?php _e( 'Height:', 'wp-ds-geography' ); ?> <input size="3" maxlength="4" type="text" id="mapheight" name="mapheight" value="<?php $WPDSAddAndEditMarker->form_mapheight(); ?>"> px
                    </p>
                    <p><?php _e( 'Scale:', 'wp-ds-geography' ); ?> <input size="2" type="text" id="zoom" name="zoom" value="<?php $WPDSAddAndEditMarker->form_zoom(); ?>"></p>
                    <h4><?php _e( 'Map source:', 'wp-ds-geography' ); ?></h4>
                    <?php $WPDSAddAndEditMarker->form_layers_control(); ?>
                </div>
            </div>

            <div class="postbox">
                <h3><span><?php _e( 'Layers', 'wp-ds-geography' ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Marker can be tied to a layer:', 'wp-ds-geography' ); ?></p>
                    <?php $WPDSAddAndEditMarker->form_layers_select(); ?>
                </div>
            </div>
            
        </div>
                
        <div id="post-body">
            <div id="post-body-content">
            
                <div id="titlediv">
                    <div id="titlewrap">
                        <input type="text" name="name" size="255" value="<?php echo $WPDSAddAndEditMarker->form_name(); ?>" id="name" placeholder="<?php _e( 'Enter the name of the marker', 'wp-ds-geography' ); ?>" />
                    </div>
                </div>
        
                <div class="postbox" style="overflow: hidden;">
                    <h3><span><?php _e( 'Preview', 'wp-ds-geography' ); ?></span></h3>
                    <div class="inside">
                        <?php $WPDSAddAndEditMarker->form_map(); ?>
                        <BR />
                        <label for="address"><?php _e( 'Choose location on map or input address', 'wp-ds-geography' ); ?></label><BR />
                        <input style="width: 100%;" type="text" id="address" name="address" value="" />
                    </div>
                </div>
                
                <div class="postbox">
                    <h3><span><?php _e( 'Marker settings', 'wp-ds-geography' ); ?></span></h3>
                    <div class="inside">
                        <h4><?php _e( 'Coordinates', 'wp-ds-geography' ); ?></h4>
                        <p>
                            <?php _e( 'Latitude:', 'wp-ds-geography' ); ?> <input type="text" id="lat" name="lat" value="<?php $WPDSAddAndEditMarker->form_lat(); ?>" /> &nbsp;
                            <?php _e( 'Longitude:', 'wp-ds-geography' ); ?> <input type="text" id="lon" name="lon" value="<?php $WPDSAddAndEditMarker->form_lon(); ?>" />
                        </p>
                        <h4><?php _e( 'Flag', 'wp-ds-geography' ); ?></h4>
                        <?php $WPDSAddAndEditMarker->form_icon_list(); ?>
                    </div>
                </div>
                
                <div class="postbox">
                    <h3><span><?php _e( 'Popup window', 'wp-ds-geography' ); ?></span></h3>
                    <div class="inside">
                        <h4><?php _e( 'Display options', 'wp-ds-geography' ); ?></h4>
                        <p><label for="openpopup"><input type="checkbox" name="openpopup" id="openpopup" <?php $WPDSAddAndEditMarker->form_openpopup(); ?> /> <?php _e( 'Open popup window', 'wp-ds-geography' ); ?></label><BR /><i><?php _e( 'If unchecked, the popup window will be shown only after marker is clicked.', 'wp-ds-geography' ); ?></i></p>
                        
                        <h4><?php _e( 'Text in a popup window', 'wp-ds-geography' ); ?></h4>
                        <?php
                        $args = array( 'wpautop' => 1
                                ,'media_buttons' => 1
                                ,'textarea_name' => 'popuptext'
                                ,'textarea_rows' => 10
                                ,'tabindex' => null
                                ,'editor_css' => ''
                                ,'editor_class' => ''
                                ,'teeny' => 0
                                ,'dfw' => 0
                                ,'tinymce' => 1
                                ,'quicktags' => 1
                        );
                        wp_editor( stripslashes( preg_replace( '/(\015\012)|(\015)|(\012)/','<br/>', $WPDSAddAndEditMarker->form_popuptext() ) ), 'popuptext', $args );
                        ?>
                    </div>
                </div>
            
            </div>
        </div>

    </div>
</div>

<?php $WPDSAddAndEditMarker->form_hidden_input(); ?>
<?php wp_nonce_field( '{B7C83ACB-A647-4C2B-B147-DE1314AE27B1}', 'wp_nonce_save_marker_field' ); ?>

</form>

<?php $WPDSAddAndEditMarker->form_js(); ?>
