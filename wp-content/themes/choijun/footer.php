<footer id="footer" class="footer">
    <div class="footer-wrapper">
        <div class="row">
            <div class="medium-3 columns">
                <?php dynamic_sidebar('footer-widget-1');?>
            </div>
            <div class="medium-3 columns">
                <?php dynamic_sidebar('footer-widget-2');?>
            </div>
            <div class="medium-3 columns">
                <?php dynamic_sidebar('footer-widget-3');?>
            </div>
            <div class="medium-3 columns">
                <?php dynamic_sidebar('footer-widget-4');?>
            </div>
        </div>
    </div>
</footer>
<div id="bottom-bar" role="contentinfo">
    <?php dynamic_sidebar('footer-copyright');?>
</div>
<div id="fb-root"></div>
<a class="scroll-top" href="#"></a>
<?php wp_footer();?>
<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/vi_VN/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<script type="text/javascript">
    (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        po.src = 'https://apis.google.com/js/plusone.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
    })();
</script>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.0";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
</body>
</html>