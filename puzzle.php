<?php

/**
 * 8数码拼图
 * Class Puzzle
 */
class Puzzle
{
    /**
     * 拼图数组
     * @var array
     */
    private $_puzzleArr;

    /**
     * 获取拼图
     * @return array
     */
    public function getPuzzleArr()
    {
        return $this->_puzzleArr;
    }

    /**
     * 初始化拼图数组
     * Puzzle constructor.
     * @param $horizontal
     * @param $vertical
     */
    public function __construct($horizontal, $vertical)
    {
        $this->_puzzleArr = [];
        $product = $horizontal * $vertical;
        for ($i = 0; $i < $horizontal; $i++) {
            for ($j = 0; $j < $vertical; $j++) {
                $randNum = $this->randNumFn($product);
                $this->_puzzleArr[$i][$j] = $randNum == $product ? "" : $randNum;
            }
        }
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
    public function isAvailable()
    {
        $oneDimensional = [];
        foreach ($this->_puzzleArr as $key => $arr) {
            foreach ($arr as $val) {
                array_push($oneDimensional, $val);
            }
        }
        $totalGreaterNum = 0;
        foreach ($oneDimensional as $key => $val) {
            if ($val) {
                foreach ($oneDimensional as $key2 => $val2) {
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

    public function computeSolution()
    {
        
    }
}