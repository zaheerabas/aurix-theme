<?php
if ( is_active_sidebar('sidebar-1') ) :
  echo '<aside id="secondary" class="widget-area">';
  dynamic_sidebar('sidebar-1');
  echo '</aside>';
endif;
