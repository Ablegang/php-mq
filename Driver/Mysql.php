<?php
// +----------------------------------------------------------------------
// | Mysql.php
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------
// | Time: 2018/12/19 上午11:17
// +----------------------------------------------------------------------
// | Author: Object,半醒的狐狸<2252390865@qq.com>
// +----------------------------------------------------------------------

namespace Driver;

use Job;

class Mysql implements QueueI
{
    private $conn;
    private $config;
    private $table;
    private $select_suffix;
    private $delete_suffix;
    private $update_suffix;
    private $insert_suffix;

    public function __construct()
    {
        $config_path = ROOT . '/config.php';
        if (!file_exists($config_path)) {
            throw new \Exception('配置文件不存在：' . $config_Path);
        }

        $this->config = (include_once $config_path)['mysql'];

        $this->conn = new \PDO(
            $this->config['dsn'],
            $this->config['username'],
            $this->config['password']
        );

        $field_string = Job::$field_string;
        $this->table = $this->config['table'];
        $this->select_suffix = "SELECT {$field_string} FROM {$this->table}";
        $this->delete_suffix = "DELETE FROM {$this->table}";
        $this->update_suffix = "UPDATE {$this->table}";
        $this->insert_suffix = "INSERT INTO {$this->table}";
    }

    public function tubes(): array
    {
        $sql = "SELECT `tube` FROM {$this->table} GROUP BY `tube`";
        $res = $this->conn->query($sql);
        if (!$res) {
            throw new \PDOException('查询错误：' . $sql . '-错误提示：' . json_encode($statement->errorInfo()));
        }

        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete(Job $job): bool
    {
        if (!$job->id) {
            throw new \Exception('job id 不能为空');
        }
        $sql = "{$this->delete_suffix} WHERE id = :id";
        $statement = $this->conn->prepare($sql);
        $res = $statement->execute([':id' => $job->id]);
        return $res;
    }

    public function jobs(string $tube): array
    {
        $sql = "{$this->select_suffix} WHERE tube = :tube";
        $statement = $this->conn->prepare($sql);
        $res = $statement->execute([':tube' => $tube]);
        if (!$res) {
            throw new \PDOException('查询错误：' . $sql . '-错误提示：' . json_encode($statement->errorInfo()));
        }

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function put(Job $job): Job
    {
        // 组装sql
        $sql = "{$this->insert_suffix}";
        $field = '';
        $prepare = '';
        $value = []; // 直接进行赋值是没问题的，但如果直接进行运算就不行
        foreach (Job::$field as $v) {
            // null的值无需加入到预处理中
            if ($job->$v) {
                $field .= "{$v},";
                $prepare .= ":{$v},";
                $value[":{$v}"] = $job->$v;
            }
        }
        $field = '(' . trim($field, ',') . ')';
        $prepare = '(' . trim($prepare, ',') . ')';
        $sql = "{$sql} {$field} VALUES {$prepare}";

        // 执行sql
        $statement = $this->conn->prepare($sql);
        $res = $statement->execute($value);

        // 结果
        if (!$res) {
            throw new \PDOException("插入错误：" . $sql . '-错误提示：' . json_encode($statement->errorInfo()));
        }
        $job->id = $this->conn->lastInsertId();

        return $job;
    }

    public function reserve(string $tube): Job
    {
        $time = time();
        $over_time = $time - $this->config['ttr'];
        $sql = "{$this->select_suffix} WHERE (status = 'ready' OR (status = 'reserved' AND reserved_at <= {$over_time})) AND available_at <= {$time} AND tube = :tube ORDER BY sort limit 1";
        $statement = $this->conn->prepare($sql);
        $res = $statement->execute([':tube' => $tube]);
        if (!$res) {
            throw new \PDOException('查询错误：', $sql);
        }

        if ($data = $statement->fetch()) {
            $job = new Job($data);
            $attempts = $job->attempts + 1;
            $time = time();
            $sql = "{$this->update_suffix} SET status='reserved',attempts = {$attempts},reserved_at = {$time} WHERE id = {$job->id}";
            $rows = $this->conn->exec($sql);
            if ($rows <= 0) {
                throw new \PDOException('更新出错：' . $sql . '-错误提示：' . json_encode($statement->errorInfo()));
            }

            return $job;
        }

        return new Job();
    }
}