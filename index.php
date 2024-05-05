<?php
require_once 'functions.php';
require_once 'jwt.php';
$HEADERS = getallheaders();

$token = $HEADERS['Authorization'];

// Manejar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleRequest('POST');
} 
// Manejar solicitudes GET
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleRequest('GET');
} 
// Manejar solicitudes DELETE
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    handleRequest('DELETE');
} 
// Manejar solicitudes PUT
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    handleRequest('PUT');
}

// Función para manejar todas las solicitudes
function handleRequest($method) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'POST':
            if (isset($_GET['action']) && $_GET['action'] === 'login') {
                handleLogin($input);
            } elseif (isset($input['nombre']) && isset($input['correo']) && isset($input['pass'])) {
                createUserRequest($input);
            } else {
                sendBadRequestResponse("Se requieren datos válidos para la solicitud POST");
            }
            break;
            
        case 'GET':
            if (isset($_GET['id'])) {
                getUserByIdRequest($_GET['id']);
            } else {
                getUsersWithPaginationRequest();
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                deleteUserRequest($_GET['id']);
            } else {
                sendBadRequestResponse("Se requiere un ID de usuario para la solicitud DELETE");
            }
            break;
            
        case 'PUT':
            if (isset($input['id'])) {
                updateUserRequest($input);
            } else {
                sendBadRequestResponse("Se requiere un ID de usuario para la solicitud PUT");
            }
            break;
            
        default:
            sendBadRequestResponse("Método de solicitud no válido");
            break;
    }
}

// Función para manejar solicitud de inicio de sesión
function handleLogin($input) {
    if (isset($input['correo']) && isset($input['pass'])) {
        $response = login($input['correo'], $input['pass']);
        $response->token = $this->SignIn([
            "pass"    => $input['pass'],
            "correo"   => $input['correo']
        ]);;
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
