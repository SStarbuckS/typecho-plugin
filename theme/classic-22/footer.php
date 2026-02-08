<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<footer class="site-footer container-fluid">
    <div class="text-center">
        <p class="list-inline text-muted mb-0">
            &copy; <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
        </p>
        <p class="list-inline text-muted mb-0">
            <a href="<?php $this->options->feedUrl(); ?>" target="_blank" rel="noopener"><?php _e('RSS'); ?></a>
            <span style="margin: 0 0.5rem;">|</span>
            <a href="<?php $this->options->siteUrl(); ?>sitemap.xml" target="_blank" rel="noopener">Sitemap</a>
            <span style="margin: 0 0.5rem;">|</span>
            <?php _e('Powered by <a href="https://typecho.org" target="_blank" rel="nofollow noopener">Typecho</a>'); ?>
        </p>
        <?php if ($this->options->beian): ?>
        <p class="list-inline text-muted mb-0">
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener"><?php $this->options->beian(); ?></a>
        </p>
        <?php endif; ?>
    </div>
</footer>

<?php $this->footer(); ?>

</body>
</html>
