<?php

namespace App\Contract\Request;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HoyolabRequest
{
    private HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client
    )
    {
        $this->client = $client;
    }

    /**
     * @return array
     * Fetch hoyolab article data
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
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
}
