<?php
/*
Модуль главной страницы
*/

 $url = $_SERVER['REQUEST_URI'];
 if ( isset( $_POST["new_iso"] )) {
    ?>
      <div class="wrap">
         <div id="icon-users" class="icon32"></div>
            <h2>
            <?php _e( 'Create iso image', 'iso-management-snuglinux' ) ?>
            </h2>
            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
               <p>
                  <?php echo '<img src="' .plugins_url( 'images/iso-64.png', __FILE__ ). '"name="ISO" align="top" hspace="2" width="64" height="64" border="2"/>'; ?>
                  <?php _e( 'Creating a new iso image', 'iso-management-snuglinux' )?>
               </p>
            </div>
      </div>
    <?php
     // проверим есть ли файл
     $str_data = date("Y.m.d");
     $file = "snuglinux-" . $str_data . "-x86_64.iso";
     $path = 'iso';
     if ( $path [ strlen( $path ) - 1 ] != '/' ) {
        $path .= '/';
     }
     $full_path = $_SERVER['DOCUMENT_ROOT'] . '/'. $path;
     if ( file_exists($full_path) == false ) {
        echo sprintf( __( "The %s path does not exist!", 'iso-management-snuglinux' ), $full_path );
        exit;
     }
     if ( file_exists($full_path . $file) == true ) {
        echo sprintf( __( 'The %s file already exists!', 'iso-management-snuglinux' ), $file);
        add_button_back();
        exit;
     }
     ?>
        <pre>
           <?php $result = liveExecuteCommand('sudo /bin/start-user-build-snuglive');?>
        </pre>
     <?php
    //$result = liveExecuteCommand('ping  -c 30 8.8.8.8');
    if ( $result['exit_status'] === 0 ) {
        add_iso_database();
       _e( "Successfully executed script", 'iso-management-snuglinux' );
    } else {
       _e( "Error running script", 'iso-management-snuglinux' );
    }
    add_button_back();
    exit;
 }
 else if ( stripos($url, '=iso-page&delete&iso=') > 0 ) {
    if ( isset( $_POST["delete"] )) {
       delete_iso();
    }
    else if ( isset( $_POST["cancel"] )) {
       wp_redirect(esc_url(get_admin_url(null, 'admin.php?page=iso-page' )));
       }
    else {
       $paged_array = explode( '=iso-page&delete&iso=', $url );
       $_SESSION["delete-iso"] = $paged_array[1];
       $_SESSION["download-iso"] = NULL;
    }}
 else if ( stripos($url, '=iso-page&download&iso=') > 0 ) {
    $paged_array = explode( '=iso-page&download&iso=', $url );
    $_SESSION["download-iso"] = $paged_array[1];
    $_SESSION["delete-iso"]   = NULL;
    }
 else {
    $_SESSION["download-iso"] = NULL;
    $_SESSION["delete-iso"]   = NULL;
 }
 // загрузка iso файла
 if (isset($_SESSION["download-iso"])) {
    download_iso();
 }
 else if (isset($_SESSION["delete-iso"])) {
    view_delete_form();
 }
 else {
    view_maim_form();
 }

//======================================
function delete_iso() {
   global $wpdb;
   global $iso_table_list;

   $name_file = $_SESSION["delete-iso"];
   $path = 'iso';
   if ( $path [ strlen( $path ) - 1 ] != '/' ) {
      $path .= '/';
   }
   $full_path = $_SERVER['DOCUMENT_ROOT'] . '/'. $path;
   if ( file_exists($full_path) == false ) {
      echo sprintf( __( "The %s path does not exist!", 'iso-management-snuglinux' ), $full_path);
      exit;
   }
   // удалить расширение файла (уже не нужно)
   // $name_file = pathinfo($file, PATHINFO_FILENAME);

   // удалить md5sum файл
   $md5sum_file = $name_file . '.md5';
   if ( file_exists( $full_path . $md5sum_file )) {
      unlink( $full_path . $md5sum_file );
   }
   $iso_file = $name_file . '.iso';
   if (file_exists( $full_path . $iso_file )) {
      unlink( $full_path . $iso_file );
   }
   $delete_data = array( 'name' => $name_file );
   $wpdb -> delete( $iso_table_list, $delete_data, null );
   wp_redirect(esc_url(get_admin_url(null, 'admin.php?page=iso-page' )));
}

//======================================
function download_iso() {
   $path = 'iso';
   if ( $path [ strlen( $path ) - 1 ] != '/' ) {
      $path .= '/';
   }
   $file = $_SESSION["download-iso"];
   $server_name = $_SERVER['SERVER_NAME'];
   if ( $server_name [ strlen( $server_name ) - 1 ] != '/' ) {
      $server_name .= '/';
   }
   $link = 'https://' . $server_name . $path . $file . '.iso';
   wp_safe_redirect( $link );
   $_SESSION["download-iso"] = NULL;
}

//======================================
function add_iso_database() {
   global $wpdb;
   global $iso_table_list;

   $path = 'iso';
   $mask = '*.add';
   $data = array();
   if ( $path [ strlen( $path ) - 1 ] != '/' ) {
      $path .= '/';
   }
   $full_path = $_SERVER['DOCUMENT_ROOT'] . '/'. $path;
   if ( file_exists($full_path) == false ) {
       echo sprintf( __( "The %s path does not exist!", 'iso-management-snuglinux' ), $full_path);
       return $data;
   }

   $files = iso_get_files($full_path, 0, $mask);
   foreach($files as $file){
           $name_file   = pathinfo($file, PATHINFO_FILENAME);
           $file_iso    = $name_file . '.iso';
           $file_md5sum = $name_file . '.md5';

           if ( ! file_exists($full_path . $file_iso) ) {
              return 1;
           }
           if ( file_exists($full_path . $file_md5sum) ) {
              $md5sum = file_get_contents($full_path . $file_md5sum, FALSE, NULL, 0, 100);
           }
           else {
              $md5sum = '';
           }

           $save_data = array(
                   'name'        => $name_file,
                   'md5sum'      => $md5sum,
                   'file_iso'    => $full_path . $file_iso,
                   'file_md5sum' => $full_path . $file_md5sum
                   );
           $wpdb -> insert( $iso_table_list, $save_data );
           unlink($full_path . $file);
   }
   return 0;
}

//====================================
function view_delete_form() {
   $iso_file = $_SESSION["delete-iso"];
   ?>
      <div class="wrap">
          <div id="icon-users" class="icon32"></div>
             <form method="post">
                <h2>
                   <?php _e( 'Delete file', 'iso-management-snuglinux' )?>
                </h2>
                <p>
                  <h4>
                      <font color="#ce181e">
                         <?php echo sprintf( __( "Do you want to delete the %s file?", 'iso-management-snuglinux' ), $iso_file );?>
                      </font>
                   </h4>
                   <p>
                      <input type="submit" name="delete" class="page-title-action" value= "<?php echo _e( 'Delete', 'iso-management-snuglinux' );?>">
                      <input type="submit" name="cancel" class="page-title-action" value= "<?php echo _e( 'Cancel', 'iso-management-snuglinux' );?>">
                   </p>
                </p>
             </form>
      </div>
   <?php
}

//====================================
function view_maim_form() {
    global $wpdb, $TableISO;

    $TableISO -> prepare_items();

    ?>
      <div class="wrap">
         <div id="icon-users" class="icon32"><br/></div>
            <h2>
            <?php _e( 'ISO Management', 'iso-management-snuglinux' ) ?>
            </h2>
            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
               <p>
                  <?php echo '<img src="' .plugins_url( 'images/iso-64.png', __FILE__ ). '"name="ISO" align="top" hspace="2" width="64" height="64" border="2"/>'; ?>
                  <?php _e( 'This is the main page of the plugin "ISO Management"', 'iso-management-snuglinux' )?>
               </p>
            </div>
            <?php
               if ( current_user_can( 'iso_create' )) {
                  ?>
                     <!-- Тип кодирования данных, enctype -->
                     <form enctype="multipart/form-data" action="" method="POST">
                     <p>
                        <input type="submit" name="new_iso" class="page-title-action" value= "<?php echo _e( 'New ISO', 'iso-management-snuglinux' );?>" >
                     </p>
                     </form>
                  <?php
                  }
            ?>
      </div>
      <?php $TableISO -> display() ?>
     <?php
   /*  <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
     <form id="movies-filter" method="get">
     <!-- For plugins, we also need to ensure that the form posts back to our current page -->
     <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
     <!-- Now we can render the completed list table -->
   */
}

//====================================
function add_button_back() {
   ?>
      <div class="wrap">
         <div id="icon-users" class="icon32"></div>
              <!-- Тип кодирования данных, enctype -->
              <form enctype="multipart/form-data" action="" method="POST">
              </p>
                <input type="submit" name="back" class="page-title-action" value= "<?php echo _e( 'Back', 'iso-management-snuglinux' );?>" >
              </p>
            </form>
        </div>
   <?php
}
