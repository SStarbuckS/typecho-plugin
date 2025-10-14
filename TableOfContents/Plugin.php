<?php
/**
 * 文章目录树插件
 * 
 * @package TableOfContents
 * @author cxuxrxsxoxr
 * @version 1.0.0
 * @link http://www.typecho.org
 */
class TableOfContents_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array('TableOfContents_Plugin', 'footer');
        Typecho_Plugin::factory('Widget_Archive')->header = array('TableOfContents_Plugin', 'header');
        return '插件已启用，文章页将自动生成目录树';
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
        // 位置设置
        $position = new Typecho_Widget_Helper_Form_Element_Radio(
            'position',
            array(
                'left' => '左侧固定',
                'right' => '右侧固定',
                'default' => '文章顶部'
            ),
            'right',
            '目录位置',
            '选择目录显示的位置'
        );
        $form->addInput($position);
        
        // 最小标题数量
        $minHeaders = new Typecho_Widget_Helper_Form_Element_Text(
            'minHeaders',
            NULL,
            '3',
            '最小标题数量',
            '当文章标题数量少于此值时不显示目录'
        );
        $form->addInput($minHeaders);
        
        // 标题层级
        $headingLevels = new Typecho_Widget_Helper_Form_Element_Checkbox(
            'headingLevels',
            array(
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6'
            ),
            array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'),
            '包含的标题层级',
            '选择要包含在目录中的标题层级'
        );
        $form->addInput($headingLevels);
        
        // 是否自动编号
        $autoNumber = new Typecho_Widget_Helper_Form_Element_Radio(
            'autoNumber',
            array(
                'true' => '开启',
                'false' => '关闭'
            ),
            'true',
            '自动编号',
            '是否为目录项自动添加编号'
        );
        $form->addInput($autoNumber);
        
        // 主题色
        $themeColor = new Typecho_Widget_Helper_Form_Element_Text(
            'themeColor',
            NULL,
            '#4285f4',
            '主题颜色',
            '目录的主题颜色（16进制颜色代码）'
        );
        $form->addInput($themeColor);
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
        $options = Helper::options()->plugin('TableOfContents');
        if (!$options) {
            return;
        }
        
        $position = $options->position;
        $themeColor = $options->themeColor;
        
        echo '<style>
/* 目录树容器 */
#toc-container {
    background: #fff;
    border: 1px solid #e1e4e8;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#toc-container.toc-fixed-left,
#toc-container.toc-fixed-right {
    position: fixed;
    top: 100px;
    width: 260px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    z-index: 999;
}

#toc-container.toc-fixed-left {
    left: 20px;
}

#toc-container.toc-fixed-right {
    right: 20px;
}

#toc-container.toc-default {
    margin-bottom: 30px;
}

#toc-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid ' . $themeColor . ';
    color: #24292e;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#toc-toggle {
    cursor: pointer;
    font-size: 14px;
    color: #586069;
    user-select: none;
}

#toc-content {
    overflow: hidden;
    transition: max-height 0.3s ease;
}

#toc-content.collapsed {
    max-height: 0 !important;
}

#toc-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

#toc-list li {
    margin: 0;
    padding: 0;
}

#toc-list a {
    display: block;
    padding: 6px 0;
    color: #586069;
    text-decoration: none;
    transition: all 0.2s ease;
    border-left: 2px solid transparent;
    padding-left: 10px;
    line-height: 1.5;
    font-size: 14px;
}

#toc-list a:hover {
    color: ' . $themeColor . ';
    border-left-color: ' . $themeColor . ';
    padding-left: 12px;
}

#toc-list a.active {
    color: ' . $themeColor . ';
    border-left-color: ' . $themeColor . ';
    font-weight: 600;
}

/* 标题层级缩进 */
#toc-list .toc-h2 { padding-left: 10px; }
#toc-list .toc-h3 { padding-left: 20px; }
#toc-list .toc-h4 { padding-left: 30px; }
#toc-list .toc-h5 { padding-left: 40px; }
#toc-list .toc-h6 { padding-left: 50px; }

/* 滚动条美化 */
#toc-container::-webkit-scrollbar {
    width: 6px;
}

#toc-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#toc-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#toc-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* 移动端隐藏目录 */
@media screen and (max-width: 768px) {
    #toc-container {
        display: none !important;
    }
}

/* 深色模式适配 */
@media (prefers-color-scheme: dark) {
    #toc-container {
        background: #1e1e1e;
        border-color: #3a3a3a;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    #toc-title {
        color: #e1e1e1;
    }
    
    #toc-toggle {
        color: #b1b1b1;
    }
    
    #toc-list a {
        color: #b1b1b1;
    }
    
    #toc-list a:hover {
        color: ' . $themeColor . ';
    }
    
    #toc-list a.active {
        color: ' . $themeColor . ';
    }
    
    #toc-container::-webkit-scrollbar-track {
        background: #2a2a2a;
    }
    
    #toc-container::-webkit-scrollbar-thumb {
        background: #4a4a4a;
    }
    
    #toc-container::-webkit-scrollbar-thumb:hover {
        background: #5a5a5a;
    }
}

/* 自动编号样式 */
.toc-auto-number #toc-list {
    counter-reset: toc-h2;
}

.toc-auto-number .toc-h2 {
    counter-reset: toc-h3;
}

.toc-auto-number .toc-h2::before {
    counter-increment: toc-h2;
    content: counter(toc-h2) ". ";
}

.toc-auto-number .toc-h3 {
    counter-reset: toc-h4;
}

.toc-auto-number .toc-h3::before {
    counter-increment: toc-h3;
    content: counter(toc-h2) "." counter(toc-h3) ". ";
}

.toc-auto-number .toc-h4 {
    counter-reset: toc-h5;
}

.toc-auto-number .toc-h4::before {
    counter-increment: toc-h4;
    content: counter(toc-h2) "." counter(toc-h3) "." counter(toc-h4) ". ";
}

.toc-auto-number .toc-h5 {
    counter-reset: toc-h6;
}

.toc-auto-number .toc-h5::before {
    counter-increment: toc-h5;
    content: counter(toc-h2) "." counter(toc-h3) "." counter(toc-h4) "." counter(toc-h5) ". ";
}

.toc-auto-number .toc-h6::before {
    counter-increment: toc-h6;
    content: counter(toc-h2) "." counter(toc-h3) "." counter(toc-h4) "." counter(toc-h5) "." counter(toc-h6) ". ";
}
</style>';
    }
    
    /**
     * 在页面底部输出JavaScript
     */
    public static function footer()
    {
        $widget = Typecho_Widget::widget('Widget_Archive');
        
        // 只在文章页面显示
        if (!$widget->is('single')) {
            return;
        }
        
        $options = Helper::options()->plugin('TableOfContents');
        if (!$options) {
            return;
        }
        
        $position = $options->position;
        $minHeaders = $options->minHeaders;
        $headingLevels = $options->headingLevels;
        $autoNumber = $options->autoNumber;
        
        // 将配置传递给JavaScript
        $config = array(
            'position' => $position,
            'minHeaders' => intval($minHeaders),
            'headingLevels' => $headingLevels,
            'autoNumber' => $autoNumber === 'true'
        );
        
        echo '<script>
(function() {
    var config = ' . json_encode($config) . ';
    
    // 生成目录
    function generateTOC() {
        // 获取文章内容容器（需要根据实际主题调整选择器）
        var articleSelectors = [".post-content", ".entry-content", ".article-content", ".content", "article", ".post"];
        var article = null;
        
        for (var i = 0; i < articleSelectors.length; i++) {
            article = document.querySelector(articleSelectors[i]);
            if (article) break;
        }
        
        if (!article) {
            console.warn("TOC: 未找到文章内容容器");
            return;
        }
        
        // 获取所有标题
        var headingSelector = config.headingLevels.join(",");
        var headings = article.querySelectorAll(headingSelector);
        
        if (headings.length < config.minHeaders) {
            return;
        }
        
        // 为每个标题添加ID
        var tocItems = [];
        for (var i = 0; i < headings.length; i++) {
            var heading = headings[i];
            var id = "toc-" + i;
            
            if (!heading.id) {
                heading.id = id;
            } else {
                id = heading.id;
            }
            
            var level = heading.tagName.toLowerCase();
            tocItems.push({
                id: id,
                text: heading.textContent,
                level: level
            });
        }
        
        // 生成目录HTML
        var positionClass = config.position === "default" ? "toc-default" : "toc-fixed-" + config.position;
        var tocHTML = "<div id=\"toc-container\" class=\"" + positionClass + (config.autoNumber ? " toc-auto-number" : "") + "\">";
        tocHTML += "<div id=\"toc-title\">目录 <span id=\"toc-toggle\">[-]</span></div>";
        tocHTML += "<div id=\"toc-content\"><ul id=\"toc-list\">";
        
        for (var i = 0; i < tocItems.length; i++) {
            var item = tocItems[i];
            tocHTML += "<li><a href=\"#" + item.id + "\" class=\"toc-" + item.level + "\">" + item.text + "</a></li>";
        }
        
        tocHTML += "</ul></div></div>";
        
        // 插入目录
        if (config.position === "default") {
            article.insertAdjacentHTML("beforebegin", tocHTML);
        } else {
            // 确保在 body 标签存在后插入
            if (document.body) {
                document.body.insertAdjacentHTML("beforeend", tocHTML);
            } else {
                document.documentElement.insertAdjacentHTML("beforeend", tocHTML);
            }
        }
        
        // 初始化功能
        initTOC();
    }
    
    // 初始化目录功能
    function initTOC() {
        var tocContainer = document.getElementById("toc-container");
        var tocContent = document.getElementById("toc-content");
        var tocToggle = document.getElementById("toc-toggle");
        var tocLinks = document.querySelectorAll("#toc-list a");
        
        if (!tocContainer) return;
        
        // 折叠/展开功能
        if (tocToggle) {
            tocToggle.addEventListener("click", function() {
                if (tocContent.classList.contains("collapsed")) {
                    tocContent.classList.remove("collapsed");
                    tocToggle.textContent = "[-]";
                } else {
                    tocContent.classList.add("collapsed");
                    tocToggle.textContent = "[+]";
                }
            });
        }
        
        // 平滑滚动
        tocLinks.forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                var targetId = this.getAttribute("href").substring(1);
                var target = document.getElementById(targetId);
                
                if (target) {
                    var offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: "smooth"
                    });
                    
                    // 更新活动状态
                    tocLinks.forEach(function(l) { l.classList.remove("active"); });
                    link.classList.add("active");
                }
            });
        });
        
        // 滚动高亮
        var throttle = function(func, delay) {
            var timer = null;
            return function() {
                if (!timer) {
                    timer = setTimeout(function() {
                        func.apply(this, arguments);
                        timer = null;
                    }, delay);
                }
            };
        };
        
        window.addEventListener("scroll", throttle(function() {
            var scrollPos = window.scrollY + 100;
            var headings = document.querySelectorAll(config.headingLevels.map(function(h) { return "#toc-content ~ * " + h + "[id], " + h + "[id]"; }).join(","));
            
            var current = null;
            for (var i = 0; i < headings.length; i++) {
                var heading = headings[i];
                if (heading.offsetTop <= scrollPos) {
                    current = heading;
                }
            }
            
            tocLinks.forEach(function(link) { link.classList.remove("active"); });
            
            if (current) {
                var activeLink = document.querySelector("#toc-list a[href=\"#" + current.id + "\"]");
                if (activeLink) {
                    activeLink.classList.add("active");
                }
            }
        }, 100));
    }
    
    // DOM加载完成后生成目录
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", generateTOC);
    } else {
        generateTOC();
    }
})();
</script>';
    }
} 