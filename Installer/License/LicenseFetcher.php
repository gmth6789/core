<?php declare(strict_types=1);

namespace Shopware\Core\Installer\License;

use GuzzleHttp\Client;
use Shopware\Core\Installer\Subscriber\InstallerLocaleListener;
use Symfony\Component\HttpFoundation\Request;

class LicenseFetcher
{
    private Client $guzzle;
    private array $tosUrls;

    public function __construct(Client $guzzle, array $tosUrls)
    {
        $this->guzzle = $guzzle;
        $this->tosUrls = $tosUrls;
    }

    public function fetch(Request $request): string
    {
        $locale = $request->attributes->get('_locale');
        $uri = $this->tosUrls[$locale] ?? $this->tosUrls[InstallerLocaleListener::FALLBACK_LOCALE];

        $response = $this->guzzle->get($uri);

        return $response->getBody()->getContents();
    }
}
