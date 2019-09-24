<?php

namespace Rgergo67\LaravelMailman;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Rgergo67\LaravelMailman\Exceptions\EmailAlreadySubscribedException;
use Rgergo67\LaravelMailman\Exceptions\EmailNotFoundException;
use Rgergo67\LaravelMailman\Exceptions\InvalidEmailException;
use Rgergo67\LaravelMailman\Exceptions\NonExistingListException;

class Mailman
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Mailman constructor.
     *
     * @param  Client  $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send an HTTP request.
     *
     * @param  string  $method  HTTP method
     * @param  string  $endpoint  API endpoint
     * @param  array  $body  request's parameters
     *
     * @return string
     * @throws GuzzleException
     * @throws InvalidEmailException
     */
    protected function sendRequest($method, $endpoint, $body = [])
    {
        try {
            $response = $this->client->request($method, $endpoint, ['query' => $body]);
            $content = $response->getBody()->getContents();
            return $content;
        } catch (ClientException $e) {
            $errorMessage = ClientException::getResponseBodySummary($e->getResponse());
            if ($errorMessage === InvalidEmailException::MAILMAN_ERROR) {
                throw new InvalidEmailException;
            }
            throw $e;
        }
    }

    /**
     * Get the entries of a response.
     *
     * @param string $response request's response
     * @return array
     */
    protected function getEntries($response)
    {
        $json = json_decode($response, false, 512, JSON_BIGINT_AS_STRING);
        $array = isset($json->entries) ? $json->entries : [];
        return $array;
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws InvalidEmailException
     */
    public function lists()
    {
        $response = $this->sendRequest('GET', 'lists');
        return $this->getEntries($response);
    }

    /**
     * Find a list by its name.
     *
     * @param $fqdnName
     *
     * @return array|null
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     */
    protected function getListByFqdnName($fqdnName)
    {
        $lists = $this->lists();
        $index = array_search($fqdnName, array_column($lists, 'fqdn_listname'));
        if ($index !== false) {
            return $lists[$index];
        } else {
            throw new NonExistingListException;
        }
    }

    /**
     * Returns if a request was successful or not.
     *
     * @param string|null $response request's response
     * @return bool
     */
    protected function getStatus($response)
    {
        return ! is_null($response);
    }

    /**
     * @param $userEmail
     *
     * @return array
     * @throws GuzzleException
     * @throws InvalidEmailException
     */
    public function membership($userEmail)
    {
        $response = $this->sendRequest('GET', "addresses/{$userEmail}/memberships");
        return $this->getEntries($response);
    }

    /**
     * @param $listId
     * @param $userEmail
     *
     * @return mixed
     * @throws EmailNotFoundException
     * @throws GuzzleException
     * @throws InvalidEmailException
     */
    public function getListMemberById($listId, $userEmail)
    {
        $memberships = $this->membership($userEmail);
        $key = array_search(
            $listId,
            array_column($memberships, 'list_id')
        );
        if ($key !== false) {
            return $memberships[$key];
        } else {
            throw new EmailNotFoundException;
        }
    }

    /**
     * @param $listFqdnName
     * @param $userName
     * @param $userEmail
     *
     * @return bool
     * @throws EmailAlreadySubscribedException
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     */
    public function subscribe($listFqdnName, $userName, $userEmail)
    {
        $list = $this->getListByFqdnName($listFqdnName);
        try {
            $response = $this->sendRequest('POST', 'members', [
                'list_id' => $list->list_id,
                'display_name' => $userName,
                'subscriber' => $userEmail,
                'pre_verified' => true,
                'pre_confirmed' => true,
                'pre_approved' => true,
            ]);
        } catch (ClientException $e) {
            $errorMessage = ClientException::getResponseBodySummary($e->getResponse());
            if ($errorMessage === EmailAlreadySubscribedException::MAILMAN_ERROR) {
                throw new EmailAlreadySubscribedException;
            }
        }
    }

    /**
     * @param $listFqdnName
     * @param $userEmail
     *
     * @return bool
     * @throws EmailNotFoundException
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     */
    public function unsubscribe($listFqdnName, $userEmail)
    {
        $response = null;
        $list = $this->getListByFqdnName($listFqdnName);
        $member = $this->getListMemberById($list->list_id, $userEmail);

        try {
            $response = $this->sendRequest('DELETE', "members/{$member->member_id}");
        } catch (ClientException $e) {
            $errorMessage = ClientException::getResponseBodySummary($e->getResponse());
            if ($errorMessage === EmailNotFoundException::MAILMAN_ERROR) {
                throw new EmailNotFoundException;
            }
        }

        return $this->getStatus($response);
    }
}
