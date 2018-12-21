<?php
// +----------------------------------------------------------------------
// | Mq.php
// +----------------------------------------------------------------------
// | Description: 队列工具
// +----------------------------------------------------------------------
// | Time: 2018/12/19 上午11:15
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

class Queue
{

    /**
     * @var 驱动
     */
    public static $driver = null;

    /**
     * @param string $driver
     */
    public static function init($driver = 'Mysql')
    {
        $class = "Driver\\$driver";
        self::$driver = new $class;
    }

    /**
     * @return array
     * 获取管道列表
     */
    public static function tubes(): array
    {
        return self::$driver->tubes();
    }

    /**
     * @param Job $job
     * @return Job
     * 放入任务
     */
    public static function put(Job $job): Job
    {
        return self::$driver->put($job);
    }

    /**
     * @param string $tube
     * @return Job
     * 接收任务
     */
    public static function reserve(string $tube = 'default'): Job
    {
        return self::$driver->reserve($tube);
    }

    /**
     * @param string $tube
     * @return array
     * 获取任务列表
     */
    public static function jobs(string $tube = 'default'): array
    {
        return self::$driver->jobs($tube);
    }

    /**
     * @param Job $job
     * @return bool
     * 删除任务
     */
    public static function delete(Job $job): bool
    {
        return self::$driver->delete($job);
    }
}