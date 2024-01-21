<?php
session_start();

function iniciarJuego() {
    $palabras = ["programacion", "javascript", "php", "html", "css", "python"];
    $palabraSecreta = $palabras[array_rand($palabras)];
    $_SESSION['palabraSecreta'] = strtoupper($palabraSecreta);
    $_SESSION['letrasDescubiertas'] = str_repeat('_', strlen($palabraSecreta));
    $_SESSION['intentosRestantes'] = 6;
}

function adivinarLetra($letra) {
    $palabraSecreta = $_SESSION['palabraSecreta'];
    $letrasDescubiertas = $_SESSION['letrasDescubiertas'];

    $letra = strtoupper($letra);

    if (strpos($palabraSecreta, $letra) !== false) {
        for ($i = 0; $i < strlen($palabraSecreta); $i++) {
            if ($palabraSecreta[$i] == $letra) {
                $letrasDescubiertas[$i] = $letra;
            }
        }
    } else {
        $_SESSION['intentosRestantes']--;
    }

    $_SESSION['letrasDescubiertas'] = $letrasDescubiertas;

    if (ganarJuego()) {
        return "ganar";
    } elseif ($_SESSION['intentosRestantes'] <= 0) {
        return "perder";
    }

    return "continuar";
}

function ganarJuego() {
    return $_SESSION['letrasDescubiertas'] == $_SESSION['palabraSecreta'];
}

function reiniciarJuego() {
    session_unset();
    iniciarJuego();
}

// Procesa el reinicio si se envía por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset'])) {
    reiniciarJuego();
    // Redirige para evitar reenvío del formulario al recargar la página
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Inicia el juego si no hay una palabra secreta en la sesión
if (!isset($_SESSION['palabraSecreta'])) {
    iniciarJuego();
}

// Procesa la letra si se envía por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['letra'])) {
    $resultado = adivinarLetra($_POST['letra']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego del Ahorcado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Juego del Ahorcado</h1>

    <?php
    if (isset($resultado)) {
        if ($resultado == "ganar") {
            echo "<p class='mensaje ganador'>¡Felicidades, has ganado!</p>";
        } elseif ($resultado == "perder") {
            echo "<p class='mensaje perdedor'>¡Oh no, has perdido! La palabra era: {$_SESSION['palabraSecreta']}</p>";
        }
    }
    ?>

    <p>Palabra: <?php echo implode(' ', str_split($_SESSION['letrasDescubiertas'])); ?></p>
    <p>Intentos restantes: <?php echo $_SESSION['intentosRestantes']; ?></p>

    <?php
    if (!ganarJuego() && $_SESSION['intentosRestantes'] > 0) {
        echo "<form action='' method='post'>";
        echo "<label for='letra'>Adivina una letra:</label>";
        echo "<input type='text' name='letra' maxlength='1' pattern='[A-Za-z]' required>";
        echo "<button type='submit'>Adivinar</button>";
        echo "</form>";
    }
    ?>

    <?php
    if (ganarJuego() || $_SESSION['intentosRestantes'] <= 0) {
        echo "<form action='' method='post'>";
        echo "<button type='submit' name='reset'>Reiniciar Juego</button>";
        echo "</form>";
    }
    ?>

</body>
</html>
