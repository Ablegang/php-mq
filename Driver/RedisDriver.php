<?php
// +----------------------------------------------------------------------
// | Redis.php
// +----------------------------------------------------------------------
// | Description: redis驱动
// +----------------------------------------------------------------------
// | Time: 2018/12/19 上午11:17
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

namespace Driver;

class RedisDriver implements QueueI
{

    private $conn;
    private $config;
    private $tubes_key;

    public function __construct($options = [])
    {
        $this->conn = new \Redis();
        $this->conn->connect($options['ip'], $options['port']);
        if (isset($options['password'])) {
            $this->conn->auth($options['password']);
        }
        $this->tubes_key = $options['tubes'];
    }

    public function tubes(): array
    {
        // 使用 sorted-set 存储当前拥有的队列，比如你 default、test、sms 队列
        return $this->conn->zRange($this->tubes_key, 0, -1);
    }

    public function jobs(string $tube): array
    {
        return Job::arr2job($this->conn->lRange($tube, 0, -1));
    }

    public function put(Job $job): Job
    {
        // 维护 tube 集合，可实现不重复
        $this->conn->zAdd($this->tubes_key, 1, $job->tube);

        // 用 list 存储队列内容，返回的队列长度，就是这个 job 在 list 中的下标
        if ($id = $this->conn->lPush($job->tube, json_encode($job))) {
            $job->id = $id;
        } else {
            throw new \RedisException('插入失败');
        }
        return $job;
    }

    public function delete(Job $job): bool
    {
        // 在 redis 的 list 中不可使用 lRem 来删除具体项，具体原因，在后面测试一节描述
        return true;
    }

    public function reserve(string $tube): Job
    {
        // redis 的rPop在接收时就会将 job 从 list 中删除，所以，没有 reserve 状态
        if ($data = $this->conn->rPop($tube)) {
            $job = json_decode($data, true);
        }
        return new Job($job ?? []);
    }
}