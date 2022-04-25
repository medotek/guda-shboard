<?php

namespace App\Helper\Discord;

class EmbedBuilder
{
    public function __construct() {}

    public static function hoyolabNotification($embed): array
    {
        $s = '';
        $x = '';
        if ($embed['news'] > 1) {
            $s = 's';
            $x = 'x';
        }

        $desc = "Vous avez **{$embed['news']}** nouveau{$x} message{$s} sur ce post hoyo";

        return [
            "color" => 6651640,
            "title" => $embed['subject'],
            "url" => "https://hoyolab.com/article/{$embed['postId']}",
            "description" => $desc,
            "fields" => [
                [
                    "name" => "**Views**",
                    "value" => $embed['stats']['view'],
                    "inline" => true
                ],
                [
                    "name" => "**Replies**",
                    "value" => $embed['stats']['reply'],
                    "inline" => true
                ],
                [
                    "name" => "**Likes**",
                    "value" => $embed['stats']['like'],
                    "inline" => true
                ]
            ],
            "thumbnail" => [
                "url" => $embed['hoyoUserImage']
            ]
        ];
    }
}
