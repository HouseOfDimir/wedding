<?php

$error = [];
$errorMessage = [
                    'name'       => ['mess' => 'Le champ Nom est requis', 'validate' => FILTER_SANITIZE_FULL_SPECIAL_CHARS],
                    'firstname'  => ['mess' => 'Le champ Prénom est requis', 'validate' => FILTER_SANITIZE_FULL_SPECIAL_CHARS],
                    'email'      => ['mess' => 'Le champ Email est requis', 'validate' => FILTER_VALIDATE_EMAIL],
                    'diner'      => ['mess' => 'Le champ Participe au repas est requis', 'validate' => FILTER_VALIDATE_BOOL],
                    'sleep'      => ['mess' => 'Le champ Dort sur place est requis', 'validate' => FILTER_VALIDATE_BOOL],
                    'babysitter' => ['mess' => 'Le champ Coin enfant est requis', 'validate' => FILTER_VALIDATE_BOOL],
                    'brunch'     => ['mess' => 'Le champ Participe au brunch est requis', 'validate' => FILTER_VALIDATE_BOOL],
                ];

foreach($_POST as $key => $value){
    if($value === ''){
        $error[] = $errorMessage[$key];
    }else{
        filter_var($value, $errorMessage[$key]['validate']);
    }
}

$EmailTo = "clara.ipponich@gmail.com";
$Subject = "Nouveaux participants au mariage !";

// prepare email body text
$Body  = "Bonjour bébé d'amour,<br /><br />";
$Body .= "Voici un nouveau participant au mariage !<br /><br />";
$Body .= "<b>Nom:</b> ". $_POST['name'] . "  -  <b>Prénom:</b>" . $_POST['firstname'] . '<br />';
$Body .= "<b>Participe au brunch:</b> " . $_POST['brunch'] ? 'Oui' : 'Non' . '<br />';
$Body .= "<b>Participe au repas:</b> " . $_POST['diner'] ? 'Oui' : 'Non' . '<br />';
$Body .= "<b>Dort sur place:</b> " . $_POST['sleep'] ? 'Oui' : 'Non' . '<br />';
$Body .= "<b>Utilise le coin enfants:</b> " . $_POST['babysitter'] ? 'Oui' : 'Non' . '<br /><br />';
if($_POST['message'] !== ''){
    $Body .= "<b>La personne a laissé un commentaire:</b> <br /><br />";
    $Body .= filter_var($_POST['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

// send email
$success = mail($EmailTo, $Subject, $Body, "From:".$_POST['email']);

// redirect to success page
if($success && count($error) === 0){
   return true;
}else{
    if(count($error) > 0){
        return json_encode($error);
    }else{
        return "Une erreur est survenue lors de l'envoi du mail. Veuillez réessayer ultérieurement";
    }
}

?>