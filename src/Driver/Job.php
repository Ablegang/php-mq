<?php
// +----------------------------------------------------------------------
// | Job.php
// +----------------------------------------------------------------------
// | Description: 任务对象
// +----------------------------------------------------------------------
// | Time: 2018/12/19 下午3:19
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

namespace Ablegang\PhpMq\Driver;

class Job
{
    public $id = null;
    public $tube;
    public $status;
    public $job_data;
    public $attempts;
    public $sort;
    public $reserved_at;
    public $available_at;
    public $created_at;

    public static $field = [
        'id', 'tube', 'status',
        'job_data', 'attempts',
        'sort', 'reserved_at',
        'available_at', 'created_at',
    ];

    public static $field_string
        = 'id,tube,status,job_data,attempts,sort,' .
        'reserved_at,available_at,created_at';

    public static function arr2job($jobs)
    {
        $real_jobs = [];
        foreach ($jobs as $v) {
            if (!is_array($v)) {
                $v = json_decode($v, true);
            }
            $real_jobs[] = new Job($v);
        }
        return $real_jobs;
    }

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        $this->created_at = time();
        $this->available_at = $this->created_at;
    }

    public function isEmpty()
    {
        return $this->job_data ? false : true;
    }
}