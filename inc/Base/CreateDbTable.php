<?php
namespace JobPressInc\Base;

class CreateDbTable
{
	public static function create_application_db_table() {

		/***
		**Create database table on plugin activation time
		***/
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
    
        $jobpress_application_table_name = $wpdb->prefix . 'jobpress_application';
        $jobpress_application_sql = "CREATE TABLE $jobpress_application_table_name (
            id int(9) NOT NULL AUTO_INCREMENT,
            fullname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            resume varchar(255) NOT NULL,
            message text NULL,
            ip_address varchar(255) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $jobpress_application_sql );
	}
}