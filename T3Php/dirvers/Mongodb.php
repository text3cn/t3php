<?php
/**
 * Mongodb操作类 基于官方提供的驱动封装 https://www.php.net/mongodb
 * @author T3
 * @since 2020-03-18
 */

namespace T3Php\dirvers;
use T3Php\core\Db;
use app\common\ErrorCode;

class Mongodb
{
    // 连接对象
    public $conn;
    // 本类对象
    public static $self;

    /**
     * Mongodb constructor.
     * 构造 数据库连接
     * @param string $connString mongodb连接字符串
     * @param array $dbConfig mongodb配置信息
     */
    public function __construct($connString, $dbConfig)
    {
        $this->conn = new \MongoDB\Driver\Manager($connString);
        $this->config = $dbConfig;
    }

    /**
     * 设置表
     * @param string $table 表名
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 新增一条记录并返回主键id
     * @param array $data 文档记录
     */
    public function insert($data)
    {
        $bulk = new \MongoDB\Driver\BulkWrite;
        $_id = $bulk->insert($data);
        $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
        return (string)$_id;
    }

    /**
     * 使用主键查询一条记录
     */
    public function get($id)
    {
        // 主键字符串需要先转成主键对象
        $id = new \MongoDB\BSON\ObjectId($id);
        $query = new \MongoDB\Driver\Query(['_id' => $id]);
        $rows = $this->conn->executeQuery($this->config['dbName'] . '.' . $this->table, $query);
        foreach ($rows as $document) {
        }
        // 对象转数组 并将_id转id
        $document = json_decode(json_encode($document), true);
        if (isset($document['_id']['$oid'])) {
            $document['id'] = $document['_id']['$oid'];
            unset($document['_id']);
        }
        return $document;
    }

    /**
     * 查询多条记录
     * @param array $where 筛选条件
     * @param string $fields 指定查询的字段，多个用半角符号隔开；如果指定除某个字段不取则传一个数组，如 ：['no' => 'password,  tel']
     * @param array $sort 排序 ['id' => SORT_MONGO_DESC, 'number' => SORT_MONGO_ASC ]
     * @param int $page 页码
     * @param int $rows 每页显示记录数
     * @return array $return
     */
    public function all($where, $fields = '', $sort = [], $page = null, $rows = null)
    {
        $filter = $this->filterWhere($where);
        $options = [];
        // 指定查询字段
        if ($fields) {
            // 不取指定字段
            if (is_array($fields)) {
                if (!isset($fields['no'])) {
                    p('参数 $fields 格式不对。');
                }
                $fields = explode(',', $fields['no']);
                $fields = array_filter($fields);
                foreach ($fields as $item) {
                    $item = trim($item);
                    $projection[$item] = 0;
                }
            } // 只取指定字段
            else {
                $fields = explode(',', $fields);
                $fields = array_filter($fields);
                foreach ($fields as $item) {
                    $item = trim($item);
                    $projection[$item] = 1;
                }
                // _id会默认带出，弄掉它
                if (!in_array('_id', $fields)) {
                    $projection['_id'] = 0;
                }
            }
            $options['projection'] = $projection;
        }
        // 排序
        if ($sort) {
            $options['sort'] = $sort;
        }
        // 统计 count
        $count = $this->count($filter);
        // 分页
        if ($page && $rows) {
            $totalPage = ceil($count / $rows);
            if ($page > $totalPage && $totalPage > 0) {
                $page = $totalPage;
            } elseif ($page < 1) {
                $page = 1;
            }
            $skip = ($page - 1) * $rows;
            $options['skip'] = $skip;
            $options['limit'] = $rows;
        }
        // 发起查询
        try {
            $query = new \MongoDB\Driver\Query($filter, $options);
            $list = $this->conn->executeQuery($this->config['dbName'] . '.' . $this->table, $query);
        } catch (\Exception $e) {
            return false;
        }

        // oid强转为字符串
        $data = [];
        foreach ($list as $row) {
            if (isset($row->_id)) {
                $row->_id = (string)$row->_id;
            }
            to_array($row);
            $data[] = $row;
        }
        $return['totalRows'] = $count;
        $return['totalPage'] = $totalPage ?? 0;
        $return['page'] = $page;
        $return['rows'] = $rows;
        $return['list'] = $data;
        return $return;
    }

    /**
     * count
     * @param array $filter 条件
     */
    public function count($filter = [])
    {
        try {
            $query["count"] = $this->table;
            if (!empty($filter)) {
                $query["query"] = $filter;
            }
            $command = new \MongoDB\Driver\Command($query);
            $result = $this->conn->executeCommand($this->config['dbName'], $command);
        } catch (\Exception $e) {
            return 0;
        }

        $res = current($result->toArray());
        $count = 0;
        if ($res) {
            $count = $res->n;
        }
        return $count;
    }

    /**
     * 修改记录,如果where条件不成立会新增一条数据
     * @param array $where 条件
     * @param array $data 要设置的数据
     */
    public function saveAll($where = [], $data = [])
    {
        if (empty($where) || empty($data)) {
            p('Mongodb::saveAll()方法参数不完整，两个参数为必传数组');
            return;
        }
        $filter = $this->filterWhere($where);
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            $filter,
            ['$set' => $data],       // $set 相当于mysql的set
            [
                'multi' => true,    // 为true修改全部 ， false则只修改一条，默认false
                'upsert' => true     // 为true条件不成立会新增一条数据，false不会新增数据，默认false
            ]
        );
        $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
    }

    /**
     * 修改记录,如果where条件不成立不新增数据
     * @param array $where 条件
     * @param array $data 要设置的数据
     */
    public function updateAll($where = [], $data = [])
    {
        if (empty($where) || empty($data)) {
            p('Mongodb::updateAll()方法参数不完整，两个参数为必传非空数组');
            return;
        }
        $filter = $this->filterWhere($where);
        if (empty($filter)) {
            echo '条件为空会将整个库给修改，禁止操作！';
            return;  // 危险，条件为空会把整个数据库给修改了，必须判断。
        }
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            $filter,
            ['$set' => $data],       // $set 相当于mysql的set
            [
                'multi' => true,    // 为true修改全部 ， false则只修改一条，默认false
                'upsert' => false    // 为true条件不成立会新增一条数据，false不会新增数据，默认false
            ]
        );
        $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
    }

    /**
     * 删除
     * @param array $where 删除条件
     * @param int $limit limit 为 1 时，删除第一条匹配数据，为0删除所有匹配上的数据
     */
    public function delete($where, $limit = 0)
    {
        if (!is_numeric($limit)) {
            p('参数$limit必须是数字');
            return;
        }
        $bulk = new \MongoDB\Driver\BulkWrite;
        $filter = $this->filterWhere($where);
        $options['limit'] = $limit;
        $bulk->delete($filter, $options);
        $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
    }

    /**
     * 条件组装
     * $filter官方文档：https://docs.mongodb.com/manual/tutorial/query-documents/
     */
    public function filterWhere($where)
    {
        $filter = [];
        $allow  = ['=', '!=', ':in', '>', '<', '%', ':or', ':regex'];
        try {
            if (empty($where)) {
                return $filter;
            }
            // 全是等于的条件
            $count = count($where);
            $i = 0;
            foreach ($where as $k => $w) {
                if (!in_array($k, $allow)) {
                    $i++;
                }
            }
            // 条件全是等于
            if ($i == $count) {
                $where['='] = $where;
            } // 条件并非全是等于，再遍历一遍判断操作是否合法
            else {
                foreach ($where as $k => $w) {
                    if (!in_array($k, $allow)) {
                        p('操作不合法，没有' . $k . '这样的条件符号。' . "支持的条件符号：\n=\n!=\n:in\n>\n<\n%\n:or\n:regex");
                        return;
                    }
                }
            }
            // 等于
            if (isset($where['='])) {
                foreach ($where['='] as $key => $item) {
                    if ($key == '_id') {
                        $item = new \MongoDB\BSON\ObjectId($item);
                    }
                    $filter[$key] = $item;
                }
            }
            // 不等于
            if (isset($where['!='])) {
                foreach ($where['!='] as $key => $item) {
                    if ($key == '_id') {
                        $item = new \MongoDB\BSON\ObjectId($item);
                    }
                    $filter[$key] = ['$ne' => $item];
                }
            }
            // in
            if (isset($where[':in'])) {
                foreach ($where[':in'] as $key => $item) {
                    $filter[$key] = ['$in' => $item];  // $item类型为array
                }
            }
            // 大于
            if (isset($where['>'])) {
                foreach ($where['>'] as $key => $item) {
                    if ($key == '_id') {
                        $item = new \MongoDB\BSON\ObjectId($item);
                    }
                    $filter[$key] = ['$gt' => $item];
                }
            }
            // 小于
            if (isset($where['<'])) {
                foreach ($where['<'] as $key => $item) {
                    if ($key == '_id') {
                        $item = new \MongoDB\BSON\ObjectId($item);
                    }
                    $filter[$key] = ['$lt' => $item];
                }
            }
            // like
            if (isset($where['%'])) {
                foreach ($where['%'] as $key => $item) {
                    $filter[$key] = new \MongoDB\BSON\Regex(".*{$item}.*", '');
                }
            }
            // or
            if (isset($where[':or'])) {
                foreach ($where[':or'] as $key => $item) {
                    if ($key == '_id') {
                        $item = new \MongoDB\BSON\ObjectId($item);
                    }
                    $or[$key] = $item;
                }
                $filter['$or'] = $or;
            }
            // 使用正则查询
            if (isset($where[':regex'])) {
                foreach ($where[':regex'] as $key => $item) {
                    $filter[$key] = ['$regex' => $item];
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return $filter;
    }

    /**
     * 字段值修改(增1减1操作)
     * @param array $where 修改条件
     * @param array $data [key => value] 要修改的字段名和值
     */
    public function updateNum($where, $data)
    {
        if (empty($where) || empty($data)) {
            p('Mongodb::updateNum()方法参数不完整');
            return;
        }
        $filter = $this->filterWhere($where);
        if (empty($filter)) {
            echo '条件为空会将整个库给修改，禁止操作！';
            return;  // 危险，条件为空会把整个数据库给修改了，必须判断。
        }
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            $filter,
            ['$inc' => $data],       // $inc自增、自减操作 可以接收正的和负的值
            [
                'multi'  => false,   // 为true修改全部 ， false则只修改一条，默认false
                'upsert' => false    // 为true条件不成立会新增一条数据，false不会新增数据，默认false
            ]
        );
        $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
    }

    /**
     * 全局ID查询并更新
     */
    public function getGlobalId()
    {
        try {
            // 查询全局ID信息
            $bulk  = new \MongoDB\Driver\BulkWrite;
            $query = new \MongoDB\Driver\Query([]);
            $rows  = $this->conn->executeQuery($this->config['dbName'] . '.' . $this->table, $query);
            foreach ($rows as $document) {
            }
            $document = json_decode(json_encode($document), true);
            // 更新全局ID
            if (isset($document['_id']['$oid'])) {
                $document['id'] = $document['_id']['$oid'];
                $bulk->update(
                    ['key' => 1],
                    ['$inc' => ['value' => 1]]
                );
                $this->conn->executeBulkWrite($this->config['dbName'] . '.' . $this->table, $bulk);
                return [intval($document["value"] + 1), "success", ErrorCode::SUCCESS];
            } else {
                return ["", "获取失败,请稍后再试", ErrorCode::FAILED];
            }
        } catch (\Exception $e) {
            return ["", $e->getMessage(), ErrorCode::FAILED];
        }
    }

    /**
     * 通用排序(适用于倒序排列)
     * @param int|array $ids 要排序的记录
     * @param int $option 1.默认 2.交换排序(上移 下移)，2.置顶
     * @param string $field 排序字段
     * @return array
     */
    public function sort($ids, $option, $field = 'sort')
    {
        // 转换ID为数组格式
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        // 交换排序时 验证传值ID
        if ($option == SORT_ACTION_EXCHANGE && !isset($ids[1])) {
            return [null, '交换排序的ID不能为空', ErrorCode::FAILED];
        }
        // 查询当前记录
        $model = self::all(["global_id" => intval($ids[0])], "$field");
        if (empty($model['list'])) {
            return [null, $ids[0] . '记录不存在', ErrorCode::FAILED];
        }
        try {
            switch ($option) {
                // 交换排序
                case SORT_ACTION_EXCHANGE:
                    $mod = self::all(['global_id' => intval($ids[1])], "$field");
                    if (empty($mod['list'])) {
                        return [null, $ids[1] . '记录不存在', ErrorCode::FAILED];
                    }
                    $temp = $mod["list"][0][$field];
                    self::updateAll(['global_id' => intval($ids[1])], [$field => intval($model['list'][0][$field])]);
                    self::updateAll(['global_id' => intval($ids[0])], [$field => intval($temp)]);
                    break;
                // 置顶排序
                case SORT_ACTION_TOP:
                    $topSort = self::all([], "$field", [$field => SORT_MONGO_DESC], 1, 1);
                    if (empty($topSort['list'])) {
                        return [null, '查询失败', ErrorCode::FAILED];
                    }
                    self::updateAll(['global_id' => intval($ids[0])], [$field => intval($topSort['list'][0][$field] + 1)]);
                    break;
                // 默认排序
                default :
                    self::updateAll(['global_id' => intval($ids[0])], [$field => intval($ids[0])]);
            }
        } catch (\Exception $e) {
            return [$e->getMessage(), '操作失败', ErrorCode::FAILED];
        }
        return [null, 'success', ErrorCode::SUCCESS];
    }
}

