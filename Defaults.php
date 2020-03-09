<?php declare(strict_types=1);

namespace Shopware\Core;

/**
 * @Internal
 * System wide defaults that are fixed for performance measures
 */
final class Defaults
{
    public const SALES_CHANNEL = '98432def39fc4624b33213a56b8c944d';

    /**
     * Don't depend on this being en-GB, the underlying language can be overwritten by the installer!
     */
    public const LANGUAGE_SYSTEM = '2fbb5fe2e29a4d70aa5854ce7ce3e20b';

    public const FALLBACK_CUSTOMER_GROUP = 'cfbd5018d38d41d8adca10d94fc8bdd6';

    public const LIVE_VERSION = '0fa91ce3e96a4bc2be4bd9ce752c3425';

    /**
     * Don't depend on this being EUR, the underlying currency can be overwritten by the installer!
     */
    public const CURRENCY = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';

    public const SALES_CHANNEL_TYPE_API = 'f183ee5650cf4bdb8a774337575067a6';

    public const SALES_CHANNEL_TYPE_STOREFRONT = '8a243080f92e4c719546314b577cf82b';

    public const SALES_CHANNEL_TYPE_PRODUCT_COMPARISON = 'ed535e5722134ac1aa6524f73e26881b';

    public const SALES_CHANNEL_TYPE_GOOGLE_SHOPPING = 'eda0a7980ee745fbbb7e58202dcdc04f';

    public const STORAGE_DATE_TIME_FORMAT = 'Y-m-d H:i:s.v';

    /**
     * Do not use STORAGE_DATE_FORMAT for createdAt fields, use STORAGE_DATE_TIME_FORMAT instead
     */
    public const STORAGE_DATE_FORMAT = 'Y-m-d';
}
