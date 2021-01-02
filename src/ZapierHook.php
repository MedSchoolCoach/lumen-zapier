<?php

namespace MedSchoolCoach\LumenZapier;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MedSchoolCoach\HttpClient\Request;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class ZapierHook
{
    /**
     * @var Request
     */
    private Request $httpRequest;

    /**
     * @var string
     */
    private string $hookUrl;

    /**
     * @var string
     */
    private string $hookGroupId;

    /**
     * @var array
     */
    private array $hookList;

    /**
     * ZapierHook constructor.
     * @param string $url
     * @param string $groupId
     * @param string $hooksConfig
     * @param Request $httpRequest
     */
    public function __construct(string $url, string $groupId, string $hooksConfig, Request $httpRequest)
    {
        $this->hookUrl = $url;
        $this->hookGroupId = $groupId;
        $this->hookList = $this->parseConfig($hooksConfig);
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param $name
     * @param $arguments
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __call($name, $arguments)
    {
        $hookId = Arr::get($this->hookList, $name);

        if (!$hookId) {
            throw new MethodNotFoundException(sprintf(
                'Method `%s::%s()` is not defined. Check .env ZAPIER_HOOKS.', get_class($this), $name
            ), get_class($this), $name, $arguments);
        }

        if (!Arr::has($arguments, 1)) {
            $this->httpRequest
                ->bodyFormat((string)NULL);
        }

        return $this->httpRequest->post(
            $this->buildUrl($hookId, Arr::get($arguments, 0)),
            Arr::has($arguments, 1) ? Arr::get($arguments, 1) : []);
    }

    /**
     * @param string $hookId
     * @param array|null $queryData
     * @return string
     */
    private function buildUrl(string $hookId, ?array $queryData): string
    {
        $slash = (string)NULL;
        $query = (string)NULL;

        if (substr($this->hookUrl, -1) !== '/') {
            $slash = '/';
        }

        if ($queryData) {
            $query = '?' . http_build_query($queryData);
        }

        return "{$this->hookUrl}${slash}{$this->hookGroupId}/${hookId}${query}";
    }

    /**
     * @param string $config
     * @return array
     */
    private function parseConfig(string $config): array
    {
        $config = str_replace(': ', ':', $config);
        $list = [];

        foreach (explode(' ', $config) as $hook) {
            $hookData = explode(':', $hook);

            $list[Str::camel(Arr::first($hookData))] = Arr::last($hookData);
        }

        return $list;
    }
}
