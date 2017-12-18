<?php

namespace Seatsio\Charts;

use JsonMapper;
use Psr\Http\Message\StreamInterface;
use Seatsio\Events\EventLister;
use Seatsio\Events\EventPage;
use Seatsio\PageFetcher;

class Charts
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    private $pageSize;

    public function __construct($client, $pageSize)
    {
        $this->client = $client;
        $this->pageSize = $pageSize;
    }

    /**
     * @return Chart
     */
    public function create($name = null, $venueType = null, $categories = null)
    {
        $request = new \stdClass();
        if ($name) {
            $request->name = $name;
        }
        if ($venueType) {
            $request->venueType = $venueType;
        }
        if ($categories) {
            $request->categories = $categories;
        }
        $res = $this->client->request('POST', '/charts', ['json' => $request]);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = new JsonMapper();
        return $mapper->map($json, new Chart());
    }

    /**
     * @return void
     */
    public function update($key, $name = null, $categories = null)
    {
        $request = new \stdClass();
        if ($name) {
            $request->name = $name;
        }
        if ($categories) {
            $request->categories = $categories;
        }
        $this->client->request('POST', '/charts/' . $key, ['json' => $request]);
    }

    /**
     * @return Chart
     */
    public function retrieve($key)
    {
        $res = $this->client->request('GET', '/charts/' . $key);
        return \GuzzleHttp\json_decode($res->getBody());
    }

    /**
     * @return mixed
     */
    public function retrievePublishedChartVersion($key)
    {
        $res = $this->client->request('GET', '/charts/' . $key . '/version/published');
        return \GuzzleHttp\json_decode($res->getBody());
    }

    /**
     * @return void
     */
    public function publishDraft($key)
    {
        $this->client->request('POST', '/charts/' . $key . '/version/draft/actions/publish');
    }

    /**
     * @return void
     */
    public function discardDraft($key)
    {
        $this->client->request('POST', '/charts/' . $key . '/version/draft/actions/discard');
    }

    /**
     * @return StreamInterface
     */
    public function retrieveThumbnail($key)
    {
        $res = $this->client->request('GET', '/charts/' . $key . '/version/published/thumbnail');
        return $res->getBody();
    }

    /**
     * @return ChartLister
     */
    public function lister()
    {
        return new ChartLister(new PageFetcher('/charts', $this->client, $this->pageSize, function () {
            return new ChartPage();
        }));
    }

    /**
     * @return EventLister
     */
    public function events($key)
    {
        return new EventLister(new PageFetcher('/charts/' . $key . '/events', $this->client, $this->pageSize, function () {
            return new EventPage();
        }));
    }

}