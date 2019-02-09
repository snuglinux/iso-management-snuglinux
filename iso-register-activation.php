<?php
/*
Регистрация плагина
*/
global $wpdb;

global $db_version_plug;
global $iso_table;

$iso_table     = $wpdb -> prefix . "iso_list";

$installed_ver = get_option( "db_iso_management" );

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if (( $installed_ver != $db_version_plug ) or ( $wpdb -> get_var("SHOW TABLES LIKE '$iso_table'") != $iso_table )) {
   $sql_iso = "
       CREATE TABLE $iso_table (
       name         VARCHAR(255) NOT NULL,
       md5sum       VARCHAR(255) NOT NULL,
       file_iso     VARCHAR(500) NOT NULL
       file_md5sum  VARCHAR(500) NOT NULL
       );";

    dbDelta($sql_iso);
}

function create_new_role() {

    _x( 'ISO Moderator', 'iso-management-snuglinux' );

    $role = add_role( 'iso moderator', 'ISO Moderator' );
    $role = get_role( 'iso moderator' );
    $role -> add_cap( 'read' );                 // чтение
    $role -> add_cap( 'list_users' );           // просмотр пользователей
    $role -> add_cap( 'upload_files' );         // загружать файлы
    $role -> add_cap( 'edit_published_pages' ); // править обубликованные страницы
    $role -> add_cap( 'edit_pages' );    // править страницы
    $role -> add_cap( 'edit_posts' );    // править статьи
    $role -> add_cap( 'delete_pages' );  // удалять страницы
    $role -> add_cap( 'delete_posts' );  // удалять статьи
    $role -> add_cap( 'publish_posts' ); // публиковать статьи
    $role -> add_cap( 'publish_pages' ); // публиковать страницы
    $role -> add_cap( 'edit_posts' );
    $role -> add_cap( 'iso_list' );
    $role -> add_cap( 'iso_create' );
    $role -> add_cap( 'iso_delete' );

    // administrator
    $role = get_role('administrator');
    if ( ! empty($role) ) {
       $role->add_cap('iso_list');
       $role->add_cap('iso_create');
       $role->add_cap('iso_delete');
       }
    $role = get_role('author');
    if ( ! empty($role) ) {
       $role->add_cap('iso_list');
    }
    $role = get_role('contributor');
    if ( ! empty($role) ) {
       $role->add_cap('iso_list');
    }
    $role = get_role('editor');
    if ( ! empty($role) ) {
       $role->add_cap('iso_list');
    }

//    if ( null !== $role ) {
//       echo "Роль успешно создана";
//    } else {
//       echo "Если null, то значит роль уже существует";
//    }
}

function block_directories() {
  $full_path = $_SERVER['DOCUMENT_ROOT'] . '/iso/';
  if ( file_exists($full_path) ) {
     $file = ".htaccess";
     if ( ! file_exists($full_path . $file)) {
        $fp = fopen($full_path . $file, "w");
        fwrite($fp, "Options All -Indexes");
        fclose($fp);
     }
  }
}

//if ( $installed_ver != $db_version_plug ) {
   block_directories();
   create_new_role();
//   update_option( "db_iso_management", $db_version_plug );
//}

