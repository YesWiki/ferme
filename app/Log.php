<?php
namespace Ferme;

class Log
{
    private $file = null;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function write($user, $action)
    {
        $date = date("Y-m-d G:i:s");
        file_put_contents(
            $this->file,
            "$date : $user : $action\n",
            FILE_APPEND
        );
    }
}
