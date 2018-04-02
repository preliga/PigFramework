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
 * Class Router
 * @package library\Pig
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

    /**
     * @param string $url
     * @throws \Exception
     */
    public function route(string $url)
    {
        while (true) {
            $path = substr($url, 1);

            $this->action = null;
            if (empty($path) || $path == '/' || $path == '/index.php') {
                $appHomePath = Config::getInstance()->getConfig('appHomePath');
                $actionString = "{$appHomePath}";
            } else {
                $appPath = Config::getInstance()->getConfig('appPath');
                $actionString = "{$appPath}{$path}";
            }

            $actionPath = str_replace('\\', '/', $actionString);
            $actionString = str_replace('/', '\\', $actionString);

            require "{$actionPath}.php";
            $this->action = new $actionString($actionPath, $url);

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
