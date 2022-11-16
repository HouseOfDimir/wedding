<?php

header('Content-Type:application/json');
$error        = [];
$data         = json_decode(file_get_contents('php://input'), true);
$errorMessage = [
                    'name'        => ['mess' => 'Le champ Nom est requis ou incorrect', 'validate'                 => FILTER_SANITIZE_FULL_SPECIAL_CHARS],
                    'firstname'   => ['mess' => 'Le champ Prénom est requis ou incorrect', 'validate'              => FILTER_SANITIZE_FULL_SPECIAL_CHARS],
                    'type'        => ['mess' => 'Le champ Type est requis ou incorrect', 'validate'                => FILTER_VALIDATE_INT],
                    'email'       => ['mess' => 'Le champ Email est requis ou incorrect', 'validate'               => FILTER_VALIDATE_EMAIL],
                    'diner'       => ['mess' => 'Le champ Participe au repas est requis ou incorrect', 'validate'  => FILTER_VALIDATE_BOOLEAN],
                    'sleep'       => ['mess' => 'Le champ Dort sur place est requis ou incorrect', 'validate'      => FILTER_VALIDATE_BOOLEAN],
                    'babysitter'  => ['mess' => 'Le champ Coin enfant est requis ou incorrect', 'validate'         => FILTER_VALIDATE_BOOLEAN],
                    'brunch'      => ['mess' => 'Le champ Participe au brunch est requis ou incorrect', 'validate' => FILTER_VALIDATE_BOOLEAN],
/*                     'message'     => ['mess' => 'Le champ Commentaire est requis ou incorrect', 'validate'         => FILTER_SANITIZE_FULL_SPECIAL_CHARS],
 */                    'participant' => ['mess' => 'Le champ Participants est incorrect', 'validate'                  => FILTER_VALIDATE_INT]
                ];
unset($data['']);

foreach($data as $key => &$value){
    if(explode('_', $key)[0] == 'message'){continue;}
    if(count(explode('_', $key)) === 0){
        if(!array_key_exists($key, $errorMessage)){
            $error[] = $errorMessage[$key]['mess'];
        }
    }else{
        $subKey = explode('_', $key)[0];
        if(!array_key_exists($subKey, $errorMessage)){
            $error[] = $errorMessage[$subKey]['mess'];
        }
    }
    if($value === ''){
        $error[] = $errorMessage[isset($errorMessage[$key]) ? $key : explode('_', $key)[0]]['mess'];
    }else{
        if(count(explode('_', $key)) === 0){
            if(!filter_var($value, $errorMessage[$key]['validate'])){
                $error[] = $errorMessage[$key]['mess'];
            }else{
                $value = filter_var($value, $errorMessage[$key]['validate']);
            }
        }
    }
}

if(is_int($data['participant'])){
    for($i=0; $i < $data['participant'] -1; $i++){
        $data['name_'.$i] = filter_var($data['name_'.$i], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $data['firstname_'.$i] = filter_var($data['name_'.$i], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $data['type_'.$i] = filter_var($data['type_'.$i], FILTER_VALIDATE_INT);
    }
}

count($error) > 0 && die(json_encode(['status' => 'KO', 'error' => $error]));

$EmailTo = "clara.ipponich@gmail.com";
$Subject = "Nouveaux participants au mariage !";
$Body  = "Bonjour bébé d'amour,<br /><br />";
$Body .= "Voici un nouveau participant au mariage !<br /><br />";
$Body .= "<b>Nom:</b> ". filter_var($data['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . "  -  <b>Prénom:</b> " . filter_var($data['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ', ' . ($data['type'] == 1 ? 'Adulte' : 'Enfant') . '<br />';
$Body .= $data['brunch'] ? '<b>Participation au brunch:</b> Oui<br />' : '<b>Participation au brunch:</b> Non<br />';
$Body .= $data['diner'] ? '<b>Participation au repas:</b> Oui<br />' : '<b>Participation au repas:</b> Non<br />';
$Body .= $data['sleep'] ? '<b>Dort sur place:</b> Oui<br />' : '<b>Dort sur place:</b> Non<br />';
$Body .= $data['babysitter'] ? '<b>Utilise le coin enfants:</b> Oui<br />' : '<b>Utilise le coin enfants:</b> Non<br /><br />';
if($data['participant'] > 0){
    $Body .= 'Cette personne vient accompagnée des personnes suivantes: <br />';
    for($i=1; $i <= $data['participant']; $i++){
        $Body .= "<b>Nom:</b> ". $data['name_'.$i] . "  -  <b>Prénom:</b> " . $data['firstname_'.$i] . ', ' . ($data['type_'.$i] == 1 ? 'Adulte' : 'Enfant') . '<br />';
    }
}
if($data['message'] !== ''){
    $Body .= "<br /><b>La personne a laissé un commentaire:</b> <br /><br />";
    $Body .= filter_var($data['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

require('./PHPMailer/PHPMailer.php');
require('./PHPMailer/SMTP.php');
require('./PHPMailer/Exception.php');

$mail = createMailObject();
$mail->AddAddress($EmailTo);
$mail->SetFrom($data['email']);
$mail->AddReplyTo($data['email']);
$mail->Subject = $Subject;
$mail->Body = $Body;

if($mail->Send()){
    $mailConfirm = createMailObject();
    $mailConfirm->Debugoutput = 'html';
    $mailConfirm->IsHTML(true);
    $mailConfirm->CharSet = 'UTF-8'; 
    $mailConfirm->AddAddress($data['email']);
    $mailConfirm->SetFrom($EmailTo);
    $mailConfirm->AddReplyTo($EmailTo);
    $mailConfirm->Subject = "Confirmation de la réception de votre participation";
    $message  = "<html><body>";
   
   $message .= "<table width='100%' bgcolor='#e0e0e0' cellpadding='0' cellspacing='0' border='0'>";
   
   $message .= "<tr><td>";
   
   $message .= "<table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='max-width:650px; background-color:#fff; font-family:Verdana, Geneva, sans-serif;'>";
    
   $message .= "<thead>
      <tr height='80'>
       <th colspan='4' style='background-color:#f5f5f5; border-bottom:solid 1px #bdbdbd; font-family:Verdana, Geneva, sans-serif; color:#333; font-size:34px;' >Participation confirmée !</th>
      </tr>
      </thead>";
   $message .= "<tbody> 
      <tr>
       <td colspan='4' style='padding:15px;'>
        <p style='font-size:20px;'>Voici les informations que vous avez envoyé:</p><br />";
        $message .= "<b>Nom:</b> ". filter_var($data['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . "  -  <b>Prénom:</b> " . filter_var($data['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ', ' . ($data['type'] == 1 ? 'Adulte' : 'Enfant') . '<br />';
        $message .= $data['brunch'] ? '<b>Participation au brunch:</b> Oui<br />' : '<b>Participation au brunch:</b> Non<br />';
        $message .= $data['diner'] ? '<b>Participation au repas:</b> Oui<br />' : '<b>Participation au repas:</b> Non<br />';
        $message .= $data['sleep'] ? '<b>Dort sur place:</b> Oui<br />' : '<b>Dort sur place:</b> Non<br />';
        $message .= $data['babysitter'] ? '<b>Utilise le coin enfants:</b> Oui<br />' : '<b>Utilise le coin enfants:</b> Non<br /><br />';
        if($data['participant'] > 0){
            $message .= 'Vous venez accompagné des personnes suivantes: <br />';
            for($i=1; $i <= $data['participant']; $i++){
                $message .= "<b>Nom:</b> ". $data['name_'.$i] . "  -  <b>Prénom:</b> " . $data['firstname_'.$i] . ', ' . ($data['type_'.$i] == 1 ? 'Adulte' : 'Enfant') . '<br />';
                $message .= $data['brunch_'.$i] ? '<b>Participation au brunch:</b> Oui<br />' : '<b>Participation au brunch:</b> Non<br />';
                $message .= $data['diner_'.$i] ? '<b>Participation au repas:</b> Oui<br />' : '<b>Participation au repas:</b> Non<br />';
                $message .= $data['sleep_'.$i] ? '<b>Dort sur place:</b> Oui<br />' : '<b>Dort sur place:</b> Non<br />';
                $message .= $data['babysitter_'.$i] ? '<b>Utilise le coin enfants:</b> Oui<br />' : '<b>Utilise le coin enfants:</b> Non<br /><br />';
          
            }
        }
        if($data['message'] !== ''){
            $message .= "<br /><b>Vous avez laissé un commentaire:</b> <br /><br />";
            $message .= filter_var($data['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
  
        $message .="<hr />
        <img src='https://c.tadst.com/gfx/900x506/winter-lake.jpg?1' style='height:auto; width:100%; max-width:100%;' />
        <p style='font-size:15px; font-family:Verdana, Geneva, sans-serif;'>.</p>
       </td>
      </tr>
      
      <tr height='80'>
       <td colspan='4' align='center' style='background-color:#f5f5f5; font-size:24px; '>
       <label>
       A bientôt ! 
       </label>
       </td>
      </tr>
      
      </tbody>";
    
   $message .= "</table>";
   
   $message .= "</td></tr>";
   $message .= "</table>";
   
   $message .= "</body></html>";
    $mailConfirm->Body = $message;

    $mailConfirm->Send() ? die(json_encode(['status' => 'OK'])) : die(json_encode(['status' => 'KO', 'error' => "Une erreur est survenue lors de l'envoi du mail. Veuillez réessayer ultérieurement"]));

}

function createMailObject(){
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP();
    $mail->IsHTML(true);
    $mail->Mailer = "smtp";
    $mail->SMTPDebug  = 0;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure ="ssl";
    $mail->Port       = 465;
    $mail->Host       = "smtp.googlemail.com";
    $mail->Username   = "ConnectedCompany.IT@gmail.com";
    $mail->Password   = "gebenmdiyggauqdz";

    return $mail;
}
?>