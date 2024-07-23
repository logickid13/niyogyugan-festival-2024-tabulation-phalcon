<?php

class Contests extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $c_id;

    /**
     *
     * @var string
     */
    public $c_name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("qsadmin_niyogyugan_scoring");
        $this->setSource("contests");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Contests[]|Contests|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Contests|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
