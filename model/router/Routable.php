<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 2018-04-02
 * Time: 12:32
 */

namespace library\PigFramework\model\router;

/**
 * Interface Routable
 * @package library\PigFramework\model\router
 */
interface Routable
{
    public function route(string $url);
}