<?php
function arexworks_create_post_type_projects() {

    $labels = array(
        'name'                => _x( 'Dự án', 'Post Type General Name', 'text_domain' ),
        'singular_name'       => _x( 'Dự án', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'           => __( 'Dự án', 'text_domain' ),
    );
    $rewrite = array(
        'slug'                => 'du-an',
        'with_front'          => true,
        'pages'               => true,
        'feeds'               => true,
    );
    $args = array(
        'label'               => __( 'project', 'text_domain' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
        'taxonomies'          => array( 'project_category', 'post_tag' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-admin-post',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'rewrite'             => $rewrite,
        'capability_type'     => 'page',
    );
    register_post_type( 'project', $args );

}
add_action( 'init', 'arexworks_create_post_type_projects', 0 );

function arexworks_create_taxonomy_project_category() {

    $labels = array(
        'name'                       => _x( 'Danh sách dự án', 'Taxonomy General Name', 'text_domain' ),
        'singular_name'              => _x( 'Danh sách dự án', 'Taxonomy Singular Name', 'text_domain' )
    );
    $rewrite = array(
        'slug'                       => 'danh-sach-du-an',
        'with_front'                 => true,
        'hierarchical'               => true,
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'rewrite'                    => $rewrite,
    );
    register_taxonomy( 'project_category', array( 'project' ), $args );

}
add_action( 'init', 'arexworks_create_taxonomy_project_category', 0 );
