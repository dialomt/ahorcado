<?php
session_start();

function startGame() {
    include 'words.php'; // Include the file with words
    $secretWord = $words[array_rand($words)];
    $_SESSION['secretWord'] = strtoupper($secretWord);
    $_SESSION['discoveredLetters'] = str_repeat('_', strlen($secretWord));
    $_SESSION['remainingAttempts'] = 6;
}

function guessLetter($letter) {
    $secretWord = $_SESSION['secretWord'];
    $discoveredLetters = $_SESSION['discoveredLetters'];

    $letter = strtoupper($letter);

    if (strpos($secretWord, $letter) !== false) {
        for ($i = 0; $i < strlen($secretWord); $i++) {
            if ($secretWord[$i] == $letter) {
                $discoveredLetters[$i] = $letter;
            }
        }
    } else {
        $_SESSION['remainingAttempts']--;
    }

    $_SESSION['discoveredLetters'] = $discoveredLetters;

    if (winGame()) {
        return "win";
    } elseif ($_SESSION['remainingAttempts'] <= 0) {
        return "lose";
    }

    return "continue";
}

function winGame() {
    return $_SESSION['discoveredLetters'] == $_SESSION['secretWord'];
}

function restartGame() {
    session_unset();
    startGame();
}

// Process restart if sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset'])) {
    restartGame();
    // Redirect to avoid form resubmission when reloading the page
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Start the game if there is no secret word in the session
if (!isset($_SESSION['secretWord'])) {
    startGame();
}

// Process the letter if sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['letter'])) {
    $result = guessLetter($_POST['letter']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hangman Game</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Hangman Game</h1>

    <?php
    if (isset($result)) {
        if ($result == "win") {
            echo "<p class='message winner'>Congratulations, you won!</p>";
        } elseif ($result == "lose") {
            echo "<p class='message loser'>Oh no, you lost! The word was: {$_SESSION['secretWord']}</p>";
        }
    }
    ?>

    <p>Word: <?php echo implode(' ', str_split($_SESSION['discoveredLetters'])); ?></p>
    <p>Remaining attempts: <?php echo $_SESSION['remainingAttempts']; ?></p>

    <?php
    if (!winGame() && $_SESSION['remainingAttempts'] > 0) {
        echo "<form action='' method='post'>";
        echo "<label for='letter'>Letter, go! </label>";
        echo "<input type='text' name='letter' maxlength='1' pattern='[A-Za-z]' required>";
        echo "<button type='submit'>Unleash Guess! </button>";
        echo "</form>";
    }
    ?>

    <?php
    if (winGame() || $_SESSION['remainingAttempts'] <= 0) {
        echo "<form action='' method='post'>";
        echo "<button type='submit' name='reset'>Restart Game</button>";
        echo "</form>";
    }
    ?>

</body>
</html>
