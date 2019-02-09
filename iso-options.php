<?php
/*
Модуль страницы Опций
*/

?>
<div class="wrap">
   <h2><?php _e( 'Options computer accounting', 'iso-management' )?></h2>
      <form method="post" action="options.php">
        <?php wp_nonce_field('update-options'); ?>

           <table class="form-table">
              <tr valign="top">
                 <th scope="row"><?php _e( 'Number of rows per page: ', 'iso-management' )?></th>
                 <td><input type="text" name="number_rows_per_page" value="<?php echo get_option('number_rows_per_page'); ?>" /></td>
              </tr>
           </table>

           <input type="hidden" name="action" value="update" />
           <input type="hidden" name="page_options" value="number_rows_per_page" />

           <p class="submit">
              <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
           </p>
    </form>
</div>
<?php
$my_role = get_role( 'ca_moderator' ); // указываем роль, которая нам нужна
print_r( $my_role );
