<?php

namespace App\Contract\Request;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HoyolabRequest
{
    private HttpClientInterface $client;

    public const HEADERS = [
        'headers' => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ];

    public function __construct(
        HttpClientInterface $client
    )
    {
        $this->client = $client;
    }

    /**
     * Fetch hoyolab article data
     * @param int $id
     * @return array
     * @throws TransportExceptionInterface
     */
    public function updateHoyolabPost(int $id): array
    {
        $hoyolabArticle = 'https://bbs-api-os.hoyolab.com/community/post/wapi/getPostFull?gids=2&post_id=' . $id . '&read=1';

        $response = $this->client->request('GET', $hoyolabArticle);
        if ($response->getStatusCode() === 200) {
            try {
                return $response->toArray();
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
                // TODO : logger
                dump($e);
            }
        }
        return ['error' => []];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendDiscordEmbed($webhook, $send): ResponseInterface
    {
        return $this->client->request('POST', $webhook . '?wait=true', [
            self::HEADERS,
            'body' => json_encode($send)
        ]);
    }

    /**
     * @param string $uid
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getHoyolabUserFullInformations(string $uid): ResponseInterface
    {
        return $this->client->request("GET",
            "https://bbs-api-os.hoyolab.com/community/painter/wapi/user/full?uid={$uid}",
            self::HEADERS
        );
    }
}
