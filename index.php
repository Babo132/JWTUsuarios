<?php
require_once 'functions.php';

// Manejar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest();
} 
// Manejar solicitudes GET
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest();
} 
// Manejar solicitudes DELETE
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    handleDeleteRequest();
} 
// Manejar solicitudes PUT
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    handlePutRequest();
}

// Función para manejar solicitudes POST
function handlePostRequest() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action']) && $_GET['action'] === 'login') {
        handleLogin($input);
    } elseif (isset($input['nombre']) && isset($input['correo']) && isset($input['pass'])) {
        createUserRequest($input);
    } else {
        sendBadRequestResponse("Se requieren datos válidos para la solicitud POST");
    }
}

// Función para manejar solicitudes GET
function handleGetRequest() {
    if (isset($_GET['id'])) {
        getUserByIdRequest($_GET['id']);
    } else {
        getUsersWithPaginationRequest();
    }
}

// Función para manejar solicitudes DELETE
function handleDeleteRequest() {
    if (isset($_GET['id'])) {
        deleteUserRequest($_GET['id']);
    } else {
        sendBadRequestResponse("Se requiere un ID de usuario para la solicitud DELETE");
    }
}

// Función para manejar solicitudes PUT
function handlePutRequest() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['id'])) {
        updateUserRequest($input);
    } else {
        sendBadRequestResponse("Se requiere un ID de usuario para la solicitud PUT");
    }
}

// Función para manejar solicitud de inicio de sesión
function handleLogin($input) {
    if (isset($input['correo']) && isset($input['pass'])) {
        $response = login($input['correo'], $input['pass']);
        sendResponse($response);
    } else {
        sendBadRequestResponse("Se requieren correo y contraseña para iniciar sesión");
    }
}

// Función para manejar solicitud de creación de usuario
function createUserRequest($input) {
    $response = createUser($input['nombre'], $input['correo'], $input['pass']);
    sendResponse($response);
}

// Función para manejar solicitud de obtención de usuarios con paginación
function getUsersWithPaginationRequest() {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $users = getUsersWithPagination($limit, $offset);
    sendResponse($users);
}

// Función para manejar solicitud de obtención de usuario por ID
function getUserByIdRequest($id) {
    $user = getUserById($id);
    if ($user) {
        sendResponse($user);
    } else {
        sendNotFoundResponse("Usuario no encontrado");
    }
}

// Función para manejar solicitud de eliminación de usuario por ID
function deleteUserRequest($id) {
    $response = deleteUser($id);
    sendResponse($response);
}

// Función para manejar solicitud de actualización de usuario por ID
function updateUserRequest($input) {
    $response = updateUser($input['id'], $input['nombre'], $input['correo'], $input['pass']);
    sendResponse($response);
}

// Función para enviar una respuesta JSON
function sendResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Función para enviar una respuesta de error 400 (Bad Request)
function sendBadRequestResponse($message) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array("message" => $message));
}

// Función para enviar una respuesta de error 404 (Not Found)
function sendNotFoundResponse($message) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(array("message" => $message));
}
?>
