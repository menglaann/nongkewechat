农博网微信公众号服务
===================

# 依赖
1. python
目前不需要第三方依赖，只需要python就可以了。
如果服务器上面没有安装，可以在windows开发机上面安装python，使用pyinstaller打包成可执行文件，上传到服务器上面，这样，服务器可以不用安装python。
    
2. php
这个服务器上面已经配置好了，所以应该没问题。

# 使用
之要将gp_main.php及其所在目录下面的所有文件放到http服务器相应的路径下面就可以了，然后在微信公众号管理页面设置URL:
`http://ip/path/to/gp_main.php`
通过验证就可以使用了。
gp_main.php中调用python发送http请求获取天气信息（php脚本中暂时未调用）和农博网的作物信息.

例如，关注nongkewechat之后，发送"CXZW 苹果"，公众号就会返回苹果的信息。其他信息抓取都已经完成，但是还没有在php中调用测试。目前需要可用服务器来进行调试。


