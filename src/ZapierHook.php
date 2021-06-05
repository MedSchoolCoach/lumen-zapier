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
     * @return \MedSchoolCoach\HttpClient\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __call($name, $arguments)
    {
        $hookId = Arr::get($this->hookList, $name);
        $arguments = $this->addGlobals($arguments);

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
     * @param array $arguments
     * @return array
     */
    protected function addGlobals(array $arguments): array
    {
        if ($global = config('zapier.zaps.global-data')) {
            if ($querystring = Arr::get($global, 'querystring')) {
                $arguments[0] = array_merge(Arr::get($arguments, 0, []), $querystring);
            }

            if ($body = Arr::get($global, 'body')) {
                $arguments[1] = array_merge(Arr::get($arguments, 1, []), $body);
            }
        }

        return $arguments;
    }

    /**
     * @param string $hookId
     * @param array|null $queryData
     * @return string
     */
    protected function buildUrl(string $hookId, ?array $queryData): string
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
    protected function parseConfig(string $config): array
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
