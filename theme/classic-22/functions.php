<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form)
{
    $logoUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        null,
        _t('网站 Logo'),
        _t('在这里填写图片 URL，网站将显示 Logo')
    );

    $form->addInput($logoUrl->addRule('url', _t('请填写正确的 URL 地址')));

    $colorSchema = new \Typecho\Widget\Helper\Form\Element\Select(
        'colorSchema',
        array(
            null => _t('自动'),
            'light' => _t('浅色'),
            'dark' => _t('深色'),
        ),
        null,
        _t('外观风格'),
        _t('根据系统设置或手动选择浅色/深色主题')
    );

    $form->addInput($colorSchema);

    $beian = new \Typecho\Widget\Helper\Form\Element\Text(
        'beian',
        null,
        null,
        _t('网站备案号'),
        _t('在这里填写网站备案号，例如：京ICP备12345678号-1')
    );

    $form->addInput($beian);

    $excerptLength = new \Typecho\Widget\Helper\Form\Element\Text(
        'excerptLength',
        null,
        '70',
        _t('文章摘要长度'),
        _t('设置首页和搜索页面显示的文章摘要字符数，默认为70')
    );

    $form->addInput($excerptLength);
}

function postBreadcrumb(\Widget\Archive $archive)
{
    $options = \Typecho\Widget::widget('Widget_Options');
    $position = 1;
?>
    <span itemscope itemtype="https://schema.org/BreadcrumbList">
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="<?php echo $position++; ?>" />
            <a itemprop="item" href="<?php $options->siteUrl(); ?>">
                <span itemprop="name"><?php _e('首页'); ?></span>
            </a>
        </span> › <?php if ($archive->categories): ?>
            <?php foreach ($archive->categories as $category): ?>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="<?php echo $position++; ?>" />
                <a itemprop="item" href="<?php echo $category['permalink']; ?>">
                    <span itemprop="name"><?php echo $category['name']; ?></span>
                </a>
            </span> › <?php endforeach; ?>
        <?php endif; ?>
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="<?php echo $position; ?>" />
            <span itemprop="name"><?php $archive->title(); ?></span>
        </span>
    </span>
<?php
}

function postMeta(
    \Widget\Archive $archive,
    string $metaType = 'archive'
)
{
    $options = \Typecho\Widget::widget('Widget_Options');
?>
    <header class="entry-header text-center">
        <h1 class="entry-title" itemprop="name headline">
            <a href="<?php $archive->permalink() ?>" itemprop="url"><?php $archive->title() ?></a>
        </h1>
        <?php if ($metaType != 'page'): ?>
        <ul class="entry-meta list-inline text-muted">
            <li class="feather-calendar"><time datetime="<?php $archive->date('c'); ?>" itemprop="datePublished"><?php $archive->date(); ?></time></li>
            <li class="feather-user"><span itemprop="author" itemscope itemtype="http://schema.org/Person"><a itemprop="url" href="<?php $options->siteUrl(); ?>"><span itemprop="name"><?php $archive->author(); ?></span></a></span></li>
            <li class="feather-message"><a href="<?php $archive->permalink() ?>#comments"  itemprop="discussionUrl"><?php $archive->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></a></li>
        </ul>
        <?php if ($metaType == 'post' && $archive->categories): ?>
        <div class="text-muted" style="font-size: 0.875rem; margin-top: 0.5rem;">
            当前位置：<?php postBreadcrumb($archive); ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </header>
<?php
}

function postExcerpt(\Widget\Archive $archive, int $length = 70)
{
    echo '<a href="' . $archive->permalink . '" class="excerpt-link">';
    echo '<p>';
    $archive->excerpt($length, '......');
    echo '</p>';
    echo '</a>';
    echo '<p class="more"><a href="' . $archive->permalink . '">' . _t('阅读全文') . '</a></p>';
}

function lazyLoadImages($content)
{
    // 为所有 <img> 标签添加 loading="lazy" 属性
    $content = preg_replace('/<img((?![^>]*loading=)[^>]*)>/i', '<img$1 loading="lazy">', $content);
    return $content;
}
