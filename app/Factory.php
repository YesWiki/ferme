<?php
namespace Ferme;

abstract class Factory
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

    /**
     * Remets a zéro l'index sur la liste.
     */
    public function resetIndex()
    {
        reset($this->list);
    }

    /**
     * Passe a l'objet suivant et le retourne
     * @return bool Si il n'y a pas d'objet
     * @return Objet Une instance d'un objet de la list
     */
    public function getNext()
    {
        if (!next($this->list)) {
            return false;
        }
        return current($this->list);
    }

    public function isExist($key)
    {
        if (array_key_exists($key, $this->list)) {
            return true;
        }
        return false;
    }

    public function getCurrent()
    {
        return current($this->list);
    }

    public function count()
    {
        return count($this->list);
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

        if ($this->isExist($string)) {
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
