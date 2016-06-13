<?php
    //Iniciar una nueva sesión o reanudar la existente basada en un identificador de sesión pasado mediante una petición GET o POST, o pasado mediante una cookie.
    session_start();
    //Por defecto el menu de usuario logueado no se muestra, se muestran los botones de iniciar sesión o registrarse.
    $esVisibleMenuUsu = 'hidden';
    $esVisibleBotonesSesion = 'show';
    $nombreUsuario = '';
    //Si tenemos guardada en sesión la id del usuario, se mostrará el menú de usuario logueado.
    if(isset($_SESSION['idUsuario'])) {
        $esVisibleMenuUsu = 'show';
        $esVisibleBotonesSesion = 'hidden';
        $nombreUsuario = $_SESSION['usuario'];
        $idUsuario = $_SESSION['idUsuario'];
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Prestapuntes</title>
        <!--CSS de Bootstrap y mis estilos customizados-->
        <link href="css/bootstrap.css" rel="stylesheet" media="screen">
        <link href="css/custom.css" rel="stylesheet" media="screen">
        <!--Librerias Javascript-->
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/modernizr-custom.js"></script>
        <!--Código jQuery-->
        <script type="text/javascript">
            $(document).ready(function() {
                //Modal de iniciar sesión con Ajax.
                $('#btnLogin').click(function(e) {
                    var usu = $("#usuario").val().toLowerCase();
                    var pwd = $("#contrasena").val();
                    var params = { "usuario" : usu, "password" : pwd };
                    $.post("login.php", params, null, "json")
                        .done(function(data) {
                            if (data.estado) {
                                $("#menuUsuario").removeClass("hidden").addClass("show");
                                $("#btnUsuario span").before( usu + " ");
                                $("#botonesSesion").removeClass("show").addClass("hidden");
                                $('#modalIniciarSesion').modal('hide');
                            } else {
                                $("#mensajeLogin").html(data.mensaje);
                                $("#mensajeLogin").removeClass("hidden").addClass("show");
                            }
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            $("#mensajeLogin").html("Error de conexion con el servidor.<br/>"+textStatus+"<br/>"+errorThrown);
                            $("#mensajeLogin").removeClass("hidden").addClass("show");
                        });
                });
                //Modal de registro con Ajax.
                $('#enviarRegistro').click(function(e) {
                    $('.has-error').removeClass("has-error");
                    var nom = $("#nombre").val();
                    var ape = $("#apellidos").val();
                    var mail = $("#email").val();
                    var usu = $("#usuarioRe").val();
                    var pwd1 = $("#contrasena1").val();
                    var pwd2 = $("#contrasena2").val();
                    var params = { "nombre" : nom, "apellidos" : ape, "email" : mail, "usuario" : usu, "password1" : pwd1, "password2" :pwd2 };
                    $.post("registro.php", params, null, "json")
                        .done(function(data) {
                            if (data.estado) {
                                $('#modalRegistro').modal('hide');
                                $('#modalRegistroOK').modal('show');
                            } else {
                                var errores = data.errores;
                                var mensajes = ''; 
                                $.each(errores, function( index, error ) {
                                  mensajes += error.mensaje + "<br />";
                                  $('#' + error.campo).closest("div").addClass("has-error");
                                });

                                $("#mensajeRegistro").html(mensajes);
                                $("#mensajeRegistro").removeClass("hidden").addClass("show");
                            }
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            $("#mensajeRegistro").html("Error de conexion con el servidor.<br/>"+textStatus+"<br/>"+errorThrown);
                            $("#mensajeRegistro").removeClass("hidden").addClass("show");
                        }); 
                });
                //Limpiamos campos de iniciar sesion (modal)
                $('#modalIniciarSesion').on('hidden.bs.modal', function (e) {
                  $("#usuario").val('');
                  $("#contrasena").val('');
                  $("#mensajeLogin").removeClass("show").addClass("hidden");
                });
                 //Limpiamos campos de registro (modal)
                $('#modalRegistro').on('hidden.bs.modal', function (e) {
                  $("#nombre").val('');
                  $("#apellidos").val('');
                  $("#email").val('');
                  $("#usuarioRe").val('');
                  $("#contrasena1").val('');
                  $("#contrasena2").val('');
                  $("#mensajeRegistro").removeClass("show").addClass("hidden");
                  $('.has-error').removeClass("has-error");
                });
            });
                
        </script>
    
    </head>
    <body>
        <!--CONTENEDOR DE TODA LA PÁGINA-->
        <div id="contenedor" class="container panel panel-default">

            <!--CABECERA PÁGINA-->
            <header id="cabecera">
                
                <!--BOTONES SUPERIORES USUARIO YA LOGUEADO-->
                <div id="navegacionUsuario" class="navbar-right">
                      <div id="menuUsuario" class="navbar <?= $esVisibleMenuUsu ?>">
                          <ul class="nav navbar-nav navbar-right">
                              <li class="dropdown">   
                                  <button id="btnUsuario" type="button" class="btn btn-primary dropdown-toggle navbar-btn"
                                          data-toggle="dropdown"><?= $nombreUsuario ?>
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu">
                                    <li><a href="micuenta.php">Mi cuenta</a></li>
                                    <li><a href="subir.php">Subir archivos</a></li>
                                    <li><a href="mostrarsubidos.php">Archivos subidos</a></li>
                                    <li><a href="cerrarsesion.php">Cerrar sesión</a></li>
                                  </ul>
                              </li>
                          </ul>
                      </div>

                    <!--BOTONES SUPERIORES USUARIO NO LOGUEADO-->
                    <div id="botonesSesion" class="<?= $esVisibleBotonesSesion ?>">
                        <ul class="nav nav-pills">
                          <li data-toggle="modal" data-target="#modalIniciarSesion"><a href="#">Iniciar sesión</a></li>
                          <li data-toggle="modal" data-target="#modalRegistro"><a href="#">Registrarse</a></li>
                        </ul> 
                    </div>
                </div>
                 
                <!--MODAL DE LOGIN-->
                <div class="modal fade" id="modalIniciarSesion" tabindex="-1" role="dialog" aria-labelledby="modalIniciarSesion">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                          <h4 class="modal-title" id="modalIniciarSesion">Inicio de sesión</h4>
                        </div>
                        <div class="modal-body">
                            <div id="mensajeLogin" class="alert alert-danger hidden" role="alert"></div>
                            <div class="form-group">
                              <label for="usuario" class="control-label">Usuario:</label>
                              <input type="text" class="form-control" name ="usuario" id="usuario" minlength="4" maxlength="12"></input>
                            </div>
                            <div class="form-group">
                              <label for="contrasena" class="control-label">Contraseña:</label>
                              <input type="password" class="form-control" name="contrasena" id="contrasena" minlength="4" maxlength="12"></input>
                            </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarSes">Cerrar</button>
                          <button type="button" class="btn btn-primary" id="btnLogin">Enviar datos</button>
                        </div>
                      </div>
                    </div>
                </div>
                
                <!--MODAL FORMULARIO DE REGISTRO-->
                <div class="modal fade" id="modalRegistro" tabindex="-1" role="dialog" aria-labelledby="modalRegistro">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalRegistro">Registrarse</h4>
                      </div>
                      <div class="modal-body">
                        <form method="post" action="registro.php" name="registro" id="registro">
                          <div id="mensajeRegistro" class="alert alert-danger hidden" role="alert"></div>
                          <div class="form-group">
                            <label for="nombre" class="control-label">Nombre:</label>
                            <input type="text" class="form-control" name ="nombre" id="nombre" minlength="2" maxlength="15"></input>
                          </div>
                          <div class="form-group">
                            <label for="apellidos" class="control-label">Apellidos:</label>
                            <input type="text" class="form-control" name="apellidos" id="apellidos" minlength="2" maxlength="40"></input>
                          </div>
                          <div class="form-group">
                            <label for="email" class="control-label">Email:</label>
                            <input type="email" class="form-control" name="email" id="email" minlength="5" maxlength="40"></input>
                          </div>
                          <div class="form-group">
                            <label for="usuario" class="control-label">Usuario:</label>
                            <input type="text" class="form-control" name ="usuario" id="usuarioRe" minlength="2" maxlength="12"></input>
                          </div>
                          <div class="form-group ">
                            <label for="contrasena1" class="control-label">Contraseña:</label>
                            <input type="password" class="form-control" name="contrasena" id="contrasena1" minlength="4" maxlength="12"></input>
                          </div>
                          <div class="form-group">
                            <label for="contrasena2" class="control-label">Repite contraseña:</label>
                            <input type="password" class="form-control" name="contrasena" id="contrasena2" minlength="4" maxlength="12"></input>
                          </div>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarRe">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="enviarRegistro">Enviar datos</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!--MODAL DE REGISTRO OK-->
                <div class="modal fade" id="modalRegistroOK" tabindex="-1" role="dialog" aria-labelledby="modalRegistroOK">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                          <h4 class="modal-title" id="modalRegistroOK">Alta de usuario</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success" role="alert">Registro completado satisfactoriamente</div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrarReOK">Cerrar</button>
                        </div>
                      </div>
                    </div>
                </div>

                <!--ENCABEZADO IMAGEN CORPORATIVA-->
                <div id="encabezadoWeb" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div id="logo" class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                            <a href="index.php"><img src="img/escudo.png" class="tamanoLogo center-block" /></a>
                        </div>
                        <div id="tituloWeb" class="hidden-xs hidden-sm hidden-md col-lg-4">
                            <h1 class="text-primary"><strong>PRESTAPUNTES</strong></h1>
                        </div>
                        <div id="fondoLibros" class="hidden-xs hidden-sm hidden-md col-lg-5"></div>
                        <div id="tituloHidden" class="col-xs-12 col-sm-12 col-md-12 hidden-lg">
                            <h3 class="text-primary"><strong>PRESTAPUNTES</strong></h3>
                        </div>
                        <div id="subtitulo" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <h5 class="text-info">Tu web para compartir apuntes de informática</h5>
                        </div>              
                </div>

                <!--BARRA DE MENÚ PRINCIPAL-->
                <nav id="menuGeneral" class="navbar navbar-default col-xs-12 col-sm-12 col-md-12 col-lg-12">
                      <div class="container-fluid">
                        <div class="navbar-header">
                          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menuDesplegable" aria-expanded="false">
                            <span class="sr-only">Abrir menú</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                          </button>
                          <span class="navbar-brand">Ciclos</span>
                        </div>
                        <div class="collapse navbar-collapse" id="menuDesplegable">
                          <ul class="nav navbar-nav navbar-right">
                            <li class="hidden-lg">
                                <form class="navbar-form navbar-left" action="resultados.php" method="post" role="search">
                                    <div class="input-group">
                                      <input type="text" name="campoBuscar" class="form-control" placeholder="Buscar..." minlength="3" maxlength="60">
                                      <div class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                        <span class=" glyphicon glyphicon-search"></span>
                                        </button>
                                      </div>
                                    </div>
                                </form>
                            </li>
                          </ul>
                          <ul class="nav navbar-nav navbar-right">  
                            <li class="hidden-xs hidden-sm hidden-md">
                                <form class="navbar-form navbar-left" action="resultados.php" method="post" role="search">
                                    <div class="input-group">
                                      <input type="text" name="campoBuscar" class="form-control" placeholder="Buscar..." minlength="3" maxlength="60">
                                      <div class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                        <span class=" glyphicon glyphicon-search"></span>
                                        </button>
                                      </div>
                                    </div>
                                </form>
                            </li>
                            <li class="dropdown">   
                                <button type="button" class="btn btn-primary dropdown-toggle navbar-btn estiloBotones"
                                        data-toggle="dropdown">SMR
                                  <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                  <li><a href="resultados.php?idAsignatura=1">Montaje y mantenimiento de equipos</a></li>
                                  <li><a href="resultados.php?idAsignatura=2">Sistemas operativos monopuesto</a></li>
                                  <li><a href="resultados.php?idAsignatura=3">Aplicaciones ofimáticas</a></li>
                                  <li><a href="resultados.php?idAsignatura=4">Redes locales</a></li>
                                  <li><a href="resultados.php?idAsignatura=5">Sistemas operativos en red</a></li>
                                  <li><a href="resultados.php?idAsignatura=6">Servicios en red</a></li>
                                  <li><a href="resultados.php?idAsignatura=7">Seguridad informática</a></li>
                                  <li><a href="resultados.php?idAsignatura=8">Aplicaciones web</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">   
                                <button type="button" class="btn btn-primary dropdown-toggle navbar-btn estiloBotones"
                                        data-toggle="dropdown">ASIR
                                  <span class="caret"></span>
                                </button>
                              <ul class="dropdown-menu">
                                  <li><a href="resultados.php?idAsignatura=9">Fundamentos de hardware</a></li>
                                  <li><a href="resultados.php?idAsignatura=10">Gestión de bases de datos</a></li>
                                  <li><a href="resultados.php?idAsignatura=11">Implantación de sistemas operativos</a></li>
                                  <li><a href="resultados.php?idAsignatura=12">Lenguajes de marcas y sistemas de gestión de información</a></li>
                                  <li><a href="resultados.php?idAsignatura=13">Planificación y administración de redes</a></li>
                                  <li><a href="resultados.php?idAsignatura=14">Administración de sistemas gestores de bases de datos</a></li>
                                  <li><a href="resultados.php?idAsignatura=15">Administración de sistemas operativos</a></li>
                                  <li><a href="resultados.php?idAsignatura=16">Implantación de aplicaciones web</a></li>
                                  <li><a href="resultados.php?idAsignatura=17">Seguridad y alta disponibilidad</a></li>
                                  <li><a href="resultados.php?idAsignatura=18">Servicios de red e internet</a></li>
                               </ul>
                            </li>
                            <li class="dropdown">   
                                <button type="button" class="btn btn-primary dropdown-toggle navbar-btn estiloBotones"
                                        data-toggle="dropdown">DAW
                                  <span class="caret"></span>
                                </button>
                              <ul class="dropdown-menu">
                                  <li><a href="resultados.php?idAsignatura=19">Sistemas informáticos</a></li>
                                  <li><a href="resultados.php?idAsignatura=20">Bases de datos</a></li>
                                  <li><a href="resultados.php?idAsignatura=21">Programación</a></li>
                                  <li><a href="resultados.php?idAsignatura=22">Lenguajes de marcas y sistemas de gestión de información</a></li>
                                  <li><a href="resultados.php?idAsignatura=23">Entornos de desarrollo</a></li>
                                  <li><a href="resultados.php?idAsignatura=24">Desarrollo web en entorno cliente</a></li>
                                  <li><a href="resultados.php?idAsignatura=25">Desarrollo web en entorno servidor</a></li>
                                  <li><a href="resultados.php?idAsignatura=26">Despliegue de aplicaciones web</a></li>
                                  <li><a href="resultados.php?idAsignatura=27">Diseño de interfaces web</a></li>
                               </ul>
                            </li>
                            <li class="dropdown">   
                                <button type="button" class="btn btn-primary dropdown-toggle navbar-btn estiloBotones"
                                        data-toggle="dropdown">DAM
                                  <span class="caret"></span>
                                </button>
                              <ul class="dropdown-menu">
                                  <li><a href="resultados.php?idAsignatura=28">Sistemas informáticos</a></li>
                                  <li><a href="resultados.php?idAsignatura=29">Bases de datos</a></li>
                                  <li><a href="resultados.php?idAsignatura=30">Programación</a></li>
                                  <li><a href="resultados.php?idAsignatura=31">Lenguajes de marcas y sistemas de gestión de información</a></li>
                                  <li><a href="resultados.php?idAsignatura=32">Entornos de desarrollo</a></li>
                                  <li><a href="resultados.php?idAsignatura=33">Acceso a datos</a></li>
                                  <li><a href="resultados.php?idAsignatura=34">Desarrollo de interfaces</a></li>
                                  <li><a href="resultados.php?idAsignatura=35">Programación multimedia y dispositivos móviles</a></li>
                                  <li><a href="resultados.php?idAsignatura=36">Programación de servicios y procesos</a></li>
                                  <li><a href="resultados.php?idAsignatura=37">Sistemas de gestión empresarial</a></li>
                               </ul>
                            </li>
                            <a href="resultados.php?idAsignatura=38">
                              <button type="button" class="btn btn-primary navbar-btn estiloBotones">OTROS</button>
                            </a>
                          </ul>
                        </div>
                      </div>
                </nav>
            </header>