<?php
	session_start();
	//Incluye el archivo de conexión a la base de datos.
  require_once "conexionbdd.php";
    
  //Verificamos si el usuario y contraseña proporcionados coinciden con algun usuario registrado en la base de datos.    
  function verificar_login($conexion, $usuario, $password) {
    $loginOK = false;
    $sql = "SELECT IDUsuario, Nombre, Apellidos, Email, Usuario FROM usuario WHERE Usuario like ? and Contrasena like ?";
    //Preparamos consulta sql.
    $statement = mysqli_prepare($conexion, $sql);
    //Encriptamos la contraseña proporcionada.
    $passEnc = sha1($password);
    //Agregamos variables a la consulta sql, pasados como parametros.
    mysqli_stmt_bind_param($statement,'ss', $usuario, $passEnc);
    //Ejecutamos la sentencia.
    mysqli_stmt_execute($statement);
    //Vinculamos variables a la sentencia preparada para el almacenamiento de resultados.
    mysqli_stmt_bind_result($statement, $idUsuario, $nombre, $apellidos, $email, $usuariobdd);
    //Obtenemos los resultados de la sentencia preparada en las variables vinculadas. Guardamos el contenido de las variables en SESSION.
    while (mysqli_stmt_fetch($statement)) {
      $_SESSION['idUsuario'] = $idUsuario;
      $_SESSION['nombre'] = $nombre;
      $_SESSION['apellidos'] = $apellidos;
      $_SESSION['email'] = $email;
      $_SESSION['usuario'] = $usuariobdd;
      $loginOK = true;
    }
    //Cerramos la sentencia.
    mysqli_stmt_close($statement);
    return $loginOK;
  }

  //Cuerpo principal. Llamamos a la funcion verificar_login y devolvemos un objeto JSON con estado y mensaje.
  $jsondata = array();

  if (verificar_login($conexion, strtolower($_POST['usuario']), $_POST['password'])) {
    $jsondata['estado'] = true;
    $jsondata['mensaje'] = 'Hola! Bienvenido de nuevo.';
  } else {
    $jsondata['estado'] = false;
    $jsondata['mensaje'] = 'Lo siento, usuario y/o contraseña incorrectos.';
  }
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($jsondata, JSON_FORCE_OBJECT);
  exit();

?> 