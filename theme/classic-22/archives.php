<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 归档页面
 *
 * @package custom
 */
?>
<?php $this->need('header.php'); ?>

<main class="container">
    <div class="container-thin">
        <article class="post" itemscope itemtype="http://schema.org/BlogPosting">
            <header class="entry-header text-center">
                <h1 class="entry-title" itemprop="name headline"><?php $this->title() ?></h1>
            </header>

            <div class="entry-content fmt" itemprop="articleBody">
                <?php $this->content(); ?>
                
                <div class="archives-container">
                    <?php
                    $db = Typecho_Db::get();
                    $options = Typecho_Widget::widget('Widget_Options');
                    
                    // 获取所有已发布的文章，按时间倒序
                    $posts = $db->fetchAll($db->select()->from('table.contents')
                        ->where('table.contents.type = ?', 'post')
                        ->where('table.contents.status = ?', 'publish')
                        ->order('table.contents.created', Typecho_Db::SORT_DESC));
                    
                    if (count($posts) > 0) {
                        $currentYear = 0;
                        $currentMonth = 0;
                        $postCount = 0;
                        
                        foreach ($posts as $post) {
                            $year = date('Y', $post['created']);
                            $month = date('m', $post['created']);
                            $postCount++;
                            
                            // 年份标题
                            if ($year != $currentYear) {
                                if ($currentYear > 0) {
                                    echo '</ul></div>'; // 关闭上一个月份
                                }
                                if ($currentMonth > 0) {
                                    $currentMonth = 0;
                                }
                                
                                echo '<div class="archive-year">';
                                echo '<h3 class="year-title">' . $year . '</h3>';
                                $currentYear = $year;
                            }
                            
                            // 月份标题
                            if ($month != $currentMonth) {
                                if ($currentMonth > 0) {
                                    echo '</ul></div>'; // 关闭上一个月份
                                }
                                
                                $monthNames = [
                                    '01' => '一月', '02' => '二月', '03' => '三月',
                                    '04' => '四月', '05' => '五月', '06' => '六月',
                                    '07' => '七月', '08' => '八月', '09' => '九月',
                                    '10' => '十月', '11' => '十一月', '12' => '十二月'
                                ];
                                
                                echo '<div class="archive-month">';
                                echo '<h4 class="month-title">' . $monthNames[$month] . '</h4>';
                                echo '<ul class="post-list">';
                                $currentMonth = $month;
                            }
                            
                            // 文章列表项
                            $permalink = Typecho_Router::url('post', 
                                array('cid' => $post['cid'], 'slug' => $post['slug']), 
                                $options->index);
                            
                            echo '<li class="archive-post-item">';
                            echo '<span class="post-date">' . date('m-d', $post['created']) . '</span>';
                            echo '<a href="' . $permalink . '" class="post-link" target="_blank" rel="noopener">' . htmlspecialchars($post['title']) . '</a>';
                            echo '</li>';
                        }
                        
                        // 关闭最后一个月份和年份
                        if ($currentMonth > 0) {
                            echo '</ul></div>';
                        }
                        if ($currentYear > 0) {
                            echo '</div>';
                        }
                        
                        // 统计信息
                        echo '<div class="archive-stats">';
                        echo '<p>共 ' . $postCount . ' 篇文章</p>';
                        echo '</div>';
                        
                    } else {
                        echo '<p class="text-center text-muted">暂无文章</p>';
                    }
                    ?>
                </div>
            </div>
        </article>
    </div>
</main>

<style>
.archives-container {
    margin-top: 2rem;
}

.archive-year {
    margin-bottom: 2rem;
}

.year-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 1.5rem 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--pico-primary);
    color: var(--pico-primary);
}

.archive-month {
    margin-bottom: 1.5rem;
}

.month-title {
    font-size: 1.1rem;
    font-weight: 500;
    margin: 1rem 0 0.5rem 1rem;
    color: var(--pico-secondary);
}

.post-list {
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0 2rem;
}

.archive-post-item {
    margin: 0.5rem 0;
    padding: 0.3rem 0;
    display: flex;
    align-items: baseline;
}

.post-date {
    display: inline-block;
    min-width: 50px;
    font-size: 0.9rem;
    color: var(--pico-muted-color);
    margin-right: 1rem;
    font-family: 'Courier New', monospace;
}

.post-link {
    color: var(--pico-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.post-link:hover {
    color: var(--pico-primary);
}

.archive-stats {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--pico-muted-border-color);
    text-align: center;
    color: var(--pico-muted-color);
    font-size: 0.9rem;
}

/* 深色模式适配 */
@media (prefers-color-scheme: dark) {
    .year-title {
        border-bottom-color: var(--pico-primary);
    }
}

/* 移动端优化 */
@media (max-width: 768px) {
    .year-title {
        font-size: 1.3rem;
    }
    
    .month-title {
        font-size: 1rem;
        margin-left: 0.5rem;
    }
    
    .post-list {
        margin-left: 1rem;
    }
    
    .archive-post-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .post-date {
        margin-bottom: 0.2rem;
        font-size: 0.85rem;
    }
}
</style>

<?php $this->need('footer.php'); ?> 