<?php
session_start();
if(empty($_SESSION['choice'])){
    $choice = rand(0,5);
    $_SESSION['choice'] = $choice;
}else{
    $choice = $_SESSION['choice'];
}
if(empty($_SESSION['i'])){
    $i = 0;
    $_SESSION['i'] = $i;
}
if(empty($_SESSION['best'])){
    $best = null;
    $_SESSION['best'] = $best;
}
$response = null;
$essai = 0;
if(!isset($_POST['guess'])
    || empty($_POST['guess'])){
    $_response = 'pas de nombre';
}
if(!isset($_POST['reset'])){
    unset($_COOKIE['PHPSESSID']);
}
else{

    $guess = $_POST['guess'];
    if($guess > $choice){
        $response = 'C est moins';
        $_SESSION['i'] = $_SESSION['i'] + 1 ;
        $essai = $_SESSION['i'];
    }else if($guess < $choice){
        $response = "C est plus";
        $_SESSION['i'] = $_SESSION['i'] + 1 ;
        $essai = $_SESSION['i'];
    }else{
        $response = 'C est gagné';
        $_SESSION['i'] = $_SESSION['i'] + 1 ;
        $essai = $_SESSION['i'];
        if($essai < $_SESSION['best'] || empty($_SESSION['best'])) {
            $_SESSION['best'] = $essai;
        }
        unset($_SESSION['choice'], $_SESSION['i']);
    }

}
?>
<!DOCTYPE>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tirage au sort</title>
</head>
<body>
<form method='post'>
    <input type="text" name="guess">
    <input type="submit" name="valider">
    <?php echo($response) ?><br><br>
    Essai : <?php echo($essai) ?> <br><br>
    Meilleur score :<?php echo($_SESSION['best']) ?>
</form>
</body>
</html>
