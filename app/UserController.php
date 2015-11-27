<?php
namespace Ferme;

/**
 * Classe UserController
 *
 * gère les entrées ($_POST et $_GET)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class UserController
{
    /**
     * Contient la configuration de la ferme.
     * @var Configuration
     */
    private $configuration;

    /**
     * Constructeur
     * @param Configuration $configuration Contient la configuration de la ferme.
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Vérifie les identifiants d'un utilisateur et le connecte si ils sont bon.
     * @param  string $username identifiant a tester
     * @param  string $password mot de passe a tester
     * @return bool             Vrai si la connexion a réussie, faux sinon.
     */
    public function login($username, $password)
    {
        $list_user = $this->configuration['users'];
        foreach ($list_user as $valid_username => $hash) {
            if (($valid_username == $username)
                and password_verify($password, $hash)
            ) {
                $_SESSION['username'] = $username;
                $_SESSION['logged'] = true;
                return true;
            }
        }
        return false;
    }

    /**
     * Supprime informations concernant la connexion dans $_session
     */
    public function logout()
    {
        foreach (array('username', 'logged') as $value) {
            if (isset($_SESSION[$value])) {
                unset($_SESSION[$value]);
            }
        }
    }

    /**
     * Détermine si un utilisateur est connecté
     * @return boolean Vrai si un utilisateur est connecté, faux sinon.
     */
    public function isLogged()
    {
        if (isset($_SESSION['username'])
            and isset($_SESSION['logged'])
            and true == $_SESSION
        ) {
            return true;
        }
        return false;
    }

    /**
     * Retourne l'identifiant de l'utilisateur connecté.
     * @return string Le nom d'utilisateur si connecté, sinon vide
     */
    public function whoIsLogged()
    {
        if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }
        return '';
    }
}
