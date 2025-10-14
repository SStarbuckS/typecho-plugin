# Typecho NoFollow Links 插件

自动为文章中的外部链接添加 `nofollow` 属性，防止 SEO 权重流失，提升网站搜索排名。

## ✨ 功能特性

- 🔗 **自动识别外链** - 智能区分站内链接和外部链接
- 🚫 **添加 NoFollow** - 自动为外链添加 `rel="nofollow"` 属性
- 🔒 **安全防护** - 添加 `rel="noopener"` 防止安全风险
- 🌐 **灵活打开方式** - 支持所有链接或仅外链在新窗口打开
- 🎯 **泛域名匹配** - 支持 `*.example.com` 泛匹配排除域名
- ⚙️ **灵活配置** - 支持排除信任域名
- 📝 **保留原属性** - 智能合并已有的 rel 属性
- 🎯 **SEO 优化** - 有效防止权重流失

## 📦 安装方法

1. 下载插件文件
2. 将文件夹重命名为 `NoFollowLinks`
3. 上传到 Typecho 的 `usr/plugins/` 目录
4. 在后台「控制台」→「插件」中启用插件
5. 点击「设置」进行配置

## ⚙️ 配置选项

### 新窗口打开设置
- **默认值**：所有链接（站内+外部）
- **选项**：
  - **所有链接（站内+外部）** - 所有链接都在新窗口打开
  - **仅外部链接** - 只有外链在新窗口打开
  - **不处理** - 不添加 `target="_blank"`
- **附加**：开启新窗口时自动添加 `rel="noopener"` 防止安全风险

### 排除的域名
- **格式**：每行一个域名，不包含 `http://` 或 `https://`
- **泛匹配**：支持 `*.example.com` 格式匹配所有子域名
- **示例**：
  ```
  trusted-site.com
  partner-website.com
  *.cdn-domain.com
  github.com
  ```
- **说明**：排除列表中的域名不会被添加 nofollow 属性

## 📝 使用效果

### 原始 HTML
```html
<a href="https://external-site.com">外部链接</a>
<a href="/local-page">本站链接</a>
<a href="https://your-site.com/article">本站链接</a>
```

### 处理后（默认配置：所有链接新窗口打开）
```html
<a href="https://external-site.com" target="_blank" rel="nofollow noopener">外部链接</a>
<a href="/local-page" target="_blank" rel="noopener">本站链接</a>
<a href="https://your-site.com/article" target="_blank" rel="noopener">本站链接</a>
```

### 处理后（配置：仅外链新窗口打开）
```html
<a href="https://external-site.com" target="_blank" rel="nofollow noopener">外部链接</a>
<a href="/local-page">本站链接</a>
<a href="https://your-site.com/article">本站链接</a>
```

## 🔍 工作原理

### 外链识别
1. **完整 URL 检测** - 检查链接是否包含 `http://` 或 `https://`
2. **域名对比** - 提取域名与当前站点域名对比
3. **排除列表** - 检查是否在信任域名列表中
4. **跳过特殊链接** - 自动跳过锚点（`#`）、`javascript:`、`mailto:` 等

### 属性处理
1. **保留原属性** - 保留链接原有的所有属性
2. **智能合并 rel** - 如果已有 rel 属性，智能合并不重复
3. **添加 nofollow** - 仅为外链添加 `nofollow`
4. **添加 noopener** - 开启新窗口时自动添加 `noopener`（安全性）
5. **添加 target** - 根据配置添加 `target="_blank"`

## 🔒 安全性说明

### rel="noopener" 的重要性
当使用 `target="_blank"` 打开新窗口时，新页面可以通过 `window.opener` 访问原页面，可能导致：
- **钓鱼攻击** - 恶意页面可以修改原页面的 URL
- **性能问题** - 新页面与原页面共享同一进程

添加 `rel="noopener"` 可以：
- ✅ 阻止新页面访问 `window.opener`
- ✅ 提升安全性
- ✅ 改善性能

本插件在开启新窗口打开时，会自动添加 `noopener` 属性。

## 📊 SEO 最佳实践

### NoFollow 的作用
- **控制权重流向** - 防止 PageRank 流失到外部站点
- **避免垃圾链接** - 防止评论区、用户生成内容中的垃圾链接
- **提升站内权重** - 集中权重在站内重要页面

### 何时使用 NoFollow
✅ **应该使用**：
- 不信任的外部链接
- 用户生成的内容（评论、论坛）
- 付费链接、广告链接
- 登录、注册等功能性链接

❌ **不应使用**：
- 权威网站的引用（可添加到排除列表）
- 合作伙伴网站（可添加到排除列表）
- 有价值的资源推荐（视情况而定）

### 排除信任域名
对于信任的网站，可以添加到排除列表：
```
wikipedia.org
github.com
*.googleapis.com
stackoverflow.com
```

**泛匹配说明**：
- `*.googleapis.com` 会匹配：
  - `googleapis.com`
  - `ajax.googleapis.com`
  - `fonts.googleapis.com`
  - 所有 `.googleapis.com` 的子域名

## 🎯 使用场景

### 1. 博客文章
自动为文章中引用的外部资料添加 nofollow，保护 SEO 权重。

### 2. 评论系统
如果启用了评论功能，用户评论中的外链会自动添加 nofollow，防止垃圾链接。

### 3. 友情链接
普通友情链接自动添加 nofollow，信任的友情链接可添加到排除列表。

### 4. 内容聚合
引用外部内容时，自动添加 nofollow，避免权重流失。

## ⚡ 性能说明

- **正则表达式处理** - 使用高效的正则表达式，性能优秀
- **单次遍历** - 一次性处理所有链接，避免多次解析
- **智能跳过** - 快速跳过非外链，减少处理时间
- **内存友好** - 不使用 DOM 解析器，内存占用极小

## ❓ 常见问题

### 1. 插件会影响站内链接吗？
不会。插件会智能识别站内链接（包括相对路径和完整域名），只处理外部链接。

### 2. 如何排除某些信任网站？
在插件配置中的「排除的域名」中添加域名，每行一个，不包含 `http://`。

### 3. 已有 rel 属性的链接会被覆盖吗？
不会。插件会智能合并已有的 rel 属性，不会覆盖或重复。

### 4. NoFollow 会影响用户体验吗？
不会。NoFollow 只影响搜索引擎的抓取行为，不影响用户的点击和访问。

### 5. 为什么要添加 rel="noopener"？
当使用 `target="_blank"` 时，添加 `noopener` 可以防止安全风险和性能问题。

## 🔧 技术说明

### 处理的链接类型
✅ **会处理**：
- `<a href="https://example.com">外链</a>`
- `<a href="http://example.com">外链</a>`

❌ **不处理**：
- `<a href="/local-page">站内相对链接</a>`
- `<a href="https://your-site.com/page">站内完整链接</a>`
- `<a href="#anchor">锚点链接</a>`
- `<a href="javascript:void(0)">JavaScript 链接</a>`
- `<a href="mailto:email@example.com">邮件链接</a>`

### 插件钩子
- `Widget_Abstract_Contents::contentEx` - 处理文章内容

### 域名匹配逻辑

#### 普通匹配
```php
// 精确匹配或子域名匹配
$urlDomain === $excludeDomain 
|| substr($urlDomain, -strlen('.' . $excludeDomain)) === '.' . $excludeDomain
```

示例：排除 `example.com` 会匹配：
- `example.com` ✅
- `www.example.com` ✅
- `blog.example.com` ✅
- `sub.blog.example.com` ✅

#### 泛匹配（推荐）
```php
// 支持 *.example.com 格式
if (strpos($excludeDomain, '*.') === 0) {
    $pattern = substr($excludeDomain, 2);
    // 匹配主域名和所有子域名
}
```

示例：排除 `*.example.com` 会匹配：
- `example.com` ✅
- `www.example.com` ✅
- `api.example.com` ✅
- `cdn.static.example.com` ✅

**推荐使用泛匹配** `*.example.com` 以确保匹配所有子域名。

## 📈 SEO 效果

使用本插件后：
- ✅ **权重集中** - PageRank 不会流失到外部站点
- ✅ **排名提升** - 站内重要页面获得更多权重
- ✅ **垃圾防护** - 自动处理用户生成内容中的链接
- ✅ **灵活控制** - 可以精确控制哪些外链传递权重

## 📄 浏览器兼容性

所有现代浏览器都支持以下属性：
- `rel="nofollow"` ✅
- `rel="noopener"` ✅
- `target="_blank"` ✅

## 🔗 相关资源

- [Google: rel="nofollow" 属性](https://developers.google.com/search/docs/crawling-indexing/qualify-outbound-links)
- [MDN: rel="noopener"](https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noopener)
- [Typecho 官网](http://typecho.org)

## 📝 更新日志

### 1.0.0 (2025-10-13)
- 🎉 首次发布
- ✅ 自动识别并处理外部链接
- ✅ 自动为外链添加 nofollow 属性
- ✅ 支持三种新窗口打开模式（所有/仅外链/不处理）
- ✅ 自动添加 noopener 安全属性
- ✅ 支持排除信任域名（泛匹配 `*.example.com`）
- ✅ 智能合并已有 rel 属性
- ✅ 移除 external 属性（简化输出）

## 👨‍💻 作者

**cxuxrxsxoxr**

## 📄 许可证

本插件遵循 Typecho 相关许可协议。 