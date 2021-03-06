<?php

namespace Seatsio\Subaccounts;

class Subaccount
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var \Seatsio\Workspaces\Workspace
     */
    public $workspace;
    /**
     * @var string
     */
    public $secretKey;
    /**
     * @var string
     */
    public $designerKey;
    /**
     * @var string
     */
    public $publicKey;
    /**
     * @var string
     */
    public $name;
    /**
     * @var boolean
     */
    public $active;

}
