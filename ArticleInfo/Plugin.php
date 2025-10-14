<?php
/**
 * 文章信息显示插件
 * 
 * @package ArticleInfo
 * @author cxuxrxsxoxr
 * @version 1.0.0
 * @link http://www.typecho.org
 */
class ArticleInfo_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array('ArticleInfo_Plugin', 'footer');
        Typecho_Plugin::factory('Widget_Archive')->header = array('ArticleInfo_Plugin', 'header');
        return '插件已启用，文章页将显示更新时间和加载时间';
    }
    
    /**
     * 禁用插件方法
     */
    public static function deactivate()
    {
        return '插件已禁用';
    }
    
    /**
     * 获取插件配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 显示位置
        $position = new Typecho_Widget_Helper_Form_Element_Radio(
            'position',
            array(
                'top' => '文章顶部',
                'bottom' => '文章底部',
                'both' => '顶部和底部'
            ),
            'both',
            '显示位置',
            '选择信息显示的位置'
        );
        $form->addInput($position);
        
        // 显示更新时间
        $showUpdateTime = new Typecho_Widget_Helper_Form_Element_Radio(
            'showUpdateTime',
            array(
                'true' => '显示',
                'false' => '隐藏'
            ),
            'true',
            '显示更新时间',
            '是否显示文章最后更新时间'
        );
        $form->addInput($showUpdateTime);
        
        // 显示加载时间
        $showLoadTime = new Typecho_Widget_Helper_Form_Element_Radio(
            'showLoadTime',
            array(
                'true' => '显示',
                'false' => '隐藏'
            ),
            'true',
            '显示加载时间',
            '是否显示页面加载时间'
        );
        $form->addInput($showLoadTime);
        
        // 显示字数统计
        $showWordCount = new Typecho_Widget_Helper_Form_Element_Radio(
            'showWordCount',
            array(
                'true' => '显示',
                'false' => '隐藏'
            ),
            'true',
            '显示字数统计',
            '是否显示文章字数统计'
        );
        $form->addInput($showWordCount);
        
        // 更新时间格式
        $timeFormat = new Typecho_Widget_Helper_Form_Element_Text(
            'timeFormat',
            NULL,
            'Y-m-d',
            '时间格式',
            '设置时间显示格式（PHP date 函数格式）'
        );
        $form->addInput($timeFormat);
        
        // 主题颜色
        $themeColor = new Typecho_Widget_Helper_Form_Element_Text(
            'themeColor',
            NULL,
            '#666',
            '文字颜色',
            '设置信息文字的颜色（16进制颜色代码）'
        );
        $form->addInput($themeColor);
        
        // 背景颜色
        $bgColor = new Typecho_Widget_Helper_Form_Element_Text(
            'bgColor',
            NULL,
            '#f5f5f5',
            '背景颜色',
            '设置信息框的背景颜色（16进制颜色代码）'
        );
        $form->addInput($bgColor);
    }
    
    /**
     * 个人用户的配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }
    
    /**
     * 在页面头部输出CSS
     */
    public static function header()
    {
        $options = Helper::options()->plugin('ArticleInfo');
        if (!$options) {
            return;
        }
        
        $themeColor = $options->themeColor;
        $bgColor = $options->bgColor;
        
        echo '<style>
/* 文章信息容器 */
.article-info-box {
    background: ' . $bgColor . ';
    border-left: 3px solid ' . $themeColor . ';
    padding: 12px 16px;
    margin: 20px 0;
    border-radius: 4px;
    font-size: 14px;
    color: ' . $themeColor . ';
    line-height: 1.8;
}

.article-info-item {
    display: inline-block;
    margin-right: 20px;
    margin-bottom: 5px;
}

.article-info-label {
    font-weight: 600;
    margin-right: 4px;
}

.article-info-value {
    font-family: "Courier New", monospace;
}

/* 移动端适配 */
@media screen and (max-width: 768px) {
    .article-info-box {
        font-size: 13px;
        padding: 10px 12px;
    }
    
    .article-info-item {
        display: block;
        margin-right: 0;
        margin-bottom: 8px;
    }
}

/* 深色模式适配 */
@media (prefers-color-scheme: dark) {
    .article-info-box {
        background: #1e1e1e;
        border-left-color: ' . $themeColor . ';
        color: #b1b1b1;
    }
    
    .article-info-label {
        color: #e1e1e1;
    }
    
    .article-info-value {
        color: #b1b1b1;
    }
}
</style>';
    }
    
    /**
     * 在页面底部输出JavaScript
     */
    public static function footer()
    {
        $widget = Typecho_Widget::widget('Widget_Archive');
        
        // 只在文章页面显示，排除独立页面
        if (!$widget->is('post')) {
            return;
        }
        
        $options = Helper::options()->plugin('ArticleInfo');
        if (!$options) {
            return;
        }
        
        $position = $options->position;
        $showUpdateTime = $options->showUpdateTime === 'true';
        $showLoadTime = $options->showLoadTime === 'true';
        $showWordCount = $options->showWordCount === 'true';
        
        // 获取文章更新时间
        $updateTime = '';
        if ($showUpdateTime) {
            $modified = $widget->modified;
            $timeFormat = $options->timeFormat;
            $updateTime = date($timeFormat, $modified);
        }
        
        // 计算文章字数
        $wordCount = 0;
        if ($showWordCount) {
            $content = $widget->content;
            // 去除HTML标签
            $content = strip_tags($content);
            // 去除空白字符
            $content = preg_replace('/\s+/', '', $content);
            // 计算字符数（包括中英文）
            $wordCount = mb_strlen($content, 'UTF-8');
        }
        
        // 将配置传递给JavaScript
        $config = array(
            'position' => $position,
            'showUpdateTime' => $showUpdateTime,
            'showLoadTime' => $showLoadTime,
            'showWordCount' => $showWordCount,
            'updateTime' => $updateTime,
            'wordCount' => $wordCount
        );
        
        echo '<script>
(function() {
    var config = ' . json_encode($config) . ';
    var startTime = performance.timing.navigationStart || Date.now();
    
    // 生成信息框HTML
    function generateInfoBox() {
        var infoHTML = "<div class=\"article-info-box\">";
        
        // 更新时间
        if (config.showUpdateTime && config.updateTime) {
            infoHTML += "<div class=\"article-info-item\">";
            infoHTML += "<span class=\"article-info-label\">更新：</span>";
            infoHTML += "<span class=\"article-info-value\">" + config.updateTime + "</span>";
            infoHTML += "</div>";
        }
        
        // 字数统计
        if (config.showWordCount && config.wordCount) {
            infoHTML += "<div class=\"article-info-item\">";
            infoHTML += "<span class=\"article-info-label\">字数：</span>";
            infoHTML += "<span class=\"article-info-value\">" + config.wordCount + " 字</span>";
            infoHTML += "</div>";
        }
        
        // 加载时间
        if (config.showLoadTime) {
            var loadTime = (Date.now() - startTime).toFixed(0);
            infoHTML += "<div class=\"article-info-item\">";
            infoHTML += "<span class=\"article-info-label\">加载：</span>";
            infoHTML += "<span class=\"article-info-value\">" + loadTime + " ms</span>";
            infoHTML += "</div>";
        }
        
        infoHTML += "</div>";
        return infoHTML;
    }
    
    // 插入信息框
    function insertInfoBox() {
        // 查找文章容器
        var articleSelectors = [".post-content", ".entry-content", ".article-content", ".content", "article", ".post"];
        var article = null;
        
        for (var i = 0; i < articleSelectors.length; i++) {
            article = document.querySelector(articleSelectors[i]);
            if (article) break;
        }
        
        if (!article) {
            console.warn("ArticleInfo: 未找到文章内容容器");
            return;
        }
        
        var infoHTML = generateInfoBox();
        
        // 根据配置插入位置
        if (config.position === "top") {
            article.insertAdjacentHTML("afterbegin", infoHTML);
        } else if (config.position === "bottom") {
            article.insertAdjacentHTML("beforeend", infoHTML);
        } else if (config.position === "both") {
            article.insertAdjacentHTML("afterbegin", infoHTML);
            article.insertAdjacentHTML("beforeend", infoHTML);
        }
    }
    
    // 页面加载完成后插入信息框
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", insertInfoBox);
    } else {
        insertInfoBox();
    }
})();
</script>';
    }
} 