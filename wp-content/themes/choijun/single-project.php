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
                            <div id="tabs">
                                <ul class="resp-tabs-list tab_identifier_child">
                                    <li>TỔNG QUAN DỰ ÁN</li>
                                    <li>VỊ TRÍ DỰ ÁN</li>
                                    <li>TIỆN ÍCH</li>
                                    <li>MẶT BẰNG TẦNG</li>
                                    <li>BẢNG GIÁ</li>
                                    <li>THANH TOÁN</li>
                                </ul>
                                <div class="resp-tabs-container tab_identifier_child">
                                    <div><?php the_field('tong_quan_du_an')?></div>
                                    <div><?php the_field('vi_tri_du_an')?></div>
                                    <div><?php the_field('tien_ich')?></div>
                                    <div><?php the_field('mat_bang_tang')?></div>
                                    <div><?php the_field('hinh_anh')?></div>
                                    <div><?php the_field('thanh_toan')?></div>
                                </div>
                            </div>
                            <?php the_content();?>
                            <?php echo do_action('arexworks_social_share');?>
                            <?php the_tags();?>
                        </div>
                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery('#tabs').easyResponsiveTabs({
                                    type: 'default', //Types: default, vertical, accordion
                                    width: 'auto', //auto or any custom width
                                    fit: true,   // 100% fits in a container
                                    tabidentify:'tab_identifier_child',
                                    closed: 'accordion', // Close the panels on start, the options 'accordion' and 'tabs' keep them closed in there respective view types
                                    activetab_bg: '', // background color for active tabs in this group
                                    inactive_bg: '', // background color for inactive tabs in this group
                                    active_border_color: '', // border color for active tabs heads in this group
                                    active_content_border_color: '' // border color for active tabs contect in this group so that it matches the tab head border
                                });
                            })
                        </script>
                    </div>
                </div>
                <?php get_sidebar();?>
            </div>
        </div>
    </div>
<?php get_footer();?>