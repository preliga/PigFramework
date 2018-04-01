<?php

/**
 * Created by PhpStorm.
 * User: Piotr
 */

namespace library\PigFramework\action;

use library\PigFramework\model\{
    Registry, Session, Statement, View
};

/**
 * Class Action
 * @package library\Pig\action
 */
abstract class Action
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Statement
     */
    protected $statement;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Action constructor.
     * @param string $file
     */
    public function __construct(string $file, string $url)
    {
        $this->file = $file;
        $this->url = $url;

        $this->registry = Registry::getInstance();
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     *
     */
    public function init()
    {
        $this->view = new View($this->file);
        $this->statement = Statement::getInstance();
    }

    /**
     *
     */
    public function permissionBase()
    {
    }

    /**
     *
     */
    public function permissionStandard()
    {
    }

    /**
     *
     */
    public function permissionJSON()
    {
    }

    /**
     *
     */
    public function preActionStandard()
    {
    }

    /**
     *
     */
    public function preActionJSON()
    {
    }

    /**
     *
     */
    public function preAction()
    {
    }

    /**
     *
     */
    abstract public function onAction();

    /**
     *
     */
    public function postAction()
    {
    }

    /**
     *
     */
    public function prepareRequest()
    {
        $this->view->prepareRequest();
    }

    /**
     *
     */
    public function render()
    {
        $data = [
            'post' => $this->getPost(),
            'params' => $this->getParams(),
            'statement' => $this->statement->popStatements()
        ];

        $this->view->render($data);
    }

    public function forward(
        $url = '/',
        bool $status = true,
        string $message = "",
        array $data = []
    )
    {
        $this->view->status = $status ? 'success' : 'error';

        if (!empty($message)) {
            $this->statement->pushStatement($this->view->status, $message);
        }
        $this->view->message = $message;

        if ($this->hasParam('json')) {
            $this->view->prepareRequest($data);
            die();
        } else {

            if (!empty($params)) {
                $query = '?';
                foreach ($params as $key => $val) {
                    $query .= "$key=$val&";
                }

                $query = substr($query, 0, -1);

                $url .= $query;
            }
        }

        $this->url = $url;
    }

    /**
     * @param string $url
     * @param array $params
     * @param bool $status
     * @param string $message
     * @param array $data
     */
    public function redirect(
        string $url = "/",
        array $params = [],
        bool $status = true,
        string $message = "",
        array $data = []
    )
    {
        $this->view->status = $status ? 'success' : 'error';

        if (!empty($message)) {
            $this->statement->pushStatement($this->view->status, $message);
        }
        $this->view->message = $message;

        if ($this->hasParam('json')) {
            $this->view->prepareRequest($data);
            die();
        } else {

            if (!empty($params)) {
                $query = '?';
                foreach ($params as $key => $val) {
                    $query .= "$key=$val&";
                }

                $query = substr($query, 0, -1);

                $url .= $query;
            }

            header("Location: {$url}");
            die();
        }
    }

    /**
     * @param string $path
     */
    public function addJS(string $path)
    {
        $this->view->scriptLoader->addJS($path);
    }

    /**
     * @param string $path
     */
    public function addCSS(string $path)
    {
        $this->view->scriptLoader->addCSS($path);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $_GET;
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function getParam($name, $default = null)
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasParam($name): bool
    {
        return isset($_GET[$name]);
    }

    /**
     * @param null $name
     * @param null $default
     * @return null
     */
    public function getPost($name = null, $default = null)
    {
        if (!empty($name)) {
            return $_POST[$name] ?? $default;
        } else {
            return $_POST ?? $default;
        }
    }

    /**
     * @param null $name
     * @return bool
     */
    public function hasPost($name = null): bool
    {
        if (!empty($name)) {
            return !empty($_POST[$name]);
        } else {
            return !empty($_POST);
        }
    }
}
