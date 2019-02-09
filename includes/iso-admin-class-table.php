<?php

global $wpdb;

/************************ LOAD THE BASE CLASS ***************************/
if ( ! class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/** Create a new table class that will extend the WP_List_Table */
class class_iso_list_table extends WP_List_Table {

    /** Prepare the items for the table to process
    * @return Void */
    public function prepare_items() {
        $columns  = $this -> get_columns();
        // $hidden определяет скрытые столбцы
        $hidden   = $this -> get_hidden_columns();
        // $sortable определяет, может ли таблица быть отсортирована по этому столбцу.
        $sortable = $this -> get_sortable_columns();
        $data     = $this -> table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $PerPage = get_option('number_rows_per_page');
        $currentPage = $this -> get_pagenum();
        $totalItems  = count($data);
        $this -> set_pagination_args( array(
            // общее количество элементов
            'total_items' => $totalItems,
            // сколько элементов отображается на странице
            'per_page'    => $PerPage
        ));

//        $data = array_slice($data,(($currentPage-1)*$PerPage),$PerPage);

        $this -> _column_headers = array($columns, $hidden, $sortable);
        $this -> items = $data;

    }

    function __construct(){

       parent::__construct( array(
            'singular'  => __( 'book',  'computer-accounting' ),  //singular name of the listed records
            'plural'    => __( 'books', 'computer-accounting' ),  //plural name of the listed records
            'ajax'      => false                                  //does this table support ajax?

       ));
       add_action( 'admin_head', array( &$this, 'admin_header' ) );
    }

    function admin_header() {
       $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
       if ( 'iso-page' != $page )
          return;
       echo '<style type="text/css">';
       echo '.wp-list-table .column-name { width: 100%; }';
       echo '.wp-list-table .column-md5sum { width: 100%; }';
       echo '</style>';
    }

    /**********************************/
    function no_items() {
       _e( 'There is not a single value.', 'iso-management-snuglinux');
    }

    /*** Определяет столбцы, которые будут использоваться в вашей таблице
     * @return Array */
    public function get_columns() {
        $columns = array(
            'name'    => __( 'Name', 'iso-management-snuglinux' ),
            'md5sum'  => __( 'md5sum', 'iso-management-snuglinux' )
        );
        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    /*** Определить сортируемые столбцы.
     * @return Array */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'    => array('name', true),
            'md5sum'  => array('md5sum', false)
        );
        return $sortable_columns;
    }

    private function table_data() {
        global $wpdb, $iso_table_list;

        $path = 'iso';
        $data = array();
        if ( $path [ strlen( $path ) - 1 ] != '/' ) {
           $path .= '/';
        }
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/'. $path;
        if ( file_exists($full_path) == false ) {
           _e( "The $full_path path does not exist!", 'iso-management-snuglinux' );
           return $data;
        }
        $server_name = $_SERVER['SERVER_NAME'];
        if ( $server_name [ strlen( $server_name ) - 1 ] != '/' ) {
           $server_name .= '/';
        }

        $iso_list_array = $wpdb -> get_results( "SELECT * FROM " . $iso_table_list );
        $data = array();
        if( $iso_list_array ) {
            foreach ( $iso_list_array as $in ) {
                $name        = $in -> name;
                $md5sum      = $in -> md5sum;
                $file_iso    = $in -> file_iso;
                $file_md5sum = $in -> file_md5sum;
                $link        = 'https://' . $server_name . $path . $file_iso;
                $link_file   = '<a href = ' . $link . '>' . $file_iso . '</a> <br>';

                $data[] = array(
                'name'        => $name,
                'md5sum'      => $md5sum,
                'file_iso'    => $file_iso,
                'file_md5sum' => $file_md5sum,
                'link'        => $link_file
                );
             }
        }
        return $data;
    }

    /** Определите, какие данные будут отображаться в каждом столбце таблицы.
     * @param  Array $item  Data
     * @param  String $column_name - Current column name
     * @return Mixed */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'name':
                return $item[ $column_name ];
            case 'md5sum':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

  /*** Определение действий для поля name*/
    function column_name($item){
      if ( current_user_can( 'iso_delete' )) {
         $actions = array(
             'download'  => sprintf('<a href="?page=%s&%s&iso=%s">' . __( 'Download', 'iso-management-snuglinux' ) . '</a>', $_REQUEST['page'], 'download', $item['name']),
             'delete'    => sprintf('<a href="?page=%s&%s&iso=%s">' . __( 'Delete', 'iso-management-snuglinux' ) . '</a>', $_REQUEST['page'], 'delete', $item['name'])
             );
         }
         else {
         $actions = array(
             'download'  => sprintf('<a href="?page=%s&%s&iso=%s">' . __( 'Download', 'iso-management-snuglinux' ) . '</a>', $_REQUEST['page'], 'download', $item['name'])
             );
         }
       return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions) );
    }

    /** Позволяет сортировать данные по переменным, установленным в $_GET
     * @return Mixed */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'name';
        $order   = 'asc';
        // If orderby is set, use this as the sort column
        if ( ! empty( $_GET[ 'orderby' ] )) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if ( ! empty($_GET[ 'order' ])) {
            $order = $_GET[ 'order' ];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc') {
            return $result;
        }
        return -$result;
    }
} //class
