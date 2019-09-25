<?php

namespace Rgergo67\LaravelMailman;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Rgergo67\LaravelMailman\Exceptions\EmailAlreadySubscribedException;
use Rgergo67\LaravelMailman\Exceptions\EmailNotFoundException;
use Rgergo67\LaravelMailman\Exceptions\InvalidEmailException;
use Rgergo67\LaravelMailman\Exceptions\NonExistingListException;
use Rgergo67\LaravelMailman\Exceptions\ResourceNotFoundException;

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
     * Send an HTTP request to mailman api.
     *
     * @param  string  $method  HTTP method
     * @param  string  $endpoint  API endpoint
     * @param  array  $body  request's parameters
     *
     * @return string
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws ResourceNotFoundException
     */
    protected function sendRequest($method, $endpoint, $body = [])
    {
        try {
            $response = $this->client->request($method, $endpoint, ['query' => $body]);
            $content = $response->getBody()->getContents();
            return $content;
        } catch (ClientException $e) {
            if ($e->getCode() === ResourceNotFoundException::MAILMAN_CODE) {
                throw new ResourceNotFoundException;
            }
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
        if (!is_null($json)) {
            return isset($json->entries)
                ? $json->entries
                : $json;
        }
        return [];
    }

    /**
     * Return all lists
     *
     * @return array
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws ResourceNotFoundException
     */
    public function lists()
    {
        $response = $this->sendRequest('GET', 'lists');
        return $this->getEntries($response);
    }

    /**
     * Find a list by its name.
     *
     * @param $name string fqdn_listname or list_id
     *
     * @return array|null
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     */
    protected function getList($name)
    {
        try {
            $response = $this->sendRequest('GET', "lists/{$name}");
            return $this->getEntries($response);
        } catch (ResourceNotFoundException $e) {
            throw new NonExistingListException;
        }
    }

    /**
     * Returns lists where the given email address has a role (member, owner, etc)
     *
     * @param $email
     *
     * @return array
     * @throws EmailNotFoundException
     * @throws GuzzleException
     * @throws InvalidEmailException
     */
    public function getMemberships($email)
    {
        try {
            $response = $this->sendRequest('GET', "addresses/{$email}/memberships");
            return $this->getEntries($response);
        } catch (ResourceNotFoundException $e) {
            throw new EmailNotFoundException;
        }
    }

    /**
     * Returns a given member of a list
     *
     * @param $listName string Can be either fqdn_listname or list_id
     * @param $email string Email address to look for
     *
     * @return mixed
     * @throws EmailNotFoundException
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     */
    public function getListMember($listName, $email)
    {
        try {
            $response = $this->sendRequest('GET', "lists/{$listName}/member/{$email}");
            $member = $this->getEntries($response);

            if (empty($member)) {
                throw new EmailNotFoundException;
            }

            return $member;
        } catch (ResourceNotFoundException $e) {
            // Either e-mail or list could be missing, let's find out which one
            $list = $this->getList($listName);
            if (empty($list)) {
                throw new NonExistingListException;
            } else {
                throw new EmailNotFoundException;
            }
        }
    }

    /**
     * @param $listName string Can be either fqdn_listname or list_id
     * @param $userName string User's name
     * @param $email string Email address to unsubscibe
     *
     * @return void
     * @throws EmailAlreadySubscribedException
     * @throws GuzzleException
     * @throws InvalidEmailException
     * @throws NonExistingListException
     * @throws ResourceNotFoundException
     */
    public function subscribe($listName, $userName, $email)
    {
        $list = $this->getList($listName);
        try {
            $this->sendRequest('POST', 'members', [
                'list_id' => $list->list_id,
                'display_name' => $userName,
                'subscriber' => $email,
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
     * Unsubscribes an email address from a list
     *
     * @param $listName string Can be either fqdn_listname or list_id
     * @param $email string Email address to unsubscibe
     *
     * @return void
     * @throws EmailNotFoundException
     * @throws GuzzleException
     * @throws NonExistingListException
     * @throws ResourceNotFoundException
     * @throws InvalidEmailException
     */
    public function unsubscribe($listName, $email)
    {
        $member = $this->getListMember($listName, $email);
        $this->sendRequest('DELETE', "members/{$member->member_id}");
    }
}
