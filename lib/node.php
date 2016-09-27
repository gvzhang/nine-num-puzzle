<?php

/**
 * @link https://github.com/JohnZhang360/zgjian-framework
 */
class Node
{
    private $_key;
    private $_parent_node;
    private $_operation;

    /**
     * node constructor.
     * @param $_key
     * @param $_parent_node
     * @param $_operation
     */
    public function __construct($_key, $_parent_node, $_operation)
    {
        $this->_key = $_key;
        $this->_parent_node = $_parent_node;
        $this->_operation = $_operation;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * @param mixed $from_operation
     */
    public function setOperation($from_operation)
    {
        $this->_operation = $from_operation;
    }

    /**
     * @return mixed
     */
    public function getParentNode()
    {
        return $this->_parent_node;
    }

    /**
     * @param mixed $parent_node
     */
    public function setParentNode($parent_node)
    {
        $this->_parent_node = $parent_node;
    }
}