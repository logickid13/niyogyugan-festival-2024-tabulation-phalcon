<?php

class FloatVotes extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $iD;

    /**
     *
     * @var string
     */
    public $eMAIL;

    /**
     *
     * @var string
     */
    public $fULLNAME;

    /**
     *
     * @var string
     */
    public $aDDRESS;

    /**
     *
     * @var string
     */
    public $cELLPHONE_NUMBER;

    /**
     *
     * @var string
     */
    public $fACEBOOK_PROFILE;

    /**
     *
     * @var integer
     */
    public $dATA_PRIVACY;

    /**
     *
     * @var string
     */
    public $fLOAT_VOTE_CHOICES;

    /**
     *
     * @var string
     */
    public $dATE_REGISTERED;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("qsadmin_niyogyugan_scoring");
        $this->setSource("float_votes");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FloatVotes[]|FloatVotes|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FloatVotes|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
