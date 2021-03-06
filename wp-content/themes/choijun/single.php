<?php
get_header();
the_post();
?>
    <div id="section-breadcumn">
        <div class="row">
            <div class="large-12 columns">
                <h1><?php the_title();?></h1>
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
                    <?php
                    $left_class = '';
                    $right_class = 'medium-12 columns';
                    if(get_field('hien_thi_menu',get_the_ID()) == 'yes') {
                        $left_class = 'medium-3 columns custom-content-menu-left';
                        $right_class = 'medium-9 columns';
                    }
                    ?>
                    <div class="row">
                        <?php if($left_class):?>
                            <div class="<?php echo $left_class?>">
                                <?php the_field('chon_menu');?>
                            </div>
                        <?php endif;?>
                        <div class="<?php echo $right_class;?>">
                            <?php the_content();?>
                            <?php echo do_action('arexworks_social_share');?>
                            <?php the_tags();?>
                        </div>
                    </div>
                </div>
                <?php get_sidebar();?>
            </div>
        </div>
    </div>
<?php get_footer();?>