<article <?php post_class()?>>
    <div class="blog-content">
        <h2 class="entry-title">
            <a rel="bookmark" title="<?php the_title()?>" href="<?php the_permalink()?>"><?php the_title()?></a>
        </h2>
        <?php the_excerpt();?>
        <a rel="nofollow" class="details more-link" href="<?php the_permalink();?>">Xem chi tiết</a>
    </div>
</article>