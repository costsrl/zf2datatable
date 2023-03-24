<?php
namespace Zf2datatable\Event;

use Laminas\EventManager\Event;

class Zf2datatableEvent extends Event
{
    protected $context;
    /**
     * @return the $context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param field_type $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

}

?>