<?php
require_once("config/dbconf.php");
session_start();

global $config;
$pdo = new PDO($config['host'], $config['user'], $config['password']);
$r = backup($pdo);
if(!isset($_SESSION['user'])){
    save($pdo);
    header("Location: /login.php");
    exit;
}

board($pdo);
if(isset($_POST['reset_best'])){
    unset($_SESSION['best_score']);
    best($pdo);
}
if(empty($_SESSION['choice']) || isset($_POST['reset'])){
    $choice  =  rand(0,100);
    $_SESSION['score'] = 0;
    $_SESSION['choice'] = $choice;
    $_SESSION['response'] = null;
    save($pdo);
}else{
    $choice = $_SESSION['choice'];
    $_SESSION['score'] = $r['save_score'];
    $_SESSION['choice'] = $r['save_number'];
    $_SESSION['response'] = $r['save_response'];
    $_SESSION['guess']=$r['save_guess'];
}
if( empty($_POST['guess'])){
    if($_SESSION['response'] == null){
        $_SESSION['response'] = 'Pas de nombre';
    }
}else{
    $guess = $_POST['guess'];
    $_SESSION['score']++;
    if($guess > $choice) {
        $_SESSION['response'] = "C'est moins";
    }elseif($guess < $choice){
        $_SESSION['response'] = "C'est plus";
    }else{
        $_SESSION['response'] = "C'est gagné";
        if( !isset($_SESSION['best_score'])
            || $_SESSION['best_score'] > $_SESSION['score']){
            $_SESSION['best_score'] = $_SESSION['score'];
            best($pdo);
        }
        unset($_SESSION['choice']);
    }
    save($pdo);
}

/*  LEADER BOARD*/

function board($pdo){
$q = $pdo->prepare("SELECT login, best_score
                        from users
                        ORDER BY best_score LIMIT 0,10"
);
$q->execute();
    echo('<table border="1px">');
    echo('<th>Nom</th><th>Score</th>');
        while($result = $q->fetch()){
              echo('<tr>'.'<td>'.$result['login'].'</td>'.'<td>'.$result['best_score'].'</td>'.'</tr>');
        }
    echo('</table>');
}

/* SAVE */

function save($pdo){
    $q = $pdo->prepare("UPDATE users
                           SET save_score = :score ,save_number =:choice, save_response =:response, save_guess=:guess
                           WHERE login =:login"
    );
    $q->bindParam("score",$_SESSION['score']);
    $q->bindParam("response",$_SESSION['response']);
    $q->bindParam("guess",$_POST['guess']);
    $q->bindParam("choice",$_SESSION['choice']);
    $q->bindParam("login",$_SESSION['user']);
    $q->execute();
    $result=$q->fetch();

}

/* Save back*/

function backup($pdo){
    $q = $pdo->prepare("SELECT save_score, save_number, save_response, save_guess
                             FROM users
                             WHERE login =:login"
    );
    $q->bindParam("login",$_SESSION['user']);
    $q->execute();
    $result = $q->fetch();
    return $result;
}

/* Best Score*/

function best($pdo){
    $q = $pdo->prepare("UPDATE users
                        SET best_score = :score
                        WHERE login = :login"
    );
    $q->bindParam("score",$_SESSION['score']);
    $q->bindParam("login",$_SESSION['user']);
    $q->execute();
    $result=$q->fetch();


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Des papiers dans un bol </title>
</head>
<body>

<?php echo $_SESSION['response'];?> <br>
Nombre de coup : <?php echo $_SESSION['score']; ?><br>
<em>[Meilleur score pour <?php echo $_SESSION['user'];?>:
    <?php
    echo !isset($_SESSION['best_score'])
        ? "Pas de meilleur score"
        : $_SESSION['best_score'];
    ?>]</em>
<form method="POST">
    <input type="text" name="guess" autofocus>
    <input type="submit">
    <input type="submit" name="reset" value="reset">
    <input type="submit" name="reset_best" value="reset best">
</form>
<em>(Dernière valeur saisie: <?php echo $_SESSION['guess'] ?>)
    (La réponse est <?php echo $_SESSION['choice']?>)</em>


<form method="POST" action="/login.php">
    <input type="submit" name="logout" value="Logout">
</form>
</body>
</html>