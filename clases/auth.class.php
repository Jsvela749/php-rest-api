<?php 

    require_once 'conexion/conexion.php';
    require_once 'respuestas.class.php';

    class auth extends conexion{
        public function login($json){
            $_respuestas = new respuestas();
            $datos = json_decode($json, true);
            if(!isset($datos['usuario']) || !isset ($datos['password'])){
                // Error con los campos
                return $_respuestas->error_400();
            }else{
                // Todo ha salido bien
                $usuario = $datos['usuario'];
                $password = $datos['password'];
                $password = parent::encriptar($password);
                $datos = $this->obtenerDatosUsuario($usuario);
                if($datos){
                    // Verificar la contraseña
                    if($password == $datos[0]['Password']){
                        if($datos[0]['Estado'] == 'Activo'){
                            // Crear el token
                            $verificar = $this->insertarToken($datos[0]['UsuarioID']);
                            if($verificar){
                                // Si se guardo
                                $result = $_respuestas->response;
                                $result['result'] = array('token' => $verificar );
                                return $result;
                            }else{
                                return $_respuestas->error_500("Error interno, no hemos podido generar el token");
                            }
                        }else{
                            return $_respuestas->error_200("El usuario esta inactivo");
                        }
                    }else{
                        return $_respuestas->error_200("El password es invalido");
                    }
                }else{
                    // No existe el usuario
                    return $_respuestas->error_200("El usuario $usuario no existe");
                }
            }
        }

        private function obtenerDatosUsuario($correo){
            $query = "SELECT UsuarioID, Password, Estado FROM usuarios WHERE Usuario = '$correo'";
            $datos = parent::obtenerDatos($query);
            if(isset($datos[0]['UsuarioID'])){
                return $datos;
            }else{
                return 0;
            }
        }

        private function insertarToken($usuarioid){
            $val = true;
            $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
            $date = date('Y-m-d H:i');
            $estado = 'Activo';
            $query = "INSERT INTO usuarios_token (UsuarioId, Token, Estado, Fecha)VALUES('$usuarioid','$token', '$estado', '$date')";
            $verifica = parent::nonQuery($query);
            if($verifica){
                return $token;
            }else{
                return false;
            }
        }
    }

?>