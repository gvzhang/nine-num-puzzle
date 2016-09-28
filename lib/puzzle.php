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
        array_push($this->_queue, new Node($this->_initPuzzle, 0, ""));
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
     * 使用广度优先算法计算解决路径
     * @return array
     */
    public function computeSolution()
    {
        if ($this->hasSolution()) {
            if ($this->_initPuzzle !== $this->_puzzleTarget) {
                $this->_searched[$this->_initPuzzle] = reset($this->_queue);
                $isSolve = false;
                while (count($this->_queue) > 0 && !$isSolve) {
                    $queue = reset($this->_queue);
                    foreach ($this->_operations as $operation) {
                        if ($this->_reverseOperation[$operation] !== $queue->getOperation()) {
                            if ($changeQueueKey = $this->move($queue->getKey(), $operation)) {
                                if (!isset($this->_searched[$changeQueueKey])) {
                                    //未搜索过,压入队列
                                    $node = new Node($changeQueueKey, $queue->getKey(), $operation);
                                    array_push($this->_queue, $node);
                                    $this->_searched[$changeQueueKey] = $node;
                                }else {
                                    if ($changeQueueKey === $this->_puzzleTarget) {
                                        $isSolve = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    //搜索完毕,出队列
                    array_shift($this->_queue);
                }
            }
            return $this->getPath($this->_puzzleTarget);
        } else {
            return [];
        }
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
        $node = $this->_searched[$nodeKey];
        if ($node->getParentNode() === 0) {
            return array_reverse($path);
        } else {
            array_push($path, $node->getOperation());
            return $this->getPath($node->getParentNode());
        }
    }
}