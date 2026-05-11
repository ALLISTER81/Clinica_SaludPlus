<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require 'conexion.php';

// Verificar acceso
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Obtener lista de usuarios
$usuarios = $pdo->query("SELECT idUser, nombre, apellidos FROM users_data ORDER BY nombre")->fetchAll();

$exito = $_GET['exito'] ?? '';
$errores = [];

if (isset($_GET['error'])) {
    $errores[] = $_GET['error'];   // lo metemos como array
}



// ------------------------------------------------------
// BORRAR CITA
// ------------------------------------------------------
if (isset($_GET['borrar'])) {
    $idCita = $_GET['borrar'];

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    if ($cita) {
        $pdo->prepare("DELETE FROM citas WHERE idCita=?")->execute([$idCita]);
        header("Location: citas-administracion.php?idUser=" . $cita['idUser'] . "&exito=Cita borrada correctamente");
        exit;
    }
}

// --------------------------
// GUARDAR CAMBIOS DE EDICIÓN
// --------------------------
if (isset($_POST['guardar_cambios'])) {

    $idCita = $_POST['idCita'];
    $fecha = $_POST['fecha_cita'];
    $motivo = trim($_POST['motivo_cita']);

    // Normalizar fecha
    $fecha = date('Y-m-d', strtotime($fecha));
    $hoy = date('Y-m-d');

    // Obtener idUser de la cita
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    // VALIDACIÓN: impedir mover a fecha pasada
    if ($fecha < $hoy) {
        $mensaje = urlencode("No puedes mover la cita a una fecha pasada.");
        header("Location: citas-administracion.php?editar=$idCita&idUser=".$cita['idUser']."&error=$mensaje");
        exit;
    }

    // Actualizar cita
    $stmt = $pdo->prepare("
        UPDATE citas
        SET fecha_cita=?, motivo_cita=?
        WHERE idCita=?
    ");
    $stmt->execute([$fecha, $motivo, $idCita]);

    header("Location: citas-administracion.php?idUser=".$cita['idUser']."&exito=Cita actualizada correctamente");
    exit;
}



// ------------------------------------------------------
// OBTENER idUser SELECCIONADO (ORDEN CORRECTO)
// ------------------------------------------------------
$idUserSeleccionado = null;

// 1) Si estamos editando → PRIORIDAD
if (isset($_GET['editar'])) {

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['editar']]);
    $idUserSeleccionado = $stmt->fetchColumn();

// 2) Si estamos borrando
} elseif (isset($_GET['borrar'])) {

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['borrar']]);
    $idUserSeleccionado = $stmt->fetchColumn();

// 3) Si solo seleccionamos usuario
} elseif (isset($_GET['idUser'])) {

    $idUserSeleccionado = $_GET['idUser'];
}

// ------------------------------------------------------
// CARGAR CITA PARA EDICIÓN (DESPUÉS DE idUserSeleccionado)
// ------------------------------------------------------
$citaEditar = null;

if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita=?");
    $stmt->execute([$idEditar]);
    $citaEditar = $stmt->fetch();
}

// ------------------------------------------------------
// OBTENER CITAS DEL USUARIO
// ------------------------------------------------------
$citas = [];

if ($idUserSeleccionado) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre, u.apellidos
        FROM citas c
        JOIN users_data u ON c.idUser = u.idUser
        WHERE c.idUser = ?
        ORDER BY c.fecha_cita ASC
    ");
    $stmt->execute([$idUserSeleccionado]);
    $citas = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/Trabajo_Final_Php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Trabajo_Final_Php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Trabajo_Final_Php/favicon-16x16.png">
    <link rel="manifest" href="/Trabajo_Final_Php/site.webmanifest">
    <link rel="icon" href="/Trabajo_Final_Php/favicon.ico">
    <title>Administración de citas</title>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>   
    

    <h1>Panel de administración de citas</h1>  

    <?php if (!empty($errores)): ?>
        <div class="mensaje-error">
            <?php foreach ($errores as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito">
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>    
   

    <h2 class="seccion-titulo">Seleccionar usuario</h2>    

    <form method="GET" class="admin-form-selector">

        <label>Usuario:</label>
            <select name="idUser" required>
                <option value="">-- Selecciona un usuario --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['idUser'] ?>"
                        <?= ($idUserSeleccionado == $u['idUser']) ? 'selected' : '' ?>>
                        <?= $u['nombre'] . " " . $u['apellidos'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

        <button type="submit">Ver citas</button>
    </form>

    <hr>

    <!-- TABLA DE CITAS -->
    <?php if ($idUserSeleccionado): ?>

    <h2>Citas del usuario seleccionado</h2>

    <?php if (!$citas): ?>
        <div class="mensaje-error">
            <p>No tiene citas asignadas.</p>
        </div>
    <?php else: ?>

    
        <table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Acciones</th>
            </tr>

            <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= $c['idCita'] ?></td>
                    <td><?= date("d/m/Y", strtotime($c['fecha_cita'])) ?></td>
                    <td><?= $c['motivo_cita'] ?></td>
                    <td style="white-space: nowrap;">

                        <form method="GET" action="citas-administracion.php" style="display:inline;">
                            <input type="hidden" name="editar" value="<?= $c['idCita'] ?>">
                            <input type="hidden" name="idUser" value="<?= $c['idUser'] ?>">
                            <button type="submit" class="btn-accion btn-editar">Editar</button>
                        </form>

                        <form method="GET" action="citas-administracion.php" style="display:inline;">
                            <input type="hidden" name="borrar" value="<?= $c['idCita'] ?>">
                            <input type="hidden" name="idUser" value="<?= $c['idUser'] ?>">
                            <button type="submit" class="btn-accion btn-borrar"
                                    onclick="return confirm('¿Seguro que deseas borrar esta cita?')">Borrar</button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>

    <hr>

     <!-- FORMULARIO DE CREACIÓN  -->
    <h2>Crear nueva cita</h2>
    
    <form method="POST" action="crear-cita.php" class="admin-form">
        <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">

        <label>Fecha:</label>
        <input type="date" name="fecha_cita" required><br><br>

        <label>Especialidad (motivo):</label>
            <select name="motivo_cita" class="select-cita" required>
                <option value="">Seleccione una especialidad</option>
                <option value="Medicina General">Medicina General</option>
                <option value="Cardiología">Cardiología</option>
                <option value="Radiología Digital">Radiología Digital</option>
                <option value="Resonancia Magnética 3D">Resonancia Magnética 3D</option>
                <option value="Análisis clínicos">Análisis clínicos</option>
                <option value="Pediatría">Pediatría</option>
                <option value="Fisioterápia">Fisioterápia</option>
                <option value="Oftalmología">Oftalmología</option>
                <option value="Podología">Podología</option>              
                <option value="Odontología">Odontología</option>            
            </select>
        <br>

        <button type="submit">Crear cita</button>
    </form>

    <?php endif; ?>


    <?php if ($citaEditar): ?>

    <!-- FORMULARIO DE EDICIÓN -->
    <h2>Editar cita</h2>

    <form method="POST" action="citas-administracion.php?editar=<?= $citaEditar['idCita'] ?>" class="admin-form-edit">
        <input type="hidden" name="guardar_cambios" value="1">
        <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">

        <label>Fecha:</label>
        <input type="date" name="fecha_cita" value="<?= $citaEditar['fecha_cita'] ?>" required><br><br>

        <label>Especialidad (motivo):</label>
            <select name="motivo_cita" class="select-cita" required>
                <option value="">Seleccione una especialidad</option>

                <option value="Medicina General" 
                    <?= $citaEditar['motivo_cita'] == 'Medicina General' ? 'selected' : '' ?>>
                Medicina General
                </option>

                <option value="Cardiología" 
                    <?= $citaEditar['motivo_cita'] == 'Cardiología' ? 'selected' : '' ?>>
                Cardiología
                </option>

                <option value="Radiología Digital" 
                    <?= $citaEditar['motivo_cita'] == 'Radiología Digital' ? 'selected' : '' ?>>
                Radiología Digital
                </option>

                <option value="Resonancia Magnética 3D" 
                    <?= $citaEditar['motivo_cita'] == 'Resonancia Magnética 3D' ? 'selected' : '' ?>>
                Resonancia Magnética 3D
                </option>

                <option value="Análisis clínicos" 
                    <?= $citaEditar['motivo_cita'] == 'Análisis clínicos' ? 'selected' : '' ?>>
                Análisis clínicos
                </option>

                <option value="Pediatría" 
                    <?= $citaEditar['motivo_cita'] == 'Pediatría' ? 'selected' : '' ?>>
                Pediatría
                </option>

                <option value="Fisioterápia" 
                    <?= $citaEditar['motivo_cita'] == 'Fisioterápia' ? 'selected' : '' ?>>
                Fisioterápia
                </option>

                <option value="Oftalmología" 
                    <?= $citaEditar['motivo_cita'] == 'Oftalmología' ? 'selected' : '' ?>>
                Oftalmología
                </option>

                <option value="Podología" 
                    <?= $citaEditar['motivo_cita'] == 'Podología' ? 'selected' : '' ?>>
                Podología
                </option>

                <option value="Odontología" 
                    <?= $citaEditar['motivo_cita'] == 'Odontología' ? 'selected' : '' ?>>
                Odontología
                </option>
        </select>


        <button type="submit">Guardar cambios</button>
    </form>

    <script>
        window.onload = function() {
            document.querySelector('.admin-form-edit').scrollIntoView({ behavior: 'smooth' });
        };
    </script>

    <?php endif; ?>

    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>    

</body>
</html>
