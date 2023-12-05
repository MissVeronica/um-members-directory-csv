<?php
/**
 * Plugin Name:     Ultimate Member - Members Directory CSV 
 * Description:     Extension to Ultimate Member for defining Members Directory primary user list order from a spreadsheet saved as a CSV file.
 * Version:         1.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class Members_Directory_CSV {

    public $member_user_ids = false;
    public $member_list_all_users = array();
    public $query_args_role__in = array();

    function __construct() {

        add_filter( 'um_settings_structure',            array( $this, 'um_settings_structure_csv' ), 99, 1 );
        add_filter( 'um_members_directory_sort_fields', array( $this, 'um_members_directory_sort_fields_csv' ), 10, 1 );

        add_filter( 'um_prepare_user_query_args',       array( $this, 'um_prepare_user_query_args_directories' ), 10, 2 );
        add_action( 'um_prepare_user_query_args',       array( $this, 'um_prepare_user_query_args_csv' ), 10, 2 );
    }

    public function um_prepare_user_query_args_directories( $query_args, $directory_data ) {

        if ( $directory_data['form_id'] == UM()->options()->get( 'csv_users_form_id' )) {

            $this->query_args_role__in = $query_args['role__in'];

            $this->get_member_user_ids_list();
            $query_args['include'] = $this->member_user_ids;
        }
        return $query_args;
    }

    public function um_prepare_user_query_args_csv( $query_args, $directory_data ) {

        if ( $directory_data['form_id'] == UM()->options()->get( 'csv_users_form_id' )) {
            if ( $query_args['orderby'] == 'csv_file' ) {

                unset( $query_args['order'], $query_args['orderby'] );
                $this->query_args_role__in = $query_args['role__in'];

                $this->get_member_user_ids_list();

                add_filter( 'um_prepare_user_results_array', array( $this, 'um_prepare_user_results_array_csv_sorting' ), 10, 2 );
            }
        }
        return $query_args;
    }

    public function um_prepare_user_results_array_csv_sorting( $user_ids, $query_args ) {

        if ( is_array( $this->member_user_ids )) {
            if ( isset( $query_args['paged'] ) && isset( $query_args['number'] )) {

                $page_start = ( $query_args['paged'] - 1 ) * $query_args['number'];
                $user_ids = array_slice( $this->member_user_ids, $page_start, $query_args['number'], true );
            }
        }
        return $user_ids;
    }

    public function um_members_directory_sort_fields_csv( $sort_fields ) {

        $sort_fields['csv_file'] = __( 'Primary Users list from CSV File or UM settings', 'ultimate-member' );
        return $sort_fields;
    }

    public function get_member_user_ids_list() {

        $this->get_secondary_user_list();
        $csv_file_users_list = $this->get_csv_file_users_list();

        $this->member_user_ids = array_merge( $csv_file_users_list, array_keys( $this->member_list_all_users ) );

        if ( empty ( $this->member_user_ids )) {
            $this->member_user_ids = array( false );
        }
    }

    public function get_secondary_user_list() {

        $orderby = sanitize_text_field( UM()->options()->get( 'csv_users_orderby' ));
        if ( empty( $orderby )) {
            $orderby = 'user_login';
        }

        switch( $orderby ) {
            case 'user_login':
            case 'first_name':
            case 'last_name':
            case 'user_nicename':
            case 'display_name':    $args = $orderby; break;
            case 'registered_asc':  $args = 'user_registered&order=ASC'; break;
            case 'registered_desc': $args = 'user_registered&order=DESC'; break;
            default:                $args = 'user_login'; break;
        }

        $this->member_list_all_users = array();

        $all_wp_users = get_users( 'orderby=' . $args );

        foreach ( $all_wp_users as $user ) {
            if (( isset( $user->role ) && in_array( $user->role, $this->query_args_role__in )) ||
                ( is_array( $user->wp1e_capabilities ) && in_array( array_key_first( $user->wp1e_capabilities ), $this->query_args_role__in )) ) {

                $this->member_list_all_users[$user->ID] = $user->user_login;
            }
        }
    }

    public function get_csv_file_users_list() {

        $priority_user_ids = array();
        $csv_content = '';

        if ( UM()->options()->get( 'csv_users_input' ) == 0 ) {

            $csv_file_name = sanitize_text_field( UM()->options()->get( 'csv_users_file_name' ));
            $csv_file = WP_CONTENT_DIR . '/uploads/ultimatemember/' . $csv_file_name;

            if ( file_exists( $csv_file ) && is_file( $csv_file )) {

                $csv_content = file_get_contents( $csv_file );
            }
        }

        if ( UM()->options()->get( 'csv_users_input' ) == 1 ) {

            $csv_content = UM()->options()->get( 'csv_users_textarea' );
        }

        if ( ! empty( $csv_content )) {

            $separator  = strpos( $csv_content, ';' ) ? $separator = ';' : $separator = ',';
            $terminator = strpos( $csv_content, "\n" ) ? $terminator = "\n" : $terminator = "\r";

            $csv_contents = array_map( 'sanitize_text_field', array_map( 'trim', explode( $terminator, $csv_content )));
            if ( is_array( $csv_contents ) && ! empty( $csv_contents )) {

                foreach( $csv_contents as $csv_row ) {
                    $csv_row_items = array_map( 'sanitize_text_field', array_map( 'trim', explode( $separator, str_replace( array( '"', "'" ), '', $csv_row ))));

                    if ( is_array( $csv_row_items ) && isset( $csv_row_items[0] ) && ! empty( $csv_row_items[0] )) {

                        if ( array_key_exists( $csv_row_items[0], $this->member_list_all_users )) {
                            unset( $this->member_list_all_users[$csv_row_items[0]] );
                            $priority_user_ids[] = $csv_row_items[0];
                        }
                    }
                }
            }
        }

        return array_unique( $priority_user_ids );
    }

    public function um_settings_structure_csv( $settings_structure ) {

        $um_directory_forms = get_posts( array( 'numberposts' => -1,
                                                'post_type'   => 'um_directory',
                                                'post_status' => 'publish'
                                            ));

        $directory_forms['none'] = '';
        foreach( $um_directory_forms as $um_form ) {
            $directory_forms[$um_form->ID] = $um_form->post_title;
        }

        $settings_structure['']['sections']['users']['fields'][] = array(
                                'id'      => 'csv_users_form_id',
                                'type'    => 'select',
                                'options' => $directory_forms,
                                'label'   => __( 'Members Directory CSV - Form name', 'ultimate-member' ),
                                'tooltip' => __( 'Select the Member Directory Form name for Primary/Secondary User listing', 'ultimate-member' ),
                                'size'    => 'medium',
                            );

        $settings_structure['']['sections']['users']['fields'][] = array(
                                'id'          => 'csv_users_input',
                                'type'        => 'checkbox',
                                'label'       => __( 'Members Directory CSV - User IDs input', 'ultimate-member' ),
                                'tooltip'     => __( 'Select the Member Directory input from CSV file name or Textbox for primary user IDs listing order', 'ultimate-member' ),
                                
                            );                    

        $settings_structure['']['sections']['users']['fields'][] = array(
                                'id'          => 'csv_users_file_name',
                                'type'        => 'text',
                                'label'       => __( 'Members Directory CSV - Primary Order User IDs by CSV file', 'ultimate-member' ),
                                'tooltip'     => __( 'Enter the Member Directory CSV file name for primary user listing order', 'ultimate-member' ),
                                'size'        => 'medium',
                                'conditional' => array( 'csv_users_input', '=', 0 ),
                            );

        $settings_structure['']['sections']['users']['fields'][] = array(
                                'id'          => 'csv_users_textarea',
                                'type'        => 'textarea',
                                'label'       => __( 'Members Directory CSV - Primary Order User IDs by UM list', 'ultimate-member' ),
                                'tooltip'     => __( 'Enter the Member Directory list for Primary User listing order. One user ID per line with optional username and comment separated by comma or semicolon.', 'ultimate-member' ),
                                'size'        => 'medium',
                                'args'        => array( 'textarea_rows' => 10 ),
                                'conditional' => array( 'csv_users_input', '=', 1 ),
                            );

        $settings_structure['']['sections']['users']['fields'][] = array(
                                'id'      => 'csv_users_orderby',
                                'type'    => 'select',
                                'options' => array( 'user_login'      => __( 'Username', 'ultimate-member' ),
                                                    'registered_asc'  => __( 'Old users first', 'ultimate-member' ),
                                                    'registered_desc' => __( 'New users first', 'ultimate-member' ),
                                                    'display_name'    => __( 'Display name', 'ultimate-member' ),
                                                    'first_name'      => __( 'First name', 'ultimate-member' ),
                                                    'last_name'       => __( 'Last name', 'ultimate-member' ),
                                                    'user_nicename'   => __( 'Nickname', 'ultimate-member' ),
                                                    ),
                                'label'   => __( 'Members Directory CSV - Secondary Order sorting Users by', 'ultimate-member' ),
                                'tooltip' => __( 'Select the Member Directory Secondary sorting order except Primary Users', 'ultimate-member' ),
                                'size'    => 'medium',
                            );

        return $settings_structure;
    }

}

new Members_Directory_CSV();
