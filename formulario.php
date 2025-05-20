<?php
// Conexión con la base de datos
$db = new mysqli("localhost", "root", "", "matt"); 

// Consultas preparadas
$queryInsertUser = "INSERT INTO usuarios (nombre, cargo) VALUES (?,?)";
$queryInsertTask = "INSERT INTO tareas (estadoTarea, fechaInicio, fechaFin, descripcion) VALUES (?,?,?,?)";
$queryInsertType = "INSERT INTO tipos_tarea (tipo) VALUES (?)";
$queryLinkParent = "INSERT INTO tareas_padre (idTarea, idTareaPadre) VALUES (?,?)";

// Comprueba el último ID y genera uno nuevo
$lastIdQuery = "SELECT MAX(id) FROM tareas";
$res = $db->query($lastIdQuery);
$maxRow = $res->fetch_array();
$currentTaskId = ($maxRow[0] ?? 0) + 1;

$taskTypesText = '';

// Comprobación de datos enviados por post o get
$data = $_POST ?: $_GET;

if (!empty($data['nombre'])) {
    $userName = $data['nombre'];
    $userRole = $data['cargo'];

    $taskState = $data['estadoTarea'];
    $startDate = $data['fechaInicio'];
    $endDate = $data['fechaFin'];
    $taskDesc = $data['descripcion'];

    $selectedTypes = array();
    if (!empty($data['codigo'])) $selectedTypes[] = "Código";
    if (!empty($data['grafismo'])) $selectedTypes[] = "Grafismo";
    if (!empty($data['test'])) $selectedTypes[] = "Test";
    if (!empty($data['bug'])) $selectedTypes[] = "Bug";
    if (!empty($data['documentacion'])) $selectedTypes[] = "Documentación";

    $taskTypesText = implode(", ", $selectedTypes);
    $parentTaskId = $data['tareaPadre'] ?? null;

    try {
        // Insertar usuario
        $stmt = $db->prepare($queryInsertUser);
        $stmt->bind_param("ss", $userName, $userRole);
        $stmt->execute();

        // Insertar tarea
        $stmt = $db->prepare($queryInsertTask);
        $stmt->bind_param("isss", $taskState, $startDate, $endDate, $taskDesc);
        $stmt->execute();

        // Insertar tipo de tarea, si hay alguno
        if (!empty($taskTypesText)) {
            $stmt = $db->prepare($queryInsertType);
            $stmt->bind_param("s", $taskTypesText);
            $stmt->execute();
        }

        // Insertar relación con tarea padre, si hay alguna
        if (!empty($parentTaskId)) {
            $stmt = $db->prepare($queryLinkParent);
            $stmt->bind_param("ii", $currentTaskId, $parentTaskId);
            $stmt->execute();
        }

        echo "<h2>Datos registrados.</h2>";
    } catch (Exception $err) {
        echo "<h2>Error al guardar la información: " . $err->getMessage() . "</h2>";
    }

    $db->close();
} else {
    echo "<h2>Datos no válidos.</h2>";
}
?>