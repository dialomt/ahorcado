<?php
session_start();

function initiateGame() {
    include 'wordList.php';
    $mysteryWord = $wordList[array_rand($wordList)];
    $_SESSION['mysteryWord'] = strtoupper($mysteryWord);
    $_SESSION['revealedLetters'] = str_repeat('_', strlen($mysteryWord));
    $_SESSION['remainingTries'] = 6;
}

function attemptLetter($letter) {
    $mysteryWord = $_SESSION['mysteryWord'];
    $revealedLetters = $_SESSION['revealedLetters'];

    $letter = strtoupper($letter);

    if (strpos($mysteryWord, $letter) !== false) {
        for ($i = 0; $i < strlen($mysteryWord); $i++) {
            if ($mysteryWord[$i] == $letter) {
                $revealedLetters[$i] = $letter;
            }
        }
    } else {
        $_SESSION['remainingTries']--;
    }

    $_SESSION['revealedLetters'] = $revealedLetters;

    if (victoryAchieved()) {
        return "win";
    } elseif ($_SESSION['remainingTries'] <= 0) {
        return "lose";
    }

    return "continue";
}

function victoryAchieved() {
    return $_SESSION['revealedLetters'] == $_SESSION['mysteryWord'];
}

function resetGame() {
    session_unset();
    initiateGame();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restart'])) {
    resetGame();
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

if (!isset($_SESSION['mysteryWord'])) {
    initiateGame();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['letter'])) {
    $gameStatus = attemptLetter($_POST['letter']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hangman Challenge</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Hangman Challenge</h1>

    <?php
    if (isset($gameStatus)) {
        if ($gameStatus == "win") {
            echo "<p class='gameMessage victoryMessage'>Huzzah! You've bested the hangman!</p>";
        } elseif ($gameStatus == "lose") {
            echo "<p class='gameMessage defeatMessage'>Alas! The hangman claims another. The word was: {$_SESSION['mysteryWord']}</p>";
        }
    }
    ?>

    <p>Word: <?php echo implode(' ', str_split($_SESSION['revealedLetters'])); ?></p>
    <p>Remaining attempts: <?php echo $_SESSION['remainingTries']; ?></p>

    <?php
    if (!victoryAchieved() && $_SESSION['remainingTries'] > 0) {
        echo "<form action='' method='post'>";
        echo "<label for='letter' class='guessPrompt'>Release your letter into the wild:</label>";
        echo "<input type='text' name='letter' class='guessBox' maxlength='1' pattern='[A-Za-z]' required>";
        echo "<button type='submit' class='guessButton'>Unleash Guess!</button>";
        echo "</form>";
    }
    ?>

    <?php
    if (victoryAchieved() || $_SESSION['remainingTries'] <= 0) {
        echo "<form action='' method='post'>";
        echo "<button type='submit' name='restart'>Restart Challenge</button>";
        echo "</form>";
    }
    ?>

</body>
</html>
