<?php

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
     * 初始拼图数组
     * @var array
     */
    private $_initPuzzle = [];

    /**
     * 目标数组
     * @var array
     */
    private $_puzzleTarget = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];

    /**
     * 操作单元格位置
     * @var array
     */
    private $_gridPosition;

    /**
     * 搜索的队列
     * @var array
     */
    private $_queue = [];

    /**
     * 搜索的队列KEY值
     * @var array
     */
    private $_queueKeys = [];

    /**
     * 获取二维数组形式的组合
     * @return array
     */
    public function getPuzzle()
    {
        $outPuzzle = [];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $outPuzzle[$i][$j] = $this->_initPuzzle[$i * 3 + $j];
            }
        }
        return $this->_initPuzzle;
    }

    /**
     * 初始化拼图数组
     * Puzzle constructor.
     * @param $horizontal
     * @param $vertical
     */
    public function __construct($horizontal, $vertical)
    {
        $product = $horizontal * $vertical;
        for ($i = 0; $i < $product; $i++) {
            $randNum = $this->randNumFn($product);
            if ($randNum == $product) {
                $this->_gridPosition = $i;
                $this->_initPuzzle[$i] = 0;
            } else {
                $this->_initPuzzle[$i] = $randNum;
            }
        }
        array_push($this->_queue, $this->_initPuzzle);
        array_push($_queueKeys, md5($this->_initPuzzle));
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
        foreach ($this->_initPuzzle as $key => $val) {
            if ($val) {
                foreach ($this->_initPuzzle as $key2 => $val2) {
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
            $isSolve = false;
            while (count($this->_queue) > 0 && !$isSolve) {
                $queue = reset($this->_queue);
                foreach ($this->_operations as $operation) {
                    if ($changeQueue = $this->move($queue, $operation)) {
                        $changeQueueMd5 = md5($changeQueue);
                        if (!in_array($changeQueueMd5, $this->_queueKeys)) {
                            array_unshift($this->_queue, $changeQueue);
                            array_push($this->_queue, $changeQueueMd5);
                        }
                        if ($changeQueue === $this->_puzzleTarget) {
                            $isSolve = true;
                            break;
                        }
                    }
                }
                array_shift($this->_queue);
            }
            return $changeQueue;
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
        $temp = $queue;
        switch ($operation) {
            case self::OPERATION_UP:
                if (!in_array($this->_gridPosition, [0, 1, 2])) {
                    $queue[$this->_gridPosition] = $queue[$this->_gridPosition - 3];
                    $queue[$this->_gridPosition - 3] = 0;
                }
                break;
            case self::OPERATION_DOWN:
                if (!in_array($this->_gridPosition, [6, 7, 8])) {
                    $queue[$this->_gridPosition] = $queue[$this->_gridPosition + 3];
                    $queue[$this->_gridPosition + 3] = 0;
                }
                break;
            case self::OPERATION_LEFT:
                if (!in_array($this->_gridPosition, [0, 3, 6])) {
                    $queue[$this->_gridPosition] = $queue[$this->_gridPosition - 1];
                    $queue[$this->_gridPosition - 1] = 0;
                }
                break;
            case self::OPERATION_RIGHT:
                if (!in_array($this->_gridPosition, [2, 5, 8])) {
                    $queue[$this->_gridPosition] = $queue[$this->_gridPosition + 1];
                    $queue[$this->_gridPosition + 1] = 0;
                }
                break;
        }
        if ($temp === $queue) {
            return false;
        } else {
            return $queue;
        }
    }
}