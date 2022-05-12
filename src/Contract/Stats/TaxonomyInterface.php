<?php

namespace App\Contract\Stats;

interface TaxonomyInterface
{
    public const LIKES_MAPPING = 'like_num';
    public const VIEWS_MAPPING = 'view_num';
    public const BOOKMARKS_MAPPING = 'bookmark_num';
    public const SHARES_MAPPING = 'share_num';
    public const REPLIES_MAPPING = 'reply_num';
    public const FOLLOWED_MAPPING = 'followed_cnt';
    public const POSTS_MAPPING = 'post_num';
    public const POSTRELIES_MAPPING = 'replypost_num';
    public const NEWFOLLOWERS_MAPPING = 'new_follower_num';
}
