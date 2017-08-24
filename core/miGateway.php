<?php
/**
 * xiaoMi gateway Control
 * Author: huhai
 * email:shiyueqingkong@163.com
 * CreateDate: 2017/8/23
 */

set_time_limit(0);

//判断参数
$action = $argv[1];

//根据命令调用方法
$SocketServer = new SocketServer();
$SocketServer->$action();

class SocketServer
{
    private $groupIp = "224.0.0.50";//网关组播ip,开发手册提供
    private $groupPort = 4321;//网关组播端口,开发手册提供
    private $gatewayPort = 9898;//网关通讯端口,开发手册提供

    private $gateWayIp = "192.168.1.10";//网关ip,根据自己实际情况自定义
    private $gatewayKey = "1234567890abcdef";//局域网通信协议key,根据自己实际情况自定义

    public function index()
    {


        //获取所有网关信息
        $sendMsg = "{'cmd':'whois'}";
        $this->readGateway($sendMsg, 1);

        /**
         * 读取设备信息的相关操作
         */

//        $sendMsg = '{"cmd" : "get_id_list"}';//读取子设备列表
//        $sendMsg = '{"cmd":"read","sid":"123456"}';//获取具体设备信息,sid为上面一条命令获取到的结果，请根据实际情况填写
//        $this->readGateway($sendMsg);



        /**
         *
         * 写入设备相关操作
         *
         * 写入操作都需要key才能执行
         * key是根据小米网关token生成的，网关每发送一次报文，token就刷新
         * 所以要获得写入key需要先执行一次查询命令获得token
         * 经过测试，目前只有get_id_list可以获得token
         */

          //获取通信key的方法
//        $sendMsg = '{"cmd" : "get_id_list"}';//读取子设备列表
//        $devInfo = json_decode($this->readGateway($sendMsg), true);//解析获取的报文
//        $key = $this->getWriteKey($devInfo['token']);  //获取通信key


        /**
         * 然后就可以进行相关写入操作了，具体写入操作详见开发文档，这里以无线开关为例
         */

        //写入无线开关，sid和short_id以自己实际情况为准
//        $writeCmd = [
//            "cmd" => "write",
//            "model" => "switch",
//            "sid" => "123456789",
//            "short_id" => "66666",
//            "data" => [
//                "key" => $key,
//                "status" => "click"],
//        ];

         //执行写操作
//        $this->writeGateway(json_encode($writeCmd));


    }

    /**
     * 监听网关组播信息
     * @Author huhai
     * @date 2017/8/23
     */
    public function listenGateway()
    {

        set_time_limit(0);
        ob_implicit_flush();

        //创建socket绑定ip
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($socket, '0.0.0.0', $this->gatewayPort);

        //加入组播
        $group_params = array(
            "group" => $this->groupIp,
        );
        socket_set_option($socket, IPPROTO_IP, MCAST_JOIN_GROUP, $group_params);

        //接收数据
        while (true) {

            //显示收到的结果
            $this->showReceive($socket);
        }

        socket_close($socket);
    }

    /**
     * 读取网关命令
     * @param string $cmd json格式
     * @param int $isGroup 是否组播0不是1是
     * @return string $recvStr 返回的信息
     * @Author huhai
     * @date 2017/8/23
     */
    public function readGateway($cmd, $isGroup = 0)
    {
        //创建socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($isGroup) {
            $group_params = array("group" => $this->groupIp);
            socket_set_option($socket, IPPROTO_IP, MCAST_JOIN_GROUP, $group_params);
            socket_sendto($socket, $cmd, strlen($cmd), 0, $this->groupIp, $this->groupPort);
        } else {
            socket_sendto($socket, $cmd, strlen($cmd), 0, $this->gateWayIp, $this->gatewayPort);
        }

        //显示收到的结果
        $recvStr = $this->showReceive($socket);
        socket_close($socket);
        return $recvStr;
    }

    /**
     * 写设备命令
     * @param $cmd  json格式
     * @return string $recvStr 返回的信息
     * @Author huhai
     * @date 2017/8/23
     */
    public function writeGateway($cmd)
    {
        //创建socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        //发送命令
        socket_sendto($socket, $cmd, strlen($cmd), 0, $this->gateWayIp, $this->gatewayPort);
        //显示收到的结果
        $recvStr = $this->showReceive($socket);
        socket_close($socket);
        return $recvStr;
    }


    /**
     * 显示收到的结果
     * @param $socket
     * @return string 收到的报文
     */
    private function showReceive($socket)
    {
        if (!$socket) return false;
        $port = 0;
        $from = "";
        socket_recvfrom($socket, $recvStr, 1024, 0, $from, $port);
        echo "time:" . date("Y-m-d H:i:s") . "\r\n";
        echo "from:" . $from . "\r\n";
        echo "recvStr:" . $recvStr . "\r\n";
        echo "\r\n";

        return $recvStr;
    }


    /**
     * 获取通信key
     * @param $token
     * @return string $key 与网关通讯需要的key
     * @Author huhai
     * @date 2017/8/23
     */
    public function getWriteKey($token)
    {
        $localIV = hex2bin("17996d093d28ddb3ba695a2e6f58562e");//初始向量,开发手册提供

        //打开加密模块
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);

        mcrypt_generic_init($module, $this->gatewayKey, $localIV);      //初始化加密模块
        $key = mcrypt_generic($module, $token);//加密

        //结束加密工作
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        //返回key
        return bin2hex($key);

    }
}

