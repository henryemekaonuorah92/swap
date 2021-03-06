<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap;

use Exchanger\Exchanger;
use Exchanger\Service\Chain;
use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Psr\SimpleCache\CacheInterface;
use Swap\Service\Factory;

/**
 * Helps building Swap.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Builder
{
    /**
     * The services.
     *
     * @var array
     */
    private $services = [];

    /**
     * The options.
     *
     * @var array
     */
    private $options = [];

    /**
     * The http client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * The request factory.
     *
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * The cache.
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Adds a service.
     *
     * @param string $serviceName
     * @param array  $options
     *
     * @return Builder
     */
    public function add(string $serviceName, array $options = []): self
    {
        $this->services[$serviceName] = $options;

        return $this;
    }

    /**
     * Uses the given http client.
     *
     * @param HttpClient $httpClient
     *
     * @return Builder
     */
    public function useHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Uses the given request factory.
     *
     * @param RequestFactory $requestFactory
     *
     * @return Builder
     */
    public function useRequestFactory(RequestFactory $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * Uses the given simple cache.
     *
     * @param CacheInterface $cache
     *
     * @return Builder
     */
    public function useSimpleCache(CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Builds Swap.
     *
     * @return Swap
     */
    public function build(): Swap
    {
        $serviceFactory = new Factory($this->httpClient, $this->requestFactory);
        $services = [];

        foreach ($this->services as $name => $options) {
            $services[] = $serviceFactory->create($name, $options);
        }

        $service = new Chain($services);
        $exchanger = new Exchanger($service, $this->cache, $this->options);

        return new Swap($exchanger);
    }
}
