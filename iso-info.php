<?php
/*
Модуль страницы info
*/
?>
   <div class="wrap">
     <div id="icon-users" class="icon32"></div>
        <h2>
        <?php _e( 'Information about the plug-in iso-management-snuglinux', 'iso-management-snuglinux' ) ?>
        </h2>
        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
           <p>
              <?php echo '<img src="' .plugins_url( 'images/iso-64.png', __FILE__ ). '"name="ISO" align="top" hspace="2" width="64" height="64" border="2"/>'; ?>
              <?php _e( 'The plugin is designed to automatically create ISO images via the Web interface.', 'iso-management-snuglinux' )?>
           </p>
        </div>
  </div>
  <div class="wrap">
     <p>
        <?php _e( 'The plugin works in conjunction with the start-user-build-snuglive script.', 'iso-management-snuglinux' )?>
     </p>
     <p>
        <?php _e( 'Author: <td class="email column-email" data-colname="E-mail"><a href="mailto:snuglinux@ukr.net">snuglinux@ukr.net</a></td>', 'iso-management-snuglinux' )?>
     </p>
  </div>

 <?php
