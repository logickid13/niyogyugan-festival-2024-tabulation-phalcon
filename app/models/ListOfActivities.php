<?php

class ListOfActivities extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $a_id;

    /**
     *
     * @var string
     */
    public $c_id;

    /**
     *
     * @var string
     */
    public $a_datetime;

    /**
     *
     * @var string
     */
    public $a_venue;

    /**
     *
     * @var string
     */
    public $a_icon;

    /**
     *
     * @var string
     */
    public $a_guidelines;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("qsadmin_niyogyugan_scoring");
        $this->setSource("list_of_activities");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ListOfActivities[]|ListOfActivities|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ListOfActivities|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
