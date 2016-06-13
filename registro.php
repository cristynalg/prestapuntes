<?php
  session_start();
  //Incluye el archivo de conexión a la base de datos.
  require_once "conexionbdd.php";

  //Esta funcion valida cada campo del formulario de registro según las directrices deseables.
  function validar_campos ($nombre, $apellidos, $email, $usuario, $password1, $password2) {
    //Creamos un array que contendrá todos los errores para posteriormente mostrarlos al usuario.
    $errores = array();
    if ((strlen($nombre) < 2) or (strlen($nombre) > 15) or (!preg_match("/^[a-zA-Z ]*$/", $nombre))) {
      //Creamos un array que contendrá el campo y el error referidos al campo nombre.
      $errorNombre = array();
      $errorNombre['campo'] = "nombre";
      $errorNombre['mensaje'] = "Error. El nombre escrito debe tener de 2 a 15 letras.";
      //Al array $errores añadimos el array del errorNombre.
      array_push($errores, $errorNombre);
    }  
    if ((strlen($apellidos) < 2) or (strlen($apellidos) > 40) or (!preg_match("/^[a-zA-Z ]*$/", $apellidos))) {
      //Creamos un array que contendrá el campo y el error referidos al campo apellidos.
      $errorApellidos = array();
      $errorApellidos['campo'] = "apellidos";
      $errorApellidos['mensaje'] = "Error. Los apellidos escritos deben tener de 2 a 40 letras.";
      //Al array $errores añadimos el array del errorApellidos.
      array_push($errores, $errorApellidos);
    }  
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      //Creamos un array que contendrá el campo y el error referidos al campo email.
      $errorEmail = array();
      $errorEmail['campo'] = "email";
      $errorEmail['mensaje'] = "Error. El email escrito no es válido.";
      //Al array $errores añadimos el array del errorEmail.
      array_push($errores, $errorEmail);
    }
    if ((strlen($usuario) < 2) or (strlen($usuario) > 12)) {
      //Creamos un array que contendrá el campo y el error referidos al campo usuario.
      $errorUsuario = array();
      $errorUsuario['campo'] = "usuarioRe";
      $errorUsuario['mensaje'] = "Error. El usuario escrito debe tener de 2 a 12 caracteres.";
      //Al array $errores añadimos el array del errorUsuario.
      array_push($errores, $errorUsuario);
    }  
    if(strlen($password1) < 4 or strlen($password1) > 12) {
      //Creamos un array que contendrá el campo y el error referidos al campo password1.
      $errorPassword = array();
      $errorPassword['campo'] = "contrasena1";
      $errorPassword['mensaje'] = "Error. La contraseña escrita debe tener de 4 a 12 caracteres.";
      //Al array $errores añadimos el array del errorPassword.
      array_push($errores, $errorPassword);
    } 
    if (strcmp($password1, $password2) != 0) {
      //Creamos un array que contendrá el campo y el error referidos al campo password2.
      $errorPassword2 = array();
      $errorPassword2['campo'] = "contrasena2";
      $errorPassword2['mensaje'] = "Error. La contraseña no coincide.";
      //Al array $errores añadimos el array del errorPassword2.
      array_push($errores, $errorPassword2);
    }
    return $errores;
  }

  //Esta funcion comprueba si existe el usuario escogido en la base de datos.
  function valida_existe_usuario($conexion, $usuario) {
    //Creamos un array que contendrá todos los errores para posteriormente mostrarlos al usuario.
    $errores = array();
    $select = "SELECT Usuario FROM usuario WHERE Usuario like ?";
    //Preparamos consulta sql.
    $statement = mysqli_prepare($conexion, $select);
    //Agregamos variables a la consulta sql, pasados como parametros.
    mysqli_stmt_bind_param($statement,'s', $usuario);
    //Ejecutamos la sentencia.
    mysqli_stmt_execute($statement);
    //Transfiere un conjunto de resultados desde una sentencia preparada (SELECT, SHOW, DESCRIBE, EXPLAIN), y únicamente si se quiere almacenar en buffer el conjunto de resultados completo en el cliente. 
    mysqli_stmt_store_result($statement);
    //Devuelve el número de filas de un conjunto de resultados de una sentencia
    $count = mysqli_stmt_num_rows($statement);
    //Cerramos la sentencia.
    mysqli_stmt_close($statement);
    //Si la sentencia ha producido un numero de filas mayor a 0 el usuario es repetido y devolvemos el campo y mensaje de error.
    if ($count > 0) {
      //Creamos un array que contendrá el campo y el error referidos al al usuario repetido.
      $errorUsuRepetido = array();
      $errorUsuRepetido['campo'] = "usuarioRe";
      $errorUsuRepetido['mensaje'] = "Lo siento, el usuario que usted ha escogido ya existe. Pruebe con otro distinto";
      array_push($errores, $errorUsuRepetido);
    }
    return $errores;
  }
  
  //Esta funcion inserta el usuario nuevo en la base de datos si se ha validado que el mismo no existe.
  function insertar_usuario($conexion, $nombre, $apellidos, $email, $usuario, $password1, $password2) {
    //Creamos un array que contendrá todos los errores para posteriormente mostrarlos al usuario.
    $errores = array();
    $insert = "INSERT INTO usuario (Nombre, Apellidos, Email, Usuario, Contrasena) values (?, ?, ?, ?, ?)";
    //Preparamos consulta sql.
    $statement = mysqli_prepare($conexion, $insert);
    //Encriptamos la contraseña proporcionada.
    $passEnc = sha1($password1);
    //Agregamos variables a la consulta sql, pasados como parametros.
    mysqli_stmt_bind_param($statement,'sssss', $nombre, $apellidos, $email, $usuario, $passEnc);
    //Ejecutamos la sentencia.
    $resultado = mysqli_stmt_execute($statement);
    //Cerramos la sentencia.
    mysqli_stmt_close($statement);
    //Si el resultado de la sentencia no es true, creamos un array contendrá el campo y el error referidos a un error general de inserción.
    if (!$resultado) {
      $errorInsertar = array();
      $errorInsertar['campo'] = '';
      $errorInsertar['mensaje'] = "ERROR. El alta de usuario no ha sido posible. Inténtelo de nuevo";
      array_push($errores, $errorInsertar);
    }
    return $errores;
  }


  //Cuerpo principal. Devolveremos los datos obtenido en un objeto JSON.
  $jsondata = array();
  //Convertimos a minusculas y capitalizamos la primera letra de cada palabra. Además guardamos el contenido de POST en variables.
  $nombre = strtolower($_POST['nombre']);
  $nombre = ucwords($nombre);
  $apellidos = strtolower($_POST['apellidos']);
  $apellidos = ucwords($apellidos);
  $email = strtolower($_POST['email']);
  $usuario = strtolower($_POST['usuario']);

  //Guardamos el resultado de llamar a la funcion validar_campos en la variable $erroresValidacion.
  $erroresValidacion = validar_campos($nombre, $apellidos, $email, $usuario, $_POST['password1'], $_POST['password2']);
  //Si $erroresValidacion está vacio llamamos a la funcion valida_existe_usuario y guardamos el resultado en $errorUsuRepetido, en otro caso devolvemos un objeto JSON con estado y mensaje.
  if (empty($erroresValidacion)) {
    $errorUsuRepetido = valida_existe_usuario($conexion, $usuario);
    //Si $errorUsuRepetido está vacío llamamos a la función insertar_usuario y guardamos el resultado en $errorInsercion, en otro caso devolvemos un objeto JSON con estado y mensaje.
    if (empty($errorUsuRepetido)) {
      $errorInsercion = insertar_usuario($conexion, $nombre, $apellidos, $email, $usuario, $_POST['password1'], $_POST['password2']);
      //Comprobamos si $errorInsercion está vacío o no y devolvemos un objeto JSON con estado y mensaje.
      if(empty($errorInsercion)) {
        $jsondata['estado'] = true;
        $jsondata['mensaje'] = 'Alta de usuario correcta';
      } else {
        $jsondata['estado'] = false;
        $jsondata['mensaje'] = $errorInsercion;
      }
    } else {
      $jsondata['estado'] = false;
      $jsondata['errores'] = $errorUsuRepetido;
    }
  } else {
    $jsondata['estado'] = false;
    $jsondata['errores'] = $erroresValidacion;
  }
  
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($jsondata);
  exit();
?> 
