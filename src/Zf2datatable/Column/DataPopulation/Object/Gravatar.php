<?php
namespace Zf2datatable\Column\DataPopulation\Object;

use Zf2datatable\Column\DataPopulation\ObjectAwareInterface;

class Gravatar implements ObjectAwareInterface
{

    protected $email;

    /**
     *
     * @param  string     $name
     * @param  mixed      $value
     * @throws \Exception
     */
    private function setParameter($name, $value)
    {
        if ($name == 'email') {
            $this->email = (string) $value;
        } else {
            throw new \InvalidArgumentException('Not allowed parameter: ' . $name);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Zf2datatable\Column\DataPopulation\ObjectAwareInterface::setParameterFromColumn()
     */
    public function setParameterFromColumn($name, $value)
    {
        $this->setParameter($name, $value);
    }

    /**
     * (non-PHPdoc)
     * @see \Zf2datatable\Column\DataPopulation\ObjectAwareInterface::toString()
     */
    public function toString()
    {

        $hash = '';
        if ($this->email != '') {
            $hash = md5($this->email);
        }

        return 'http://www.gravatar.com/avatar/' .$hash;
    }
}
