<?php if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'template-wpds-options.php' ) { die(); } ?>

<div class="wrap">
    <H2><?php _e( 'Geography settings', 'wp-ds-geography' ); ?></H2>
    <BR />
    
    <h2 class="nav-tab-wrapper">  
        <?php $this->form_generate_tabs(); ?>
    </h2>  
    
    <form method="post">
        <?php $this->form_current_tab(); ?>
    </form>
    
</div>