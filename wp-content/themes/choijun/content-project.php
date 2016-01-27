<li>
    <div class="project-inner">
        <h3>
            <a href="<?php the_permalink()?>" title="<?php the_title()?>">
                <?php the_title();?>
            </a>
        </h3>
        <div class="project-image">
            <a href="<?php the_permalink()?>" title="<?php the_title()?>">
                <?php if(has_post_thumbnail()):?>
                    <?php the_post_thumbnail('thumbnail');?>
                <?php else:?>
                    <img src="<?php echo get_template_directory_uri()?>/assets/images/default-image.png" alt=""/>
                <?php endif;?>
            </a>
        </div>
        <div class="project-meta">
            <p><em>Vị trí : </em><span><?php the_field('vi_tri')?></span></p>
            <p><em>Giá : </em><span><?php the_field('gia')?></span></p>
            <p><em>Giao nhà : </em><span><?php the_field('thoi_han_giao_nha')?></span></p>
        </div>
    </div>
</li>