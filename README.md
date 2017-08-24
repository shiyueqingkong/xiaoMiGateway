### 项目说明
##### 本项目是根据小米网关开发手册编写，用php实现控制操作小米网关，只有示范，具体业务逻辑请根据实际需求自行编写
### 参考文档
#### https://forrestfung.gitbooks.io/lumi-gateway-local-api/content/
### 使用说明
#### 1.监听网关信息使用说明。
##### 在命令行中运行以下命令（windows系统）
```
//php运行路径  程序文件  参数
php.exe miGateway.php listenGateway
```
##### 注意：
```
1.网关要和本机在同一网络
2.网关需要打开局域网通信协议，具体方法看网关开发文档
```
#### 2.读/写操作网关使用说明。

##### 在命令行中运行以下命令（windows系统）
```
//php运行路径  程序文件  参数
php.exe miGateway.php index
```
##### index()中的具体业务逻辑需要自己去实现