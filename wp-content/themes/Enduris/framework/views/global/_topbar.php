<?php

// =============================================================================
// VIEWS/GLOBAL/_TOPBAR.PHP
// -----------------------------------------------------------------------------
// Includes topbar output.
// =============================================================================

?>

<?php if ( x_get_option( 'x_topbar_display', '' ) == '1' ) : ?>

  <div class="x-topbar">
    <div class="x-topbar-inner x-container max width">

      <?php if ( x_get_option( 'x_topbar_content' ) != '' ) : ?>

      <p class="p-info">

      <?php echo x_get_option( 'x_topbar_content' ); ?>

      </p>

      <span class="p-number">

      <a href="tel:18883297428">888.329.7428</a>

      </span>

      <?php endif; ?>

      <?php x_social_global(); ?>

    </div>
    
  </div>

<?php endif; ?>