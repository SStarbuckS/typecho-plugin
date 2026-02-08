<?php
/**
 * Just another official theme
 *
 * @package Classic 22
 * @author Typecho Team
 * @version 1.0
 * @link http://typecho.org
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<main class="container">
    <div class="container-thin">
        <?php if (!($this->is('index')) and !($this->is('post'))): ?>
            <h6 class="text-center text-muted">
                <?php $this->archiveTitle([
                    'category' => _t('分类 %s 下的文章'),
                    'search'   => _t('包含关键字 %s 的文章'),
                    'tag'      => _t('标签 %s 下的文章'),
                    'author'   => _t('%s 发布的文章')
                ], '', ''); ?>
            </h6>
        <?php endif; ?>

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

        <nav><?php $this->pageNav(_t('前一页'), _t('后一页'), 2, '...', array('wrapTag' => 'ul', 'itemTag' => 'li')); ?></nav>
    </div>

</main>

<?php $this->need('footer.php'); ?>
