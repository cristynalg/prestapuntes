<?php
    include("cabecera.php");
    //Me aseguro de que el usuario este logueado para poder acceder a esta parte de la web.
    if (!isset($_SESSION['idUsuario'])) {
        header("Location: index.php");
    }
    require_once "conexionbdd.php";

    //Esta funcion comprueba si existe el usuario escogido en la base de datos.
    function valida_existe_usuario($conexion, $usuario, $idUsuario) {
        $select = "SELECT Usuario FROM usuario WHERE Usuario like ? and IDUsuario != ?";
        //Preparamos consulta sql.
        $statement = mysqli_prepare($conexion, $select);
        //Agregamos variables a la consulta sql, pasados como parametros.
        mysqli_stmt_bind_param($statement,'ss', $usuario, $idUsuario);
        //Ejecutamos la sentencia.
        mysqli_stmt_execute($statement);
        //Transfiere un conjunto de resultados desde una sentencia preparada (SELECT, SHOW, DESCRIBE, EXPLAIN), y únicamente si se quiere almacenar en buffer el conjunto de resultados completo en el cliente. 
        mysqli_stmt_store_result($statement);
        //Devuelve el número de filas de un conjunto de resultados de una sentencia
        $count = mysqli_stmt_num_rows($statement);
        //Cerramos la sentencia.
        mysqli_stmt_close($statement);
        //Si la sentencia ha producido un numero de filas mayor a 0 el usuario es repetido devolvemos false
        if ($count > 0) {
          return false;
        }
        return true;
    }
    
    //Esta funcion realiza una actualizacion de los datos en la base de datos. Además guardamos en sesión los datos más importantes del usuario.      
    function modificar_usuario($conexion, $nombre, $apellidos, $email, $usuario, $contrasena1, $contrasena2, $idUsuario) {
        $update = "UPDATE usuario SET Nombre = ?, Apellidos = ?, Email = ?, Usuario = ?, Contrasena = ? WHERE IDUsuario = ?";
        //Preparamos consulta sql.
        $statement = mysqli_prepare($conexion, $update);
        //Encriptamos la contraseña proporcionada.
        $passEnc = sha1($contrasena1);
        //Agregamos variables a la consulta sql, pasados como parametros.
        mysqli_stmt_bind_param($statement,'ssssss', $nombre, $apellidos, $email, $usuario, $passEnc, $idUsuario);
        //Ejecutamos la sentencia.
        $resultado = mysqli_stmt_execute($statement);
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellidos'] = $apellidos;
        $_SESSION['email'] = $email;
        $_SESSION['usuario'] = $usuario;
        //Cerramos la sentencia.
        mysqli_stmt_close($statement);
        if (!$resultado) {
          return false;
        }
        return true;
    } 

    //Cuerpo principal. Creamos diversas variables para guardar distintos contenidos que necesitaremos y para mostrar información en el HTML.
    $nombre = $_SESSION['nombre'];
    $apellidos = $_SESSION['apellidos'];
    $email = $_SESSION['email'];
    $usuario = $_SESSION['usuario'];
    $contrasena1 = "";
    $contrasena2 = "";
    $nombreError ="";
    $claseNombreError ="";
    $apellidosError ="";
    $claseApellidosError ="";
    $emailError ="";
    $claseEmailError ="";
    $usuarioError ="";
    $claseUsuarioError ="";
    $contrasena1Error ="";
    $claseContrasena1Error ="";
    $contrasena2Error ="";
    $claseContrasena2Error ="";
    $errorEnCampos = false;
    $mostrarModal = false;

    //Si el formulario de cambio de datos ha sido enviado mediante POST, validamos campos del formulario.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        //Si "nombre" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["nombre"])) {
            $nombreError = "El nombre es requerido";
            $claseNombreError ="has-error";
            $errorEnCampos = true;
        } else {
            $nombre = strtolower($_POST["nombre"]);
            $nombre = ucwords($nombre);
            if ((strlen($nombre) < 2) or (strlen($nombre) > 15) or (!preg_match("/^[a-zA-Z ]*$/", $nombre))) {
                $nombreError = "Error. El nombre escrito debe tener de 2 a 15 letras";
                $claseNombreError ="has-error";
                $errorEnCampos = true;
            }  
        }
        //Si "apellidos" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["apellidos"])) {
            $apellidosError = "Los apellidos son requeridos";
            $claseApellidosError ="has-error";
            $errorEnCampos = true;
        } else {
            $apellidos = strtolower($_POST["apellidos"]);
            $apellidos = ucwords($apellidos);
            if ((strlen($apellidos) < 2) or (strlen($apellidos) > 40) or (!preg_match("/^[a-zA-Z ]*$/", $apellidos))) {
                $apellidosError = "Error. Los apellidos escritos deben tener de 2 a 40 letras";
                $claseApellidosError ="has-error";
                $errorEnCampos = true;
            }  
        }
        //Si "email" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["email"])) {
            $emailError = "El email es requerido";
            $claseEmailError ="has-error";
            $errorEnCampos = true;
        } else {
            $email = strtolower($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailError = "Error. El email escrito no es válido";
                $claseEmailError ="has-error";
                $errorEnCampos = true;
            }
        }
        //Si "usuario" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["usuario"])) {
            $usuarioError = "El usuario es requerido";
            $claseUsuarioError ="has-error";
            $errorEnCampos = true;
        } else {
            $usuario = strtolower($_POST["usuario"]);
            if ((strlen($usuario) < 2) or (strlen($usuario) > 12)) {
                $usuarioError = "Error. El usuario escrito debe tener de 2 a 12 caracteres";
                $claseUsuarioError ="has-error";
                $errorEnCampos = true;
            }  
        }
        //Si "contrasena1" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["contrasena1"])) {
            $contrasena1Error = "La contraseña es requerida";
            $claseContrasena1Error ="has-error";
            $errorEnCampos = true;
        } else {
            $contrasena1 = $_POST["contrasena1"];
            if(strlen($contrasena1) < 4 or strlen($contrasena1) > 12) {
                $contrasena1Error = "Error. La contraseña escrita debe tener de 4 a 12 caracteres";
                $claseContrasena1Error ="has-error";
                $errorEnCampos = true;
            } 
        }
        //Si "contrasena2" no es vacio y cumple con las directrices devolvemos $errorEnCampos = true y en otro caso false.
        if (empty($_POST["contrasena2"])) {
            $contrasena2Error = "La repetición de la contraseña es requerida";
            $claseContrasena2Error ="has-error";
            $errorEnCampos = true;
        } else {
            $contrasena2 = $_POST["contrasena2"];
            if(strcmp($contrasena1, $contrasena2) != 0) {
                $contrasena2Error = "Error. La contraseña no coincide";
                 $claseContrasena2Error ="has-error";
                 $errorEnCampos = true;
            } 
        }
        //Si no ha habido errores en los campos de validación del formulario, comprobaremos si el nick nuevo de usuario que se quiere modificar existe en la base de datos y si no existe, modificamos los datos generales del usuario en cuestión.
        if (!$errorEnCampos) {
            if (valida_existe_usuario($conexion, $usuario, $idUsuario)) {
                if (modificar_usuario($conexion, $nombre, $apellidos, $email, $usuario, $contrasena1, $contrasena2, $idUsuario)) {
                    //Modificados los datos, guardamos true en una variable que despues usaremos para mostrar una modal con un mensaje afirmativo.
                    $mostrarModal = true;
                }
            } else {
                $usuarioError = "Lo siento, el usuario que usted ha escogido ya existe. Pruebe con otro distinto";
                $claseUsuarioError ="has-error";
            }
        }
    }    
    if ($mostrarModal) {  //Si $mostrarModal es true, mostramos una modal con un mensaje afirmativo   ?>
        <script>
            $(document).ready(function() {
                $('#modalCambiosUsuOK').modal('show');
            });
        </script>
    <?php }

?>

    <!--CONTENEDOR DEL CUERPO DE LA PÁGINA-->
    <div id="cuerpo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 titulosPaginas">
            <h2 class="text-primary"><strong>MI CUENTA</strong></h2>
        </div>
        <div id="miCuenta" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="apartadoInfo">
                <h5 class="text-info">En este apartado podrás cambiar tus datos básicos de identificación</h5>
            </div>
            <!--FORMULARIO DE MODIFICACIÓN DE DATOS DEL USUARIO-->   
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" name="cambiosUsu" id="cambiosUsu">
                <div class="form-group">
                    <label for="nombre" class="control-label estiloCambiosUsu <?= $claseNombreError ?>">Nombre:
                        <input type="text" class="form-control" name ="nombre" id="nombre" value="<?= $nombre ?>" minlength="2" maxlength="15" size="50">
                        <label class="error"><?= $nombreError ?></label>
                    </label>
                   <label for="apellidos" class="control-label estiloCambiosUsu <?= $claseApellidosError ?>">Apellidos:
                       <input type="text" class="form-control" name="apellidos" id="apellidos" value="<?= $apellidos ?>" minlength="2" maxlength="40" size="50">
                       <label class="error"><?= $apellidosError ?></label>
                   </label>
                </div>
                <div class="form-group">
                    <label for="email" class="control-label estiloCambiosUsu <?= $claseEmailError ?>">Email:
                        <input type="email" class="form-control" name="email" id="email" value="<?= $email ?>" minlength="5" maxlength="20" size="50">
                        <label class="error"><?= $emailError ?></label>
                   </label>   
                   <label for="usuario" class="control-label estiloCambiosUsu <?= $claseUsuarioError ?>">Usuario:
                       <input type="text" class="form-control" name ="usuario" id="usuario" value="<?= $usuario ?>" minlength="2" maxlength="12" size="50">
                       <label class="error"><?= $usuarioError ?></label>
                   </label>
                </div>
                <div class="form-group">
                   <label for="contrasena1" class="control-label estiloCambiosUsu <?= $claseContrasena1Error ?>">Nueva contraseña:
                       <input type="password" class="form-control has-error" name="contrasena1" id="contrasena1" value="<?= $contrasena1 ?>" minlength="4" maxlength="12" size="50">
                       <label class="error"><?= $contrasena1Error ?></label>
                   </label>
                   <label for="contrasena2" class="control-label estiloCambiosUsu <?= $claseContrasena2Error ?>">Repite contraseña:
                       <input type="password" class="form-control" name="contrasena2" id="contrasena2" value="<?= $contrasena2 ?>" minlength="4" maxlength="12" size="50">
                       <label class="error"><?= $contrasena2Error ?></label>
                   </label>
                </div>
                <div class="form group">
                    <input type="submit" class="btn btn-primary" name="enviarCambiosUsu" id="enviarCambiosUsu" value="Enviar datos">
                </div>
            </form>
        </div>

        <!--MODAL DE CAMBIO DE DATOS DEL USUARIO OK-->
            <div class="modal fade" id="modalCambiosUsuOK" tabindex="-1" role="dialog" aria-labelledby="modalCambiosUsuOK">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title" id="modalCambiosUsuOK">Modificación datos del usuario</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success" role="alert">Formulario de modificación completado satisfactoriamente. Recuerde sus nuevos datos a la hora de iniciar sesión en la página</div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarCambiosUsuOK">Cerrar</button>
                    </div>
                  </div>
                </div>
            </div>
    </div>
<?php include("pie.php"); ?>