<?php

// =============================================================================
// VIEWS/GLOBAL/_FOOTER-WIDGET-AREAS.PHP
// -----------------------------------------------------------------------------
// Outputs the widget areas for the footer.
// =============================================================================

$n = x_footer_widget_areas_count();

?>

<?php if ( $n != 0 ) : ?>

  <footer class="x-colophon top" role="contentinfo">

    <div class="x-container max width">


      <?php

      $i = 0; while ( $i < $n ) : $i++;

        $last = ( $i == $n ) ? ' last' : '';

        echo '<div class="x-column x-md x-1-' . $n . $last . '">';
          dynamic_sidebar( 'footer-' . $i );
        echo '</div>';

      endwhile;

      ?>

    </div>

      <div class="x-container max width middle">

        <div class="footer-module module-subscribe">
          <?php echo do_shortcode('[x_subscribe form="1528"]'); 

          ?>
        </div>

        <div class="footer-module module-estimate">
          <h5>Get a Free Quote</h5>
          <p>
            In the market for a new vinyl fence, pvc deck or vinyl railing system? Contact us to get a free project quote today.
          </p>
          <a href="/estimate/" class="x-btn">Contact Us</a>
        </div>

    </div>

  </footer>

<?php endif; ?>