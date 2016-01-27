<?php
get_header();
?>
    <div id="section-breadcumn">
        <div class="row">
            <div class="large-12 columns">
                <h1>Tin tức</h1>
                <div class="breadcrumbs" xmlns:v="http://rdf.data-vocabulary.org/#">
                    <?php if(function_exists('bcn_display'))
                    {
                        bcn_display();
                    }?>
                </div>
            </div>
        </div>
    </div>
    <div id="main">
        <div class="row">
            <div class="main-container">
                <div id="content" class="content medium-9 columns" role="main">
                    <?php while(have_posts()):?>
                        <?php the_post();?>
                        <?php get_template_part('content');?>
                    <?php endwhile;?>
                    <br/>
                    <?php
                        if(function_exists('wp_pagenavi')) wp_pagenavi();
                    ?>
                    <?php wp_reset_query();?>
                </div>
                <?php get_sidebar();?>
            </div>
        </div>
    </div>
<?php get_footer();?>