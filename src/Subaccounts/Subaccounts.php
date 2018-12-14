<?php

namespace Seatsio\Subaccounts;

use Seatsio\Charts\Chart;
use Seatsio\PageFetcher;
use Seatsio\SeatsioJsonMapper;

class Subaccounts
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var SubaccountLister
     */
    public $active;

    /**
     * @var SubaccountLister
     */
    public $inactive;

    public function __construct($client)
    {
        $this->client = $client;
        $this->active = new SubaccountLister(new PageFetcher('/subaccounts/active', $this->client, function () {
            return new SubaccountPage();
        }));
        $this->inactive = new SubaccountLister(new PageFetcher('/subaccounts/inactive', $this->client, function () {
            return new SubaccountPage();
        }));
    }

    /**
     * @var $id int
     * @return Subaccount
     */
    public function retrieve($id)
    {
        $res = $this->client->get('/subaccounts/' . $id);
        return \GuzzleHttp\json_decode($res->getBody());
    }

    /**
     * @var $name string
     * @return Subaccount
     */
    public function create($name = null)
    {
        return $this->doCreate(null, $name);
    }

    /**
     * @var $email string
     * @var $name string
     * @return Subaccount
     */
    public function createWithEmail($email, $name = null)
    {
        return $this->doCreate($email, $name);
    }

    /**
     * @return Subaccount
     */
    private function doCreate($email = null, $name = null)
    {
        $request = new \stdClass();
        if ($email !== null) {
            $request->email = $email;
        }
        if ($name !== null) {
            $request->name = $name;
        }
        $res = $this->client->post('/subaccounts', ['json' => $request]);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = SeatsioJsonMapper::create();
        return $mapper->map($json, new Subaccount());
    }

    /**
     * @var $id int
     * @var $name string
     * @return void
     */
    public function update($id, $name = null, $email = null)
    {
        $request = new \stdClass();
        if ($name != null) {
            $request->name = $name;
        }
        if ($email != null) {
            $request->email = $email;
        }
        $this->client->post('/subaccounts/' . $id, ['json' => $request]);
    }

    /**
     * @var $id int
     * @return void
     */
    public function activate($id)
    {
        $this->client->post('/subaccounts/' . $id . '/actions/activate');
    }

    /**
     * @var $id int
     * @return void
     */
    public function deactivate($id)
    {
        $this->client->post('/subaccounts/' . $id . '/actions/deactivate');
    }

    /**
     * @var $id int
     * @return string
     */
    public function regenerateSecretKey($id)
    {
        $res = $this->client->post('/subaccounts/' . $id . '/secret-key/actions/regenerate');
        $json = \GuzzleHttp\json_decode($res->getBody());
        return $json->secretKey;
    }

    /**
     * @var $id int
     * @return string
     */
    public function regenerateDesignerKey($id)
    {
        $res = $this->client->post('/subaccounts/' . $id . '/designer-key/actions/regenerate');
        $json = \GuzzleHttp\json_decode($res->getBody());
        return $json->designerKey;
    }

    /**
     * @var $id int
     * @var $chartKey string
     * @return Chart
     */
    public function copyChartToParent($id, $chartKey)
    {
        $res = $this->client->post('/subaccounts/' . $id . '/charts/' . $chartKey . '/actions/copy-to/parent');
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = SeatsioJsonMapper::create();
        return $mapper->map($json, new Chart());
    }

    /**
     * @var $fromId int
     * @var $toId int
     * @var $chartKey string
     * @return Chart
     */
    public function copyChartToSubaccount($fromId, $toId, $chartKey)
    {
        $res = $this->client->post('/subaccounts/' . $fromId . '/charts/' . $chartKey . '/actions/copy-to/' . $toId);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = SeatsioJsonMapper::create();
        return $mapper->map($json, new Chart());
    }

    /**
     * @param $subaccountListParams SubaccountListParams
     * @return SubaccountPagedIterator
     */
    public function listAll($subaccountListParams = null)
    {
        return $this->iterator()->all($this->listParamsToArray($subaccountListParams));
    }

    /**
     * @param $pageSize int
     * @param $subaccountListParams SubaccountListParams
     * @return SubaccountPage
     */
    public function listFirstPage($subaccountListParams = null, $pageSize = null)
    {
        return $this->iterator()->firstPage($this->listParamsToArray($subaccountListParams), $pageSize);
    }

    /**
     * @param $afterId int
     * @param $subaccountListParams SubaccountListParams
     * @param $pageSize int
     * @return SubaccountPage
     */
    public function listPageAfter($afterId, $subaccountListParams = null, $pageSize = null)
    {
        return $this->iterator()->pageAfter($afterId, $this->listParamsToArray($subaccountListParams), $pageSize);
    }

    /**
     * @param $beforeId int
     * @param $subaccountListParams SubaccountListParams
     * @param $pageSize int
     * @return SubaccountPage
     */
    public function listPageBefore($beforeId, $subaccountListParams = null, $pageSize = null)
    {
        return $this->iterator()->pageBefore($beforeId, $this->listParamsToArray($subaccountListParams), $pageSize);
    }

    /**
     * @return FilterableSubaccountLister
     */
    private function iterator()
    {
        return new FilterableSubaccountLister(new PageFetcher('/subaccounts', $this->client, function () {
            return new SubaccountPage();
        }));
    }

    private function listParamsToArray($subaccountListParams)
    {
        if ($subaccountListParams == null) {
            return [];
        }
        return $subaccountListParams->toArray();
    }

}