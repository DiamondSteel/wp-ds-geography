<?php if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'template-the-layer.php' ) { die(); } ?>

<?php global $WPDSAddAndEditLayer; ?>
<form method="post">
<div class="wrap">
    <h2>
        <?php $WPDSAddAndEditLayer->form_header(); ?>
    </h2>    
    <BR />
    <div class="metabox-holder has-right-sidebar">
        <div class="inner-sidebar">
            
            <div class="postbox">
                <h3><span><?php _e( 'Publish' ); ?></span></h3>
                <div class="inside" style="margin: 0; padding: 0;">
                    <div class="submitbox" id="submitpost">
                        <?php $WPDSAddAndEditLayer->form_date_from_layer(); ?>
                        <div id="major-publishing-actions">
                            <div id="delete-action">
                                <?php $WPDSAddAndEditLayer->form_delete_link(); ?>
                            </div>

                            <div id="publishing-action">
                                <span class="spinner" style="display: none;"></span>
                                <?php $WPDSAddAndEditLayer->form_submit_button(); ?>
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
                        <?php _e( 'Width:', 'wp-ds-geography' ); ?> <input size="3" maxlength="4" type="text" id="mapwidth" name="mapwidth" value="<?php $WPDSAddAndEditLayer->form_mapwidth(); ?>">
                                <label for="mapwidthunit_px"><input id="mapwidthunit_px" type="radio" name="mapwidthunit" value="px" <?php $WPDSAddAndEditLayer->form_mapwidthunit( 'px' ); ?> /> px &nbsp;</label>
                                <label for="mapwidthunit_percent"><input id="mapwidthunit_percent" type="radio" name="mapwidthunit" value="%" <?php $WPDSAddAndEditLayer->form_mapwidthunit( '%' ); ?> /> %</label><BR />
                        <?php _e( 'Height:', 'wp-ds-geography' ); ?> <input size="3" maxlength="4" type="text" id="mapheight" name="mapheight" value="<?php $WPDSAddAndEditLayer->form_mapheight(); ?>"> px
                    </p>
                    <fieldset>
                        <p><?php _e( 'Set the scale and center of the map', 'wp-ds-geography' ); ?></p>
                        <p><?php _e( 'Scale:', 'wp-ds-geography' ); ?> <input size="2" type="text" id="zoom" name="zoom" value="<?php $WPDSAddAndEditLayer->form_zoom(); ?>"></p>
                        <p>
                            <?php _e( 'Latitude:', 'wp-ds-geography' ); ?><BR /><input type="text" id="lat" name="lat" value="<?php $WPDSAddAndEditLayer->form_lat(); ?>" /><BR />
                            <?php _e( 'Longitude:', 'wp-ds-geography' ); ?><BR /><input type="text" id="lon" name="lon" value="<?php $WPDSAddAndEditLayer->form_lon(); ?>" />
                        </p>
                        <p><?php _e( 'or you can', 'wp-ds-geography' ); ?></p>
                        <p><label for="fitbounds"><input type="checkbox" name="fitbounds" id="fitbounds" <?php $WPDSAddAndEditLayer->form_fitbounds(); ?>/><?php _e( 'fit bounds', 'wp-ds-geography' ); ?></label></p>
                    </fieldset>
                    <p><label for="clustering"><input type="checkbox" name="clustering" id="clustering" <?php $WPDSAddAndEditLayer->form_clustering(); ?>/><?php _e( 'Group markers', 'wp-ds-geography' ); ?></label></p>
                    <p><label for="listmarkers"><input type="checkbox" name="listmarkers" id="listmarkers" <?php $WPDSAddAndEditLayer->form_listmarkers(); ?>/><?php _e( 'Show markers list under the map', 'wp-ds-geography' ); ?></label></p>
                    <h4><?php _e( 'Map source:', 'wp-ds-geography' ); ?></h4>
                    <?php $WPDSAddAndEditLayer->form_layers_control(); ?>
                </div>
            </div>

            <div class="postbox">
                <h3><span><?php _e( 'Multilayer map', 'wp-ds-geography' ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Display markers other layers in this map', 'wp-ds-geography' ); ?></p>
                    <?php $WPDSAddAndEditLayer->form_layers_list(); ?>
                </div>
            </div>
            
        </div>
                
        <div id="post-body">
            <div id="post-body-content">
            
                <div id="titlediv">
                    <div id="titlewrap">
                        <input type="text" name="name" size="255" value="<?php echo $WPDSAddAndEditLayer->form_name(); ?>" id="name" placeholder="<?php _e( 'Enter the name of the layer', 'wp-ds-geography' ); ?>" />
                    </div>
                </div>
        
                <div class="postbox" style="overflow: hidden;">
                    <h3><span><?php _e( 'Preview', 'wp-ds-geography' ); ?></span></h3>
                    <div class="inside">
                        <?php $WPDSAddAndEditLayer->form_map(); ?>
                        <BR />
                        <label for="address"><?php _e( 'Choose location on map or input address', 'wp-ds-geography' ); ?></label><BR />
                        <input style="width: 100%;" type="text" id="address" name="address" value="" />
                    </div>
                </div>
                
                <BR />
                
                <div id="markerslist">
                    <?php $WPDSAddAndEditLayer->form_markers_at_layer(); ?>
                </div>
                
            </div>
        </div>

    </div>
</div>

<?php $WPDSAddAndEditLayer->form_hidden_input(); ?>
<?php wp_nonce_field( '{381d0df8-6178-4e75-9a7f-66a5ed9372dd}', 'wp_nonce_save_layer_field' ); ?>

</form>

<?php $WPDSAddAndEditLayer->form_js(); ?>
