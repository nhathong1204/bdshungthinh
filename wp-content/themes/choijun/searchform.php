<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="screen-reader-text" for="s"><?php _e( 'Search for' ); ?></label>
    <input type="search" class="search-field" placeholder="<?php _e( 'Search &hellip;' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
    <button type="submit" class="search-submit"><?php _e( 'Search' ); ?></button>
    <a href="#go"><i class="fa fa-search"></i></a>
</form>