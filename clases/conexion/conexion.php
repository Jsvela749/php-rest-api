<?php

class conexion
{
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;

    function __construct()
    {
        $listadatos = $this->datosConexion();
        foreach ($listadatos as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->database, $this->port);
        if ($this->conexion->connect_errno) {
            echo "Hubo algún error con la conexión";
            die();
        }
    }

    private function datosConexion()
    {
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }

    private function convertirUTF8($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = mb_convert_encoding($item, 'utf-8');
            }
        });
        return $array;
    }

    public function obtenerDatos($query)
    {
        $results = $this->conexion->query($query);
        $resultsArray = array();
        foreach ($results as $key) {
            $resultsArray[] = $key;
        }
        return $this->convertirUTF8($resultsArray);
    }

    public function nonQuery($query)
    {
        $results = $this->conexion->query($query);
        return $this->conexion->affected_rows;
    }

    public function nonQueryId($query)
    {
        $results = $this->conexion->query($query);
        $filas = $this->conexion->affected_rows;
        if ($filas >= 1) {
            return $this->conexion->insert_id;
        } else {
            return 0;
        }
    }

    // Encriptar
    protected function encriptar($string){
        return md5($string);
    }
}

?>