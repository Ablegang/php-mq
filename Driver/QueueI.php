<?php
// +----------------------------------------------------------------------
// | QueueI.php
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------
// | Time: 2018/12/19 上午11:17
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

namespace Driver;

use Job;

interface QueueI
{
    public function tubes(): array;

    public function put(Job $job): Job;

    public function reserve(string $tube): Job;

    public function delete(Job $job): bool;

    public function jobs(string $tube): array;
}