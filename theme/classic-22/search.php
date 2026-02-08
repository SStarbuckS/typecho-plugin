<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<main class="container">
    <div class="container-thin">

        <h1 class="text-center"><?php _e('搜索'); ?></h1>
        
        <form method="post" action="<?php $this->options->siteUrl(); ?>">
            <input type="search" id="s" name="s" placeholder="<?php _e('搜索关键字'); ?>" value="<?php $this->archiveTitle('','',''); ?>">
        </form>

        <div class="text-center">
            <?php \Widget\Metas\Category\Rows::alloc()->listCategories('wrapClass=list-inline'); ?>
        </div>
    
        <hr class="post-separator">

    <?php if ($this->have()): ?>
        <?php 
        // 读取摘要长度配置
        $excerptLen = $this->options->excerptLength ? intval($this->options->excerptLength) : 70;
        ?>
        
        <?php while ($this->next()): ?>
        <article class="post" itemscope itemtype="http://schema.org/BlogPosting">
            <meta itemprop="url" content="<?php $this->permalink(); ?>">
            <?php postMeta($this); ?>
            
            <div class="entry-content fmt" itemprop="articleBody">
                <?php postExcerpt($this, $excerptLen); ?>
            </div>
        </article>
        <hr class="post-separator">
        <?php endwhile; ?>
    <?php else: ?>
        <article class="post">
            <div class="entry-content fmt text-center" itemprop="articleBody">
                <p><?php _e('没有找到内容'); ?></p>
            </div>
        </article>
    <?php endif; ?>
    
    <nav><?php $this->pageNav(_t('前一页'), _t('后一页'), 2, '...', array('wrapTag' => 'ul', 'itemTag' => 'li')); ?></nav>
    </div>

</main>

<?php $this->need('footer.php'); ?>
