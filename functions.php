<?php
// functions.php

require_once 'config.php';

// Función para crear un nuevo usuario
function createUser($nombre, $correo, $pass) {
    global $conn;
    $sql = "INSERT INTO usuarios (nombre, correo, pass) VALUES ('$nombre', '$correo', '$pass')";
    if ($conn->query($sql) === TRUE) {
        return "Usuario creado con éxito";
    } else {
        return "Error al crear el usuario: " . $conn->error;
    }
}

// Función para obtener todos los usuarios con paginación
function getUsersWithPagination($limit, $offset) {
    global $conn;
    $sql = "SELECT * FROM usuarios LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    $users = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

// Función para obtener un usuario por ID
function getUserById($userId) {
    global $conn;
    $sql = "SELECT * FROM usuarios WHERE idusuarios = '$userId' LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows == 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Función para eliminar un usuario por ID
function deleteUser($userId) {
    global $conn;
    $sql = "DELETE FROM usuarios WHERE idusuarios = '$userId'";
    if ($conn->query($sql) === TRUE) {
        return "Usuario eliminado correctamente";
    } else {
        return "Error al eliminar usuario: " . $conn->error;
    }
}

// Función para actualizar un usuario por ID
function updateUser($userId, $nombre, $correo, $pass) {
    global $conn;
    $sql = "UPDATE usuarios SET nombre='$nombre', correo='$correo', pass='$pass' WHERE idusuarios='$userId'";
    if ($conn->query($sql) === TRUE) {
        return "Usuario actualizado correctamente";
    } else {
        return "Error al actualizar usuario: " . $conn->error;
    }
}

// Función para iniciar sesión (login)
function login($userCorreo, $userPass) {
    global $conn;
    
    // Verificar si las credenciales son válidas
    $sql = "SELECT idusuarios FROM usuarios WHERE correo = '$userCorreo' AND pass = '$userPass'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows == 1) {
        // Si las credenciales son correctas, obtener el ID del usuario
        $row = $result->fetch_assoc();
        $userId = $row['idusuarios'];
        
        // Devolver un mensaje de "logueado" junto con el ID del usuario
        return array("message" => "logueado", "id" => $userId);
    } else {
        // Si las credenciales son incorrectas, devolver un mensaje de error
        return array("message" => "Credenciales incorrectas");
    }
}
?>
