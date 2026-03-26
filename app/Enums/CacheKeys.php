<?php

namespace App\Enums;

enum CacheKeys: string
{
    case USERS_LIST = 'users_list';
    case BRANDS_LIST = 'brands_list';
    case CHANNELS_LIST = 'channels_list';
    case LOCALES_LIST = 'locales_list';
}