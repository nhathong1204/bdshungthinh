<?php

// Include Posttype create
include_once "inc/create_post_type.php";
include_once "inc/shortcode.php";

add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode');

function arexworks_jpeg_quality_callback($arg) {
    return 100;
}
add_filter('jpeg_quality', 'arexworks_jpeg_quality_callback');

function arexworks_setup_theme(){

    add_theme_support( 'menus' );
    add_theme_support( 'post-thumbnails' );

    // Add Image Sizes

    add_image_size( 'large', 0, 0, true );
    add_image_size( 'medium', 0, 0, true );
    add_image_size( 'thumbnail', 160, 120, true );

    // Register menus
    register_nav_menus( array(
        'main-navigation' => __( 'Main Navigation' )
    ) );

}
add_action( 'after_setup_theme', 'arexworks_setup_theme' );

function arexworks_frontend_enqueue(){
    wp_enqueue_script('foundation',get_template_directory_uri() .'/assets/js/foundation.min.js',array('jquery'),false,true);
    wp_enqueue_script('easy-responsive-tabs',get_template_directory_uri() .'/assets/js/easyResponsiveTabs.js',array('jquery'),false,true);
    wp_enqueue_script('theme-script',get_template_directory_uri() .'/assets/js/theme.js',array('jquery'),false,true);

    wp_enqueue_style('foundation',get_template_directory_uri() . '/assets/css/foundation.css');
    wp_enqueue_style('font-awesome',get_template_directory_uri() . '/assets/css/font-awesome.min.css');
    wp_enqueue_style('easy-responsive-tabs',get_template_directory_uri() . '/assets/css/easy-responsive-tabs.css');
    wp_enqueue_style('theme-stylesheet',get_template_directory_uri() . '/assets/css/app.css');
}
add_action('wp_enqueue_scripts','arexworks_frontend_enqueue');


add_action( 'widgets_init', 'arexworks_widgets_init' );
function arexworks_widgets_init(){

    unregister_widget('WP_Widget_Calendar');

    //default sidebar
    register_sidebar(array(
        'name'          => __( 'Top bar' ),
        'id'            => 'top-bar',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __( 'Header Inner' ),
        'id'            => 'header-inner-right',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    //catalog widget area
    register_sidebar( array(
        'name'          => __( 'Sidebar Right' ),
        'id'            => 'sidebar',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    //footer second widget area
    for ( $i = 1 ; $i < 5 ; $i++){
        register_sidebar( array(
            'name'          => __( 'Footer Widget Column' ) . ' '.$i,
            'id'            => 'footer-widget-'.$i,
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }

    //catalog widget area
    register_sidebar( array(
        'name'          => __( 'Footer copyright' ),
        'id'            => 'footer-copyright',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}

add_filter( 'nav_menu_css_class', 'arexworks_add_active_class_menu', 10, 2 );
function arexworks_add_active_class_menu( $classes , $item){
    if ( $item->current || $item->current_item_ancestor || $item->current_item_parent ){
        $classes[] = 'active';
    }
    return $classes;
}


add_action('arexworks_social_share','arexworks_social_share2');
function arexworks_social_share2(){
    echo arexworks_social_share(array());
}
add_shortcode('social_share','arexworks_social_share');
function arexworks_social_share($atts,$content = null){
    ob_start();
    $uid = uniqid();
    $data_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];?>
    <ul id="social_share_<?php echo $uid;?>" class="social_share inline-list">
        <li>
            <div class="fb-like" data-share="false" data-href="<?php echo $data_url;?>" data-send="false" data-layout="button_count" data-width="50" data-show-faces="true" data-font="arial"></div>
        </li>
        <li>
            <a href="http://twitter.com/share?u=<?php echo $data_url;?>" class="twitter-share-button"></a>
        </li>
        <li>
            <g:plusone size="medium"></g:plusone>
        </li>
    </ul>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            if(jQuery('#social_share_<?php echo $uid;?>').closest('.widget').length > 0){
                jQuery('#social_share_<?php echo $uid;?>').closest('.widget').addClass('widget-no-padding');
            }
        })
    </script>
<?php
    return ob_get_clean();
}
//add_action('arexworks_social_share','arexworks_facebook_comment');
function arexworks_facebook_comment(){
    global $post;
    echo '<div class="fb-comments" data-href="'.get_permalink($post->ID).'" data-width="100%" data-numposts="10" data-colorscheme="light"></div>';
}

add_filter( 'widget_tag_cloud_args', 'arexworks_tag_cloud_args' );
function arexworks_tag_cloud_args( $args ) {
    $args['largest'] = 12;
    $args['smallest'] = 12;
    $args['unit'] = 'px';
    return $args;
}