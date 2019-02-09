<?php
/*
Общие функции
*/

/* Execute the given command by displaying console output live to the user..
*  @param string cmd   : command to be executed.
*  @return array exit_status : exit status of the executed command.
*     output  : console output of the executed command.*/
function liveExecuteCommand($cmd) {

   ?>
      <script type="text/javascript">
         function AutoScrolling() {
            window.scrollTo(0, document.body.scrollHeight);
         }
      </script>
    <?php

    while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

    $live_output  = "";
    $complete_output = "";

    while (!feof($proc)) {
      $live_output  = fread($proc, 4096);
      $complete_output = $complete_output . $live_output;
      echo "$live_output";
      @flush();
    }

    pclose($proc);

      ?>
         <script type="text/javascript">
            AutoScrolling();
         </script>
      <?php

    // get exit status
    preg_match('/[0-9]+$/', $complete_output, $matches);

    // return exit status and intended output.
    return array (
        'exit_status' => intval($matches[0]),
        'output'  => str_replace("Exit status : " . $matches[0], '', $complete_output)
       );
}

/*************************************/
//only_dir_or_files: 0 - all
//                   1 - only dir
//                   2 - only file
function iso_get_files($path, $order = 0, $mask, $only_dir_or_files = 0, $return_full_path = 0) {
  $fdir = array();
  if ( $path [ strlen( $path ) - 1 ] != '/' ) {
       $path .= '/';
  }
  if (false !== ($files = scandir($path, $order))) {
     array_shift($files); // del '.'
     array_shift($files); // del '..'
     foreach ($files as $file_name) {
             if ( $only_dir_or_files == 0 && fnmatch($mask, $file_name)) {
                  if ($return_full_path == 0)
                     $fdir[] = $file_name;
                  else $fdir[] = $path. $file_name;
              }
              elseif ( $only_dir_or_files == 1 && is_dir( $path.'/'.$file_name ) == true && fnmatch($mask, $file_name)) {
                  if ($return_full_path == 0)
                     $fdir[] = $file_name;
                  else $fdir[] = $path. $file_name;
              }
              elseif ( $only_dir_or_files == 2 && is_file( $path.'/'.$file_name ) == true && fnmatch($mask, $file_name)) {
                  if ($return_full_path == 0)
                     $fdir[] = $file_name;
                  else $fdir[] = $path. $file_name;
              }
       }
    }
    return ($fdir);
}

/*********** вывод сообщения ************/
function display_message($string) {
   ?>
      <div class="wrap">
         <div id="icon-users" class="icon32"></div>
            <form method="post">
               <h3>
                  <?php echo $string; ?>
               </h3>
               <p>
                  <input type="submit" name="close" class="page-title-action" value= "<?php echo _e( 'Close', 'computer-accounting' );?>">
               </p>
            </form>
        </div>
     </div>
   <?php
   exit;
}

/*********** нажата кнопка загрузить картинку ************/
function form_add_picture( $type_name ) {
   $file_name = $_FILES['up_file']['name'];
   if ( empty( $file_name )) {
      display_message( _e( "No file selected for upload!", 'computer-accounting' ));
   }
   $path_to = str_replace('/includes', '', plugin_dir_path(__FILE__)) ."images/".$type_name."/";
   echo $path_to;
   if (! is_dir($path_to)) {
      mkdir($path_to, 0770);
      chmod($path_to, 0770);
   }
   // обработка ошибок
   switch ($_FILES['up_file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            display_message( _e( 'The file was not uploaded.', 'computer-accounting' ));
        case UPLOAD_ERR_INI_SIZE:
            display_message( _e( 'The size of the received file has exceeded the maximum allowed size, which is specified by the directive upload_max_filesize.', 'computer-accounting' ));
        case UPLOAD_ERR_FORM_SIZE:
            display_message( _e( 'The size of the uploaded file exceeded the value of MAX_FILE_SIZE.', 'computer-accounting' ));
        default:
            display_message( _e( 'Unknown error.', 'computer-accounting' ));
    }
    // проверим тип файла
    $info_mime = new finfo(FILEINFO_MIME_TYPE);
    if ( false === $ext = array_search(
        $info_mime -> file($_FILES['up_file']['tmp_name']),
        array( 'jpg' => 'image/jpeg',
               'png' => 'image/png',
               'gif' => 'image/gif',
        ),
        true
   )) {
      display_message( _e( 'Invalid file format. Are allowed: GIF, JPEG, PNG', 'computer-accounting' ));
   }
   $file_size = getimagesize ($_FILES['up_file']['tmp_name']);
   if (( intval($file_size[0]) > 250) or (intval($file_size[1]) > 250)) {
      display_message( _e( 'Image size should not exceed 250X250 pixels', 'computer-accounting' ));
   }
   $file_in   = $path_to."type-tmp";
   $tmp_file  = $_FILES['up_file']['tmp_name'];
   if ( file_exists( $tmp_file )) {
      if ( ! copy( $tmp_file, $file_in )) {
         display_message( "Could not copy $file_name" );
      }
   } else {
     display_message( "The file not ".$tmp_file." exists" );
   }
   $_SESSION["image"] = $path_to."type-tmp";
   $_SESSION["form_type"] = "edit";
}
