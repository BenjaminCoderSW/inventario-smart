<?php
	// Incluimos el script con las funciones importantes
    require_once "main.php";

	/*== Almacenando id ==*/
    // Almacenamos el id mandado desde el formulario del archivo product_update.php en el input con name producto_id
    $id=limpiar_cadena($_POST['producto_id']);


    /*== Verificando producto ==*/
    // Abrimos una conexion a la base de datos
	$check_producto=conexion();
    // Ahora almacenamos el resultado de la consulta select a la base de datos, para poder verificar
    // si el producto realmente existe en la base de datos mediante su id
	$check_producto=$check_producto->query("SELECT * FROM producto WHERE producto_id='$id'");

    // Verificamos si el producto realmente existe en la base de datos mediante su id, al ver si la consulta trajo algun registro
    // Si la consulta SELECT, NO selecciono un producto entonces
    if($check_producto->rowCount()<=0){
        // Significa que el producto NO existe en nuestra base de datos
    	echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El producto no existe en el sistema
            </div>
        ';
        exit();
    }else{
        // Si la consulta SELECT, SI selecciono un producto entonces
        // Convertimos ese registro a un array llamada $datos, en esta variable tendremos los datos del producto que se quiere actualizar
    	$datos=$check_producto->fetch();
    }
    // Cerramos la conexion a la base de datos
    $check_producto=null;


    /*== Almacenando datos ==*/
    $codigo=limpiar_cadena($_POST['producto_codigo']);
	$nombre=limpiar_cadena($_POST['producto_nombre']);

	$precio=limpiar_cadena($_POST['producto_precio']);
	$stock=limpiar_cadena($_POST['producto_stock']);
	$categoria=limpiar_cadena($_POST['producto_categoria']);


	/*== Verificando campos obligatorios ==*/
    if($codigo=="" || $nombre=="" || $precio=="" || $stock=="" || $categoria==""){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No has llenado todos los campos que son obligatorios
            </div>
        ';
        exit();
    }


    /*== Verificando integridad de los datos ==*/
    if(verificar_datos("[a-zA-Z0-9- ]{1,70}",$codigo)){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El CODIGO de BARRAS no coincide con el formato solicitado
            </div>
        ';
        exit();
    }

    if(verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}",$nombre)){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El NOMBRE no coincide con el formato solicitado
            </div>
        ';
        exit();
    }

    if(verificar_datos("[0-9.]{1,25}",$precio)){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El PRECIO no coincide con el formato solicitado
            </div>
        ';
        exit();
    }

    if(verificar_datos("[0-9]{1,25}",$stock)){
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El STOCK no coincide con el formato solicitado
            </div>
        ';
        exit();
    }


    /*== Verificando codigo ==*/
    if($codigo!=$datos['producto_codigo']){
	    $check_codigo=conexion();
	    $check_codigo=$check_codigo->query("SELECT producto_codigo FROM producto WHERE producto_codigo='$codigo'");
	    if($check_codigo->rowCount()>0){
	        echo '
	            <div class="notification is-danger is-light">
	                <strong>¡Ocurrio un error inesperado!</strong><br>
	                El CODIGO de BARRAS ingresado ya se encuentra registrado, por favor elija otro
	            </div>
	        ';
	        exit();
	    }
	    $check_codigo=null;
    }


    /*== Verificando nombre ==*/
    if($nombre!=$datos['producto_nombre']){
	    $check_nombre=conexion();
	    $check_nombre=$check_nombre->query("SELECT producto_nombre FROM producto WHERE producto_nombre='$nombre'");
	    if($check_nombre->rowCount()>0){
	        echo '
	            <div class="notification is-danger is-light">
	                <strong>¡Ocurrio un error inesperado!</strong><br>
	                El NOMBRE ingresado ya se encuentra registrado, por favor elija otro
	            </div>
	        ';
	        exit();
	    }
	    $check_nombre=null;
    }


    /*== Verificando categoria ==*/
    if($categoria!=$datos['categoria_id']){
	    $check_categoria=conexion();
	    $check_categoria=$check_categoria->query("SELECT categoria_id FROM categoria WHERE categoria_id='$categoria'");
	    if($check_categoria->rowCount()<=0){
	        echo '
	            <div class="notification is-danger is-light">
	                <strong>¡Ocurrio un error inesperado!</strong><br>
	                La categoría seleccionada no existe
	            </div>
	        ';
	        exit();
	    }
	    $check_categoria=null;
    }


    /*== Actualizando datos ==*/
    // Abrimos conexion a la base de datos
    $actualizar_producto=conexion();
    // Preparamos una consulta UPDATE para actualizar los campos de ese producto que queremos actualizar con los nuevos valores
    // que tenemos almacenados en el array con sus marcadores correspondientes
    $actualizar_producto=$actualizar_producto->prepare("UPDATE producto SET producto_codigo=:codigo,producto_nombre=:nombre,producto_precio=:precio,producto_stock=:stock,categoria_id=:categoria WHERE producto_id=:id");

    // Array de los nuevos valores con los que se actualizara el producto
    $marcadores=[
        ":codigo"=>$codigo,
        ":nombre"=>$nombre,
        ":precio"=>$precio,
        ":stock"=>$stock,
        ":categoria"=>$categoria,
        ":id"=>$id
    ];

    // Si la consulta UPDATE se ejecuta correctamente mandandole los valores correctos entonces:
    if($actualizar_producto->execute($marcadores)){
        // Diremos que el producto se actualizo con exito
        echo '
            <div class="notification is-info is-light">
                <strong>¡PRODUCTO ACTUALIZADO!</strong><br>
                El producto se actualizo con exito
            </div>
        ';
    }else{
        // En caso de que el producto no se haya actualizado correctamente entonces:
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No se pudo actualizar el producto, por favor intente nuevamente
            </div>
        ';
    }
    // Cerramos la conexion a la base de datos que estabamos ocupando para actualizar el producto
    $actualizar_producto=null;