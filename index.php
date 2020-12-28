<?php 
require 'vendor/autoload.php';
use GuzzleHttp\Client;
function pginsert($content, $pdoObject)
{
    $query = "insert into scflog(name) values(:logs)";
    $smt = $pdoObject->prepare($query);//$tablename[1]替换为你的表名
    $smt->execute(array(
        ':logs' => $content,
    ));
}

//base_uri设置为SCF自定义运行时提供的本地交互的访问地址，拼接 SCF_RUNTIME_API和 SCF_RUNTIME_API_PORT
$client = new Client([
    'base_uri' => 'http://' . getenv('SCF_RUNTIME_API') .':'. getenv('SCF_RUNTIME_API_PORT') ,
]);
var_dump(PHP_VERSION); 
var_dump(PHP_VERSION_ID);
//初始化pgsql的连接
$pdoObject = new PDO('pgsql:host=yourpgsqladdress;dbname=yourdatabases', 'username', 'password');
//告知scf运行时，函数准备就绪
$response = $client->post('/runtime/init/ready');
var_dump($response);
//无限循环从runtime中获取请求并处理
while(true) {
    $response = $client->get('/runtime/invocation/next');
    $body = $response->getBody();
    // Explicitly cast the body to a string
    $stringBody = (string) $body;
    var_dump($stringBody);
    //插入数据库
    pginsert($stringBody,$pdoObject);
    $r = $client->request('POST', '/runtime/invocation/response', [
        'body' => 'save ok'
    ]);
    var_dump($r);
}



