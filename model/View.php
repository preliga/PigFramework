<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 */

namespace library\PigFramework\model;

/**
 * Class View
 * @package library\Pig\model
 */
class View
{
    /**
     * @var string
     */
    public $file;

    /**
     * @var bool
     */
    public $fileJsExist;

    /**
     * @var string
     */
    public $template;

    /**
     * @var array
     */
    public $path;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $message;

    /**
     * @var ScriptLoader
     */
    public $scriptLoader;

    /**
     * View constructor.
     * @param string $file
     * @param string $template
     */
    public function __construct(string $file, string $template = 'standard')
    {
        $this->file = $file;
        $this->fileJsExist = file_exists("$this->file.js");

        $this->template = $template;
        $this->path = explode(DIRECTORY_SEPARATOR, $file);

        $this->status = "success";
        $this->message = "";

        $this->scriptLoader = new ScriptLoader();
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @param array $data
     */
    public function render(array $data)
    {
        $smarty = new \Smarty();
        $smartyOptions = Config::getInstance()->getConfig('smarty');

        foreach ($smartyOptions as $key => $val) {
            $smarty->$key = $val;
        }

        $this->assignVariable($smarty, $data);
        $tpl = "layout/templates/{$this->template}.tpl";

        if (file_exists($tpl)) {
            $smarty->display($tpl);
        } else {
            die(var_dump("Not found template: $tpl"));
        }
    }

    /**
     * @param array $data
     */
    public function prepareRequest(array $data = [])
    {
        if (empty($data)) {
            $data = get_object_vars($this);
            unset($data['file']);
            unset($data['fileJsExist']);
            unset($data['template']);
            unset($data['path']);
            unset($data['status']);
            unset($data['message']);
            unset($data['scriptLoader']);
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $data, 'status' => $this->status, 'message' => $this->message]);
    }

    /**
     * @param \Smarty $smarty
     * @param array $data
     */
    private function assignVariable(\Smarty $smarty, array $data)
    {
        $result = array_merge(get_object_vars($this), $data);

        foreach ($result as $key => $var) {
            $smarty->assign($key, $var);
        }

        $data = json_encode($result);

        echo "<script>var view = $data</script>";
    }
}