<?php
namespace Ferme;

abstract class Factory implements \ArrayAccess
{
    protected $list = null;

    public function __construct($args = null)
    {
        $this->list = array();
        $this->init($args);
    }

    abstract protected function init($args = null);
    abstract public function create($args = null);
    abstract public function remove($key);

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->list[] = $value;
        } else {
            $this->list[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->list[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->list[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->list[$offset]) ? $this->list[$offset] : null;
    }

    public function exist($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Renvois un wiki ou un tableau de wiki dont le nom contient la
     * @todo  Améliorer les fonctions de recherche
     * @param  string $args nom d'un wiki ou '*' pour les avoir tous
     * @return array       liste des wikis correspondant a la recherche
     */
    public function search($string = '*')
    {
        if (!is_string($string)) {
            return array();
        }

        if ('*' === $string) {
            return $this->list;
        }

        if ($this->offsetExists($string)) {
            return array($this->list[$string]);
        }

        $selected = array();
        foreach ($this->list as $name => $object) {
            if (strstr($name, $string)) {
                $selected[$name] = $object;
            }
        }

        return $selected;
    }
}
