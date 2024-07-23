<?php

class Scores extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $s_id;

    /**
     *
     * @var string
     */
    public $s_munic;

    /**
     *
     * @var string
     */
    public $s_contest;

    /**
     *
     * @var integer
     */
    public $s_score;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("qsadmin_niyogyugan_scoring");
        $this->setSource("scores");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Scores[]|Scores|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Scores|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
