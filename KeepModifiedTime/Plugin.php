<?php
/**
 * 保持修改时间插件
 * 
 * @package KeepModifiedTime
 * @author cxuxrxsxoxr
 * @version 1.0.0
 * @link http://www.typecho.org
 */
class KeepModifiedTime_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     */
    public static function activate()
    {
        // 挂载编辑页面表单钩子
        Typecho_Plugin::factory('admin/write-post.php')->option = array('KeepModifiedTime_Plugin', 'addCheckbox');
        Typecho_Plugin::factory('admin/write-page.php')->option = array('KeepModifiedTime_Plugin', 'addCheckbox');
        
        // 使用更晚的钩子：在写入数据库之后
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('KeepModifiedTime_Plugin', 'updateModified');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('KeepModifiedTime_Plugin', 'updateModified');
        
        return '插件已启用，编辑文章时可选择不更新修改时间';
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
        // 调试模式开关
        $debugMode = new Typecho_Widget_Helper_Form_Element_Radio(
            'debugMode',
            array(
                '0' => '关闭',
                '1' => '开启'
            ),
            '0',
            '调试模式',
            '开启后会在插件目录生成 debug.log 文件，用于排查问题'
        );
        $form->addInput($debugMode);
        
        // 添加使用说明
        $html = '<div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #1976d2;">✅ 插件启用成功</h3>
            <p style="margin: 10px 0;">📝 <strong>工作方式：</strong></p>
            <ul style="margin: 10px 0 10px 20px; line-height: 1.8;">
                <li>编辑<strong>已发布</strong>的文章时，默认勾选"不更新修改时间"</li>
                <li>新建文章时，不会勾选（让新文章正常设置时间）</li>
                <li>可以手动取消勾选，让修改时间正常更新</li>
            </ul>
            <p style="margin: 10px 0;">💡 <strong>适用场景：</strong>修改错别字、调整格式、修正链接等小改动</p>
            <p style="margin: 10px 0;">⚠️ <strong>注意：</strong>重要内容更新时，请手动取消勾选，让修改时间正常更新</p>
        </div>';
        
        $description = new Typecho_Widget_Helper_Layout('div', array('class' => 'typecho-option'));
        $description->html($html);
        $form->addItem($description);
    }
    
    /**
     * 个人用户的配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }
    
    /**
     * 在编辑页面添加复选框
     */
    public static function addCheckbox()
    {
        // 获取插件配置
        $options = Helper::options();
        $pluginConfig = $options->plugin('KeepModifiedTime');
        $debugMode = isset($pluginConfig->debugMode) ? $pluginConfig->debugMode : '0';
        
        // 保存原始修改时间到 session
        $request = Typecho_Request::getInstance();
        $cid = $request->get('cid');
        
        // 判断是否为新文章（没有 cid）
        $isNewPost = empty($cid);
        
        if ($cid) {
            $db = Typecho_Db::get();
            $post = $db->fetchRow($db->select('modified')->from('table.contents')->where('cid = ?', $cid));
            
            if ($post && isset($post['modified'])) {
                // 安全启动 session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['keep_modified_' . $cid] = $post['modified'];
                
                // 调试
                if ($debugMode == '1') {
                    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - [addCheckbox] Saved original time for cid ' . $cid . ': ' . $post['modified'] . "\n", FILE_APPEND);
                }
            }
        }
        
        echo '<script>
        (function() {
            var isNewPost = ' . ($isNewPost ? 'true' : 'false') . ';
            
            // 等待 DOM 加载完成
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", addKeepModifiedTimeOption);
            } else {
                addKeepModifiedTimeOption();
            }
            
            function addKeepModifiedTimeOption() {
                // 查找高级选项容器
                var advancedPanel = document.querySelector("#advance-panel, .typecho-post-option-submit");
                
                if (advancedPanel) {
                    var checkbox = document.createElement("div");
                    checkbox.style.marginTop = "15px";
                    checkbox.style.marginBottom = "15px";
                    
                    // 对于已有文章，默认勾选；对于新文章，不勾选
                    var checkedAttr = isNewPost ? "" : "checked";
                    
                    checkbox.innerHTML = \'<label for="keepModifiedTime" class="typecho-label"><input type="checkbox" name="keepModifiedTime" id="keepModifiedTime" value="1" \' + checkedAttr + \' style="margin-right: 5px;" />不更新修改时间</label>\';
                    
                    // 插入到高级选项面板的最前面
                    if (advancedPanel.firstChild) {
                        advancedPanel.insertBefore(checkbox, advancedPanel.firstChild);
                    } else {
                        advancedPanel.appendChild(checkbox);
                    }
                }
            }
        })();
        </script>';
    }
    
    /**
     * 在文章发布后更新修改时间
     */
    public static function updateModified($contents, $widget)
    {
        // 获取插件配置
        $options = Helper::options();
        $pluginConfig = $options->plugin('KeepModifiedTime');
        $debugMode = isset($pluginConfig->debugMode) ? $pluginConfig->debugMode : '0';
        
        // 检查是否勾选了保持修改时间
        $request = $widget->request;
        $keepModifiedTime = $request->get('keepModifiedTime');
        
        // 调试：写入日志
        if ($debugMode == '1') {
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - [finishPublish] keepModifiedTime: ' . var_export($keepModifiedTime, true) . "\n", FILE_APPEND);
        }
        
        if ($keepModifiedTime == '1') {
            $db = Typecho_Db::get();
            
            // 尝试多种方式获取 cid
            $cid = null;
            if (isset($contents['cid'])) {
                $cid = $contents['cid'];
            } elseif (isset($widget->cid)) {
                $cid = $widget->cid;
            } else {
                $cid = $request->get('cid');
            }
            
            // 调试：记录 cid
            if ($debugMode == '1') {
                file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - [finishPublish] cid: ' . var_export($cid, true) . "\n", FILE_APPEND);
            }
            
            if ($cid) {
                // 安全启动 session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // 获取保存在 session 中的原始时间
                $originalTime = isset($_SESSION['keep_modified_' . $cid]) ? $_SESSION['keep_modified_' . $cid] : null;
                
                if ($debugMode == '1') {
                    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - [finishPublish] original from session: ' . var_export($originalTime, true) . "\n", FILE_APPEND);
                }
                
                if ($originalTime) {
                    // 直接更新数据库，恢复原始修改时间
                    $updateResult = $db->query($db->update('table.contents')
                        ->rows(array('modified' => $originalTime))
                        ->where('cid = ?', $cid));
                    
                    // 清除 session
                    unset($_SESSION['keep_modified_' . $cid]);
                    
                    // 调试：记录修改
                    if ($debugMode == '1') {
                        file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - [finishPublish] Modified time restored: ' . $originalTime . ' (result: ' . var_export($updateResult, true) . ')' . "\n", FILE_APPEND);
                    }
                }
            }
        }
    }
}