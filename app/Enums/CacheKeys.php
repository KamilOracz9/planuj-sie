<?php

namespace App\Enums;

enum CacheKeys: string
{
    case USERS_LIST = 'users_list';
    case BRANDS_LIST = 'brands_list';
    case CHANNELS_LIST = 'channels_list';
    case LOCALES_LIST = 'locales_list';
    case CATEGORIES_LIST = 'categories_list';
    case PRODUCTS_LIST = 'products_list';
    case VARIANTS_LIST = 'variants_list';
    case ATTRIBUTES_LIST = 'attributes_list';
    case ATTRIBUTES_WITH_TYPE_LIST = 'attributes_with_type_list';
    case ATTRIBUTE_VALUES_LIST = 'attribute_values_list';
    case ATTRIBUTE_OPTIONS_LIST = 'attribute_options_list';
    case ATTRIBUTE_OPTIONS_SELECT = 'attribute_options_select';
    case ATTRIBUTE_TYPES_LIST = 'attribute_types_list';

    case CATEGORIES_SELECT = 'categories_select';
    case PRODUCTS_SELECT = 'products_select';
    case ATTRIBUTE_TYPES_SELECT = 'attribute_types_select';
    case ATTRIBUTES_SELECT = 'attributes_select';
    case ATTRIBUTE_VALUES_SELECT_BY_MODEL = 'attribute_values_select_by_model';
}
