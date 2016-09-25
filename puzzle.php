<?php

/**
 * 拼图类
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
}