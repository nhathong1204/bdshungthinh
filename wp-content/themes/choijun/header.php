<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="top-bar">
        <?php dynamic_sidebar('top-bar');?>
        <span class="show-for-small"><i class="fa fa-angle-up"></i></span>
    </div>
    <header id="header" class="logo-classic" role="banner">
        <div class="row">
            <div class="column">
                <div class="header-wrapper">
                    <div class="site-logo">
                        <?php $logo_url = get_template_directory_uri() . '/assets/images/logo.png';?>
                        <a href="<?php echo home_url('/')?>" title="<?php bloginfo('name')?>">
                            <img src="<?php echo $logo_url?>" alt="<?php bloginfo('name')?>"/>
                        </a>
                    </div>
                    <div class="header-inner-inner">
			<?php $slogan_url = get_template_directory_uri() . '/assets/images/slogan.png';?>
			<img src="<?php echo $slogan_url?>" alt="slogan"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav-mobile show-for-small">
            <div id="dl-menu" class="dl-menuwrapper">
                <a href="#show-menu" rel="nofollow" id="mobile-menu">
                    <span class="menu-open">MENU</span>
                    <span class="menu-close">CLOSE</span>
                    <span class="menu-back">back</span>
                </a>
            </div>
        </div>
        <div class="navigation-holder">
            <div class="row">
                <div class="large-12 columns hide-for-small">
                    <div class="mini-search">
                        <?php get_search_form();?>
                    </div>
                    <?php
                        wp_nav_menu(array(
                            'theme_location' => 'main-navigation',
                            'container' => false
                        ));
                    ?>
                </div>
            </div>
        </div>
    </header>