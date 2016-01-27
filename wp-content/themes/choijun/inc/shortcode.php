<?php
add_shortcode('show_project','arexworks_create_shortcode_show_project');
function arexworks_create_shortcode_show_project($atts,$content = null){
    $columns = $small_columns = $medium_columns = $category = $posts_per_page = $orderby = $order = $include = $exclude = '';
    extract(shortcode_atts(array(
        'posts_per_page'   => '9',
        'columns'          => '3',
        'small_columns'    => '1',
        'medium_columns'   => '3',
        'category'         => '',
        'orderby'          => 'date',
        'order'            => 'DESC',
        'include'          => '',
        'exclude'          => ''
    ),$atts));
    if($include){
        $include = explode( ',', $include );
        $include = array_map( 'trim', $include );
    }
    if($exclude){
        $exclude = explode( ',', $exclude );
        $exclude = array_map( 'trim', $exclude );
    }
    if($category){
        $category = explode( ',', $category );
        $category = array_map( 'trim', $category );
    }
    $args = array(
        'post_type'        => 'project',
        'posts_per_page'   => $posts_per_page,
        'orderby'          => $orderby,
        'order'            => $order,
        'post__in'         => $include,
        'post__not_in'     => $exclude
    );
    if($category){
        $args['tax_query'] = array(
            array(
                'taxonomy' 	 => 'project_category',
                'terms' 		=> $category,
                'field' 		=> 'term_id',
                'operator' 	 => 'IN'
            )
        );
    }
    ob_start();
    $the_query = new WP_Query($args);
    echo "<div class=\"project-gird\">";
    echo "<ul class=\"block-grid-$columns medium-block-grid-$medium_columns small-block-grid-$small_columns\">";
    while($the_query->have_posts()){
        $the_query->the_post();
        get_template_part('content','project');
    }
    echo "</ul>";
    echo "</div>";
    wp_reset_query();
    return ob_get_clean();
}
?>