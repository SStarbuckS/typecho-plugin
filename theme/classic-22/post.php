<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<main class="container">
    <div class="container-thin">
        <article class="post" itemscope itemtype="http://schema.org/BlogPosting">
            <meta itemprop="datePublished" content="<?php $this->date('c'); ?>">
            <meta itemprop="dateModified" content="<?php echo date('c', $this->modified); ?>">
            <?php postMeta($this, 'post'); ?>

            <div class="entry-content fmt" itemprop="articleBody">
                <?php echo lazyLoadImages($this->content); ?>
                <p itemprop="keywords"><?php _e('标签'); ?>：<?php $this->tags(', ', true, _t('无')); ?></p>
            </div>
            
            <div class="post-copyright">
                <p>
                    《<a href="<?php $this->permalink(); ?>" target="_blank" rel="noopener"><?php $this->title(); ?></a>》
                    © <?php echo date('Y', $this->created); ?> by 
                    <a href="<?php $this->options->siteUrl(); ?>" target="_blank" rel="noopener"><?php $this->options->title(); ?></a> 
                    依据 
                    <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="nofollow noopener">CC BY-SA 4.0</a> 
                    许可协议授权
                </p>
            </div>
        </article>

        <nav class="post-nav">
            <ul class="page-navigator">
                <li class="prev"><?php $this->thePrev('%s', _t('没有了')); ?></li>
                <li class="next"><?php $this->theNext('%s', _t('没有了')); ?></li>
            </ul>
        </nav>

        <?php $this->need('comments.php'); ?>
    </div>
</main>

<?php $this->need('footer.php'); ?>
