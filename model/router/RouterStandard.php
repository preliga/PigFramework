<?php

/**
 * Created by PhpStorm.
 * User: Piotr
 */

namespace library\PigFramework\model\router;

use library\PigFramework\action\Action;
use library\PigFramework\model\Config;

//use library\PigFramework\model\PigException;

/**
 * Class RouterStandard
 * @package library\PigFramework\model\router
 */
class RouterStandard implements Routable
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var Action
     */
    protected $action;

    /**
     * Router constructor.
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl = null)
    {
        if (empty($baseUrl)) {
            $baseUrl = $_SERVER['HTTP_HOST'] . '/';
        }

        $this->baseUrl = $baseUrl;
    }

    public function generateRoutersPaths()
    {
        if (
            !Config::getInstance()->getConfig('enableCreationRoutes')
            &&
            file_exists(Config::getInstance()->getConfig('cachePath') . "routes_" . APPLICATION_ENV . ".json")
        ) {
            return;
        }

        function findActions($rootPath, $url, &$routes)
        {
            if ($handle = opendir($rootPath)) {

                while (false !== ($entry = readdir($handle))) {
                    if (in_array($entry, ['.', '..'])) {
                        continue;
                    }

                    $path = "{$rootPath}\\{$entry}";

                    if (is_dir($path)) {
                        findActions($path, "{$url}/{$entry}", $routes);
                    } else if (strpos($path, 'php')) {

                        $action = substr($path, 0, -4);

                        require_once $path;

                        $rc = new \ReflectionClass($action);
                        $doc = $rc->getDocComment();

                        if (!empty($doc)) {
                            $posRoute = strpos($doc, '@Route') + 8;
                            $length = strpos($doc, '"', $posRoute) - $posRoute;
                            $route = substr($doc, $posRoute, $length);

                        } else {
                            $route = substr("{$url}/{$entry}", 0, -4);
                        }

                        $routes[$route] = [
                            'file' => $path,
                            'action' => $action
                        ];
                    }
                }
                closedir($handle);
            }
        }

        $appPath = Config::getInstance()->getConfig('appPath');
        $appPath = substr($appPath, 0, -1);

        $bsaeUrl = '';
        $route = [];

        findActions($appPath, $bsaeUrl, $route);

        if (file_exists(Config::getInstance()->getConfig('routesDefined'))) {
            $routesDefined = json_decode(file_get_contents(Config::getInstance()->getConfig('routesDefined')), true);
            $route = array_merge($route, $routesDefined);
        }

        $routeFile = Config::getInstance()->getConfig('cachePath') . "routes_" . APPLICATION_ENV . ".json";
        file_put_contents($routeFile, json_encode($route));
    }

    /**
     * @param string $url
     * @throws \Exception
     */
    public function route(string $url)
    {
        $this->generateRoutersPaths();

        while (true) {
            $route = json_decode(file_get_contents(Config::getInstance()->getConfig('cachePath') . "routes_" . APPLICATION_ENV . ".json"), true);

            require_once $route[$url]['file'];
            $this->action = new $route[$url]['action'](substr($route[$url]['file'], 0, -4), $url);

            if (!empty($this->action)) {
                $this->action->init();
                if ($this->action->getURL() != $url) {
                    $url = $this->action->getURL();
                    continue;
                }

                $this->action->permissionBase();
                if ($this->action->getURL() != $url) {
                    $url = $this->action->getURL();
                    continue;
                }

                if (!$this->action->hasParam('json')) {
                    $this->action->permissionStandard();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }

                    $this->action->preActionStandard();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }

                } else {
                    $this->action->permissionJSON();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }

                    $this->action->preActionJSON();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }
                }

                $this->action->preAction();
                if ($this->action->getURL() != $url) {
                    $url = $this->action->getURL();
                    continue;
                }
                $this->action->onAction();
                if ($this->action->getURL() != $url) {
                    $url = $this->action->getURL();
                    continue;
                }
                $this->action->postAction();
                if ($this->action->getURL() != $url) {
                    $url = $this->action->getURL();
                    continue;
                }
                if (!$this->action->hasParam('json')) {
                    $this->action->render();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }
                } else {
                    $this->action->prepareRequest();
                    if ($this->action->getURL() != $url) {
                        $url = $this->action->getURL();
                        continue;
                    }
                }

            } else {
                throw new \Exception("Błąd przekierowanie: Action not found");
            }

            break;
        }
    }
}
