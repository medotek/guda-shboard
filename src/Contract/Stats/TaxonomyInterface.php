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

    public const LIKES_MAPPING = 'like_num';
    public const VIEWS_MAPPING = 'view_num';
    public const BOOKMARKS_MAPPING = 'bookmark_num';
    public const SHARES_MAPPING = 'share_num';
    public const REPLIES_MAPPING = 'reply_num';

    public const ALL_TAXONOMIES = [
        self::VIEWS_MAPPING => self::VIEWS,
        self::LIKES_MAPPING =>self::LIKES,
        self::SHARES_MAPPING => self::SHARES,
        self::REPLIES_MAPPING => self::REPLIES,
        self::BOOKMARKS_MAPPING => self::BOOKMARKS,
    ];

    /**
     * Get statType entity
     * @param $taxonomy
     * @return \App\Entity\HoyolabStatType
     */
    public function getStatType($taxonomy): HoyolabStatType;
}
