<?php

require "node.php";

/**
 * 8数码拼图
 * Class Puzzle
 */
class Puzzle
{
    /**
     * 向上移动
     */
    const OPERATION_UP = 0;

    /**
     * 向下移动
     */
    const OPERATION_DOWN = 1;

    /**
     * 向左移动
     */
    const OPERATION_LEFT = 2;

    /**
     * 向右移动
     */
    const OPERATION_RIGHT = 3;

    /**
     * 正向扩展
     */
    const FORWARD_DIRECTION = 0;

    /**
     * 反向扩展
     */
    const REVERSE_DIRECTION = 1;

    /**
     * 操作数组
     * @var array
     */
    private $_operations = [self::OPERATION_UP, self::OPERATION_DOWN, self::OPERATION_LEFT, self::OPERATION_RIGHT];

    /**
     * 操作数组说明
     * @var array
     */
    public static $operationText = [self::OPERATION_UP => "↑", self::OPERATION_DOWN => "↓", self::OPERATION_LEFT => "←", self::OPERATION_RIGHT => "→"];

    /**
     * 各对立操作,循环筛选使用
     * @var array
     */
    private $_reverseOperation = [self::OPERATION_UP => self::OPERATION_DOWN, self::OPERATION_DOWN => self::OPERATION_UP,
        self::OPERATION_LEFT => self::OPERATION_RIGHT, self::OPERATION_RIGHT => self::OPERATION_LEFT];

    /**
     * 初始拼图数组
     * @var string
     */
    private $_initPuzzle = "";

    /**
     * 目标数组
     * @var string
     */
    private $_puzzleTarget = "1,2,3,4,5,6,7,8,0";

    /**
     * 搜索的队列
     * @var array
     */
    private $_queue = [];

    /**
     * 已经搜索的数组集合
     * @var array
     */
    private $_searched = [];

    /**
     * 获取二维数组形式的组合
     * @return array
     */
    public function getPuzzle()
    {
        $outPuzzle = [];
        $initPuzzle = explode(",", $this->_initPuzzle);
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $value = $initPuzzle[$i * 3 + $j];
                $outPuzzle[$i][$j] = $value == 0 ? "" : $value;
            }
        }
        return $outPuzzle;
    }

    /**
     * 初始化拼图数组
     * Puzzle constructor.
     * @param $horizontal
     * @param $vertical
     */
    public function __construct($horizontal, $vertical)
    {
        $initPuzzle = [];
        $product = $horizontal * $vertical;
        for ($i = 0; $i < $product; $i++) {
            $randNum = $this->randNumFn($product);
            if ($randNum == $product) {
                $initPuzzle[$i] = 0;
            } else {
                $initPuzzle[$i] = $randNum;
            }
        }

        //组合成逗号字符串形式，方便存储比较
        foreach ($initPuzzle as $val) {
            $this->_initPuzzle .= $val . ",";
        }
        $this->_initPuzzle = rtrim($this->_initPuzzle, ",");

        //初始扩展状态
        $initPuzzleNode = new Node($this->_initPuzzle, 0, "");
        $targetPuzzleNode = new Node($this->_puzzleTarget, 0, "");
        $this->_queue[self::FORWARD_DIRECTION][] = $initPuzzleNode;
        $this->_queue[self::REVERSE_DIRECTION][] = $targetPuzzleNode;
        $this->_searched[self::FORWARD_DIRECTION][$this->_initPuzzle] = $initPuzzleNode;
        $this->_searched[self::REVERSE_DIRECTION][$this->_puzzleTarget] = $targetPuzzleNode;
    }

    /**
     * 取得随机数
     * @param $maxNum
     * @return int
     */
    private function randNumFn($maxNum)
    {
        static $insertedArr = [];
        $randNum = rand(1, $maxNum);
        if (!in_array($randNum, $insertedArr)) {
            array_push($insertedArr, $randNum);
            return $randNum;
        } else {
            return $this->randNumFn($maxNum);
        }
    }

    /**
     * 当前8数码是否有解
     *（1）当初始状态棋局的棋子数列的逆序数是奇数时，八数码问题无解；
     *（2）当初始状态棋局的棋子数列的逆序数是偶数时，八数码问题有解。
     * http://blog.csdn.net/wonengxing/article/details/6869219
     * @return bool
     */
    public function hasSolution()
    {
        $totalGreaterNum = 0;
        $initPuzzleArr = explode(",", $this->_initPuzzle);
        foreach ($initPuzzleArr as $key => $val) {
            if ($val) {
                foreach ($initPuzzleArr as $key2 => $val2) {
                    if ($key2 < $key) {
                        if ($val2 > $val) {
                            $totalGreaterNum++;
                        }
                    } else {
                        break;
                    }
                }
            }
        }
        return $totalGreaterNum % 2 == 0 ? true : false;
    }

    /**
     * 使用广度优先算法计算解决路径（DBFS双向宽搜算法）
     * 下一层选择结点个数较少的那个方向先扩展
     * @return array
     */
    public function computeSolution()
    {
        if ($this->hasSolution() && $this->_initPuzzle !== $this->_puzzleTarget) {
            $linkQueue = null;
            $isSolve = false;
            while (!$isSolve) {
                $forwardCount = count($this->_queue[self::FORWARD_DIRECTION]);
                $reverseCount = count($this->_queue[self::REVERSE_DIRECTION]);
                if ($forwardCount > 0 && $reverseCount > 0) {
                    if ($forwardCount > $reverseCount) {
                        $expandResult = $this->expand(self::REVERSE_DIRECTION);
                    } else {
                        $expandResult = $this->expand(self::FORWARD_DIRECTION);
                    }
                    $isSolve = $expandResult["flag"];
                    $linkQueue = $expandResult["node"];
                } else {
                    break;
                }
            }
            return array_merge($this->getPath($linkQueue), $this->getPathEnd($linkQueue));
        } else {
            return [];
        }
    }

    /**
     * 扩展正向（逆向）结点
     * @param $direction
     * @return array
     */
    private function expand($direction)
    {
        $isSolve = false;
        $linkQueue = null;
        $levelQueue = [];
        foreach ($this->_queue[$direction] as $queue) {
            foreach ($this->_operations as $operation) {
                if ($this->_reverseOperation[$operation] !== $queue->getOperation()) {
                    if ($changeQueueKey = $this->move($queue->getKey(), $operation)) {
                        if (!isset($this->_searched[$direction][$changeQueueKey])) {
                            //未搜索过,压入队列
                            $node = new Node($changeQueueKey, $queue->getKey(), $operation);
                            array_push($levelQueue, $node);
                            $this->_searched[$direction][$changeQueueKey] = $node;

                            if ($isSolve = $this->checkMeet($direction, $changeQueueKey)) {
                                $linkQueue = $changeQueueKey;
                                break;
                            }
                        }
                    }
                }
            }
            if ($isSolve) break;
        }
        $this->_queue[$direction] = $levelQueue;
        return ["flag" => $isSolve, "node" => $linkQueue];
    }

    /**
     * 对比扩展节点是否存在相等的
     * @param $direction
     * @param $queueKey
     * @return boolean
     */
    private function checkMeet($direction, $queueKey)
    {
        foreach ($this->_searched[1 - $direction] as $node) {
            if ($node->getKey() === $queueKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * 移动拼图组合
     * @param $queue
     * @param $operation
     * @return bool
     */
    private function move($queue, $operation)
    {
        $queue = explode(",", $queue);
        $temp = $queue;
        $zeroPosition = array_search(0, $queue);
        switch ($operation) {
            case self::OPERATION_UP:
                if (!in_array($zeroPosition, [0, 1, 2])) {
                    $queue[$zeroPosition] = $queue[$zeroPosition - 3];
                    $queue[$zeroPosition - 3] = 0;
                }
                break;
            case self::OPERATION_DOWN:
                if (!in_array($zeroPosition, [6, 7, 8])) {
                    $queue[$zeroPosition] = $queue[$zeroPosition + 3];
                    $queue[$zeroPosition + 3] = 0;
                }
                break;
            case self::OPERATION_LEFT:
                if (!in_array($zeroPosition, [0, 3, 6])) {
                    $queue[$zeroPosition] = $queue[$zeroPosition - 1];
                    $queue[$zeroPosition - 1] = 0;
                }
                break;
            case self::OPERATION_RIGHT:
                if (!in_array($zeroPosition, [2, 5, 8])) {
                    $queue[$zeroPosition] = $queue[$zeroPosition + 1];
                    $queue[$zeroPosition + 1] = 0;
                }
                break;
        }
        if ($temp === $queue) {
            return false;
        } else {
            return implode(",", $queue);
        }
    }

    /**
     * 还原路径
     * @param $nodeKey
     * @return array
     */
    private function getPath($nodeKey)
    {
        static $path = [];
        $node = $this->_searched[self::FORWARD_DIRECTION][$nodeKey];
        if ($node->getParentNode() === 0) {
            return array_reverse($path);
        } else {
            array_push($path, $node->getOperation());
            return $this->getPath($node->getParentNode());
        }
    }

    /**
     * 还原路径(尾部开始搜索)
     * @param $nodeKey
     * @return array
     */
    private function getPathEnd($nodeKey)
    {
        static $path = [];
        $node = $this->_searched[self::REVERSE_DIRECTION][$nodeKey];
        if ($node->getParentNode() === 0) {
            return $path;
        } else {
            array_push($path, $this->_reverseOperation[$node->getOperation()]);
            return $this->getPathEnd($node->getParentNode());
        }
    }
}