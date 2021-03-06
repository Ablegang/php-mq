<?php
// +----------------------------------------------------------------------
// | Consumer.php
// +----------------------------------------------------------------------
// | Description: 消费者
// +----------------------------------------------------------------------
// | Time: 2018/12/19 下午4:55
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

//include_once 'boot.php';

try {

    \Ablegang\PhpMq\Queue::init('Mysql', [
        'dsn' => 'mysql:host=mysql;dbname=test',
        'username' => 'root',
        'password' => 'root',
        'table' => 'queues',
        'ttr' => 60,
    ]);

    while (1) {
        // 死循环，使进程一直在cli中运行，不断从消息队列读取数据
        $job = \Ablegang\PhpMq\Queue::reserve('test');
        if (!$job->isEmpty()) {
            echo $job->job_data . PHP_EOL;
            sleep(2);
            if (\Ablegang\PhpMq\Queue::delete($job)) {
                echo "job was deleted" . PHP_EOL;
            } else {
                echo "delete failed" . PHP_EOL;
            }
        }
    }

} catch (\Exception $e) {
    var_dump($e->getMessage());
}