<?php
/**
 * 外链 NoFollow 插件
 * 
 * @package NoFollowLinks
 * @author cxuxrxsxoxr
 * @version 1.0.0
 * @link http://www.typecho.org
 */
class NoFollowLinks_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     */
    public static function activate()
    {
        // 挂载文章内容输出接口
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('NoFollowLinks_Plugin', 'parseContent');
        
        return '插件已启用，外链将自动添加 nofollow 属性';
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
        // 新窗口打开模式
        $openInNewWindow = new Typecho_Widget_Helper_Form_Element_Radio(
            'openInNewWindow',
            array(
                'all' => '所有链接（站内+外部）',
                'external' => '仅外部链接',
                'none' => '不处理'
            ),
            'all',
            '新窗口打开设置',
            '选择哪些链接在新窗口打开'
        );
        $form->addInput($openInNewWindow);
        
        // 排除域名
        $excludeDomains = new Typecho_Widget_Helper_Form_Element_Textarea(
            'excludeDomains',
            NULL,
            '',
            '排除的域名',
            '输入不需要添加 nofollow 的域名，每行一个（不包含 http:// 或 https://）<br>支持泛匹配：*.example.com 或 example.com<br>例如：<br>trusted-site.com<br>*.cdn-domain.com'
        );
        $form->addInput($excludeDomains);
    }
    
    /**
     * 个人用户的配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }
    
    /**
     * 解析内容，为外链添加 nofollow 属性
     */
    public static function parseContent($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        
        // 获取插件配置
        $options = Helper::options();
        $pluginOptions = $options->plugin('NoFollowLinks');
        
        if (!$pluginOptions) {
            return $content;
        }
        
        // 确保配置项存在，使用默认值
        $openInNewWindow = isset($pluginOptions->openInNewWindow) ? $pluginOptions->openInNewWindow : 'all';
        
        // 获取当前站点域名
        $siteUrl = $options->siteUrl;
        $siteDomain = parse_url($siteUrl, PHP_URL_HOST);
        
        // 如果无法解析站点域名，直接返回
        if (!$siteDomain) {
            return $content;
        }
        
        // 获取排除的域名列表（支持泛匹配）
        $excludeDomains = array();
        if (!empty($pluginOptions->excludeDomains)) {
            // 处理不同操作系统的换行符
            $lines = preg_split('/\r\n|\r|\n/', $pluginOptions->excludeDomains);
            foreach ($lines as $line) {
                $domain = trim($line);
                if (!empty($domain)) {
                    $excludeDomains[] = $domain;
                }
            }
        }
        
        // 如果内容为空，直接返回
        if (empty($content)) {
            return $content;
        }
        
        // 使用正则表达式处理链接
        $content = preg_replace_callback(
            '/<a\s+([^>]*?)href=(["\'])(.*?)\2([^>]*?)>/i',
            function($matches) use ($siteDomain, $excludeDomains, $openInNewWindow) {
                $beforeHref = $matches[1];
                $quote = $matches[2];
                $url = $matches[3];
                $afterHref = $matches[4];
                
                // 跳过锚点链接、JavaScript 链接等
                if (empty($url) || $url[0] === '#' || stripos($url, 'javascript:') === 0 || stripos($url, 'mailto:') === 0) {
                    return $matches[0];
                }
                
                // 判断是否为外链
                $isExternal = false;
                
                // 如果是完整 URL
                if (preg_match('/^https?:\/\//i', $url)) {
                    $urlDomain = parse_url($url, PHP_URL_HOST);
                    
                    // 如果无法解析域名，跳过此链接
                    if (!$urlDomain) {
                        return $matches[0];
                    }
                    
                    // 检查是否为外链
                    if ($urlDomain !== $siteDomain) {
                        $isExternal = true;
                        
                        // 检查是否在排除列表中（支持泛匹配）
                        foreach ($excludeDomains as $excludeDomain) {
                            $matched = false;
                            
                            // 泛匹配：*.example.com
                            if (strpos($excludeDomain, '*.') === 0) {
                                $pattern = substr($excludeDomain, 2); // 移除 *.
                                // 匹配 example.com 或 xxx.example.com
                                if ($urlDomain === $pattern || substr($urlDomain, -strlen('.' . $pattern)) === '.' . $pattern) {
                                    $matched = true;
                                }
                            } else {
                                // 精确匹配或子域名匹配
                                if ($urlDomain === $excludeDomain || substr($urlDomain, -strlen('.' . $excludeDomain)) === '.' . $excludeDomain) {
                                    $matched = true;
                                }
                            }
                            
                            if ($matched) {
                                $isExternal = false;
                                break;
                            }
                        }
                    }
                }
                
                // 如果不是外链且不需要处理所有链接的新窗口打开，直接返回原标签
                if (!$isExternal && $openInNewWindow !== 'all') {
                    return $matches[0];
                }
                
                // 构建新的属性
                $allAttrs = $beforeHref . $afterHref;
                
                // 检查并添加 rel 属性
                $relValues = array();
                
                if (preg_match('/rel=(["\'])(.*?)\1/i', $allAttrs, $relMatch)) {
                    // 已有 rel 属性
                    $existingRel = $relMatch[2];
                    $relParts = preg_split('/\s+/', $existingRel);
                    
                    foreach ($relParts as $part) {
                        if (!empty($part)) {
                            $relValues[] = $part;
                        }
                    }
                    
                    // 移除原有的 rel 属性
                    $allAttrs = preg_replace('/rel=(["\'])(.*?)\1/i', '', $allAttrs);
                }
                
                // 仅为外链添加 nofollow
                if ($isExternal && !in_array('nofollow', $relValues)) {
                    $relValues[] = 'nofollow';
                }
                
                // 检查是否需要在新窗口打开
                $shouldOpenInNewWindow = false;
                if ($openInNewWindow === 'all') {
                    // 所有链接都在新窗口打开
                    $shouldOpenInNewWindow = true;
                } elseif ($openInNewWindow === 'external' && $isExternal) {
                    // 仅外链在新窗口打开
                    $shouldOpenInNewWindow = true;
                }
                
                // 添加 noopener（安全性）
                if ($shouldOpenInNewWindow && !in_array('noopener', $relValues)) {
                    $relValues[] = 'noopener';
                }
                
                // 检查并添加 target 属性
                if ($shouldOpenInNewWindow) {
                    if (!preg_match('/target=/i', $allAttrs)) {
                        $allAttrs .= ' target="_blank"';
                    }
                }
                
                // 构建新的 rel 属性（如果有值）
                if (!empty($relValues)) {
                    $newRel = implode(' ', $relValues);
                    $allAttrs .= ' rel="' . $newRel . '"';
                }
                
                // 清理多余空格
                $allAttrs = trim($allAttrs);
                
                // 返回新的标签
                return '<a ' . $allAttrs . ' href=' . $quote . $url . $quote . '>';
            },
            $content
        );
        
        return $content;
    }
} 