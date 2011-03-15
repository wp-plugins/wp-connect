=== Plugin Name ===
Contributors: smyx
Donate link: http://www.smyx.net/wp-connect.html
Tags: twitter,qq,sina,netease,sohu,digu,douban,baidu,fanfou,renjian,zuosa,follow5,renren,kaixin001,connect
Requires at least: 2.9
Tested up to: 3.1.0
Stable tag: 1.3.2

支持使用微博帐号登录 WordPress 博客，并且支持同步文章的 标题和链接 到各大微博和社区。

== Description ==

可以使用腾讯微博、新浪微博、网易微博、人人帐号、豆瓣帐号登录WordPress博客，支持同步评论到相对应的微博。

发布或更新文章时同步一条该文章信息到Twitter、腾讯微博、新浪微博、网易微博、搜狐微博、人人网，开心网，嘀咕、豆瓣、百度说吧、饭否、人间网、做啥、Follow5等。

支持多作者博客，每位作者发布的文章都可以同步到他们各自绑定的微博上。

目前支持同时把文章出现的第一张图片同步到 腾讯微博、新浪微博、网易微博。

同步到Twitter时可以选择使用代理，并可自定义API。

支持自定义消息前缀。

支持t.cn短网址。

支持把文章标签当成微博话题。

Twitter、腾讯微博、新浪微博、网易微博、豆瓣采用OAuth授权。

= 注意事项 =

除了那些采用OAuth授权的外，其他的请自己输入账号和密码，不会去官方验证密码的准确性，所以请自己把关，呵呵！

= FAQs =

1、使用文章同步功能时出现Fatal error: Cannot redeclare class OAuthException in /xxxxx/wp-content/plugins/wp-connect/OAuth/OAuth.php on line 8.

出现此错误，应该是您用了同类别的同步插件，而oauth.php不同的修订版本会起冲突。

2、如何在新页面一键发布新鲜事到各大微博和社区？应该注意那些问题？

首先新建页面，切换到HTML模式，然后输入简码

`[wp_to_microblog]`

到插件后台设置 自定义页面密码，当然前提是你已经在 WordPress连接微博 后台设置过相关账号信息。你也可以使用模板文件创建一个自定义页面，调用方式

`<?php wp_to_microblog();?>`

使用不同的主题可能造成错位，请在插件页面下的page.css中修改CSS

演示: http://www.smyx.net/say

3、我更新文章时不想再次同步文章信息到微博可以吗？

可以的。你只需把更新文章间隔设为0即可。

4、为什么用网易微博登录的评论者不显示头像？

由于网易微博对Web应用暂时不支持图片外链，所以用网易微博登录并发表评论的用户头像不能正常显示，你可以发邮件到 OpenAPI@yeah.net 进行申请，标题为“XXX申请图片外链”，注明应用名称：WordPress连接微博、申请者与外链的域名。申请后该域名下可直接链接网易微博内的所有图片。申请通过后，请在后台插件的“网易微博评论者头像”选项勾选即可使用。

5、为什么我的博客评论处不显示按钮？

这与你的主题有关，请确保你的主题评论位置的form里面，有

`<?php do_action('comment_form', $post->ID); ?>`

假如你选择了用户必须注册并登录才可以发表评论，那么请在你的主题评论位置，找到类似于如下语句：

`<p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.', smyx), wp_login_url( get_permalink() )); ?></p>`

后面添加

`<?php do_action('comment_form', $post->ID); ?>`

或者添加

`<?php wp_connect(); ?>`

6、出现类似于 Call to undefined function curl_init() in ……

这是因为您的服务器(主机)当前配置不支持curl，请联系空间商重新配置。

7、如何获取人人网api key？

首先打开人人网开放平台: http://app.renren.com/developers/app/，登录后点右上角的 创建新应用 按钮，输入应用名称，创建成功后就可以看到API Key和Secret Key了，
之后点击左边的Connect设置 ，Connect URL填写为 http://www.smyx.net/wp-login.php，根域名填写为 smyx.net，把相关域名改成你的就行了。

8、为什么我使用该插件授权时总是失败？

这个问题有点纠结，因为大部分人可以完成授权，有可能是主机不支持，也可能是插件冲突，所以新增一个页面，来帮助这些人得到授权码，但不保证能正常使用！
网址：http://www.smyx.net/apps/oauth.php

== Installation ==

1.下载插件上传到WordPress插件目录，后台激活 ，
2.到设置页面开启插件并设置，以及账号绑定等，
3.国内主机用户使用Twitter请勾选使用代理。

== Changelog ==

= 1.3.2 =
*2011/03/15
支持用人人帐号登录，支持分享到人人网。
修正部分bug。

= 1.3.0 =
*2011/03/12
文章发布页面和编辑页面增加同步设置。
修正几个bug。

= 1.2.6 =
*2011/03/08
更新page.js，修正了自定义页面百度说吧不同步的bug。
同步时字符截取更准确。

= 1.2.5 =
*2011/03/07
同步新增百度说吧。
后台帐号绑定更换一组新图标。
使用微博登录的用户可以在我的资料页绑定同名帐号。

= 1.2.4 =
*2011/03/04
新增支持把文章标签当成微博话题。
完善自定义发布页面，加入ajax无刷新效果。

= 1.2.3 =
*2011/03/01
修正几个bug。

= 1.2.1 =
*2011/03/01
新增支持同时把文章出现的第一张图片同步到 腾讯微博。

= 1.2.0 =
*2011/02/28
支持t.cn短网址。
同步新增人人网，开心网。
每个用户的“我的资料” 页面可以选择是否锁定帐号，锁定后，其他同名的微博账号将不能登录。
支持多作者博客，每位作者发布的文章都可以同步到他们各自绑定的微博上，请在‘我的资料’页设置(需管理员开启)。

= 1.1.0 =
*2011/02/23
无

= 1.0.0 =
*2011/02/22
初始版本

