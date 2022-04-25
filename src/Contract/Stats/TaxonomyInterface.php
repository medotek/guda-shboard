<?php

namespace App\Contract\Stats;

use App\Entity\HoyolabStatType;

interface TaxonomyInterface
{
    public const VIEWS = 'views';
    public const LIKES = 'likes';
    public const SHARES = 'shares';
    public const REPLIES = 'REPLIES';
    public const BOOKMARKS = 'bookmarks';

    public const ALL_TAXONOMIES = [
        self::VIEWS,
        self::LIKES,
        self::SHARES,
        self::REPLIES,
        self::BOOKMARKS,
    ];

    /**
     * Get statType entity
     * @param $taxonomy
     * @return \App\Entity\HoyolabStatType
     */
    public function getStatType($taxonomy): HoyolabStatType;
}
