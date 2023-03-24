<?php
namespace Zf2datatable\Session;

use Laminas\Session\Container;

class Zf2Container extends Container
{

    public function getItem($key) {
        return parent::offsetGet($key);
    }

    public function setItem($key ,$value) {
       parent::offsetSet($key,$value);
        return $this;
    }


    public function setTag($key ,$value) {
        parent::offsetSet($key,$value);
        return $this;
    }

}

?>