<?php
namespace Ferme;

abstract class Collection implements \ArrayAccess, \Iterator, \Countable
{
    protected $list = null;

    public function __construct()
    {
        $this->list = array();
    }

    abstract public function create($args = null);
    abstract public function remove($key);

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->list[] = $value;
            return;
        }
        $this->list[$offset] = $value;
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

    public function count()
    {
        return count($this->list);
    }

    /*************************************************************************
     * Iterator
     ************************************************************************/
    public function rewind()
    {
        return reset($this->list);
    }
    public function current()
    {
        return current($this->list);
    }
    public function key()
    {
        return key($this->list);
    }
    public function valid()
    {
        return isset($this->list[$this->key()]);
    }
    public function next()
    {
        return next($this->list);
    }

    /**
     * Renvois un wiki ou un tableau de wiki dont le nom contient la
     * chaine recherchée
     * @todo  Améliorer les fonctions de recherche
     * @param  string $args nom d'un wiki ou '*' pour les avoir tous
     * @return array       liste des wikis correspondant a la recherche
     */
    public function search($string = '*')
    {
        if (!is_string($string)) {
            return array();
        }

        if ($string === '*' or $string === '') {
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

    /** Renvois un wiki ou un tableau de wiki dont le nom contient la chaine
     * recherchée sans prendre en compte la casse.
     * @todo  Améliorer les fonctions de recherche
     * @param  string $args nom d'un wiki ou '*' pour les avoir tous
     * @return array       liste des wikis correspondant a la recherche
     */
    public function searchNoCaseType($string = '*')
    {
        if (!is_string($string)) {
            return array();
        }

        if ($string === '*' or $string === '') {
            return $this->list;
        }

        if ($this->offsetExists($string)) {
            return array($this->list[$string]);
        }

        $selected = array();
        foreach ($this->list as $name => $object) {
            if (strstr(strtolower($name), strtolower($string))) {
                $selected[$name] = $object;
            }
        }

        return $selected;
    }
}
