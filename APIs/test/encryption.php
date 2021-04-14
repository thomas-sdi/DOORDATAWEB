<?php
require_once '../includes/models/M_oauth.php';

$iv = M_oAuth::genetatePublicKey('AES-256-CBC');

$methods = M_oauth::getEncryptionMethods();
$publicKey = array_key_exists('publicKey', $_POST) ? $_POST['publicKey'] : $iv;
$rawString = array_key_exists('rawString', $_POST) ? $_POST['rawString'] : 'DOORDATA_iOS_Pzm4eQFkxQzjN8WwZJpc';
$selectedMethod = array_key_exists('method', $_POST) ? $_POST['method'] : 'AES-256-CBC';

$oAuth = new M_oAuth($selectedMethod);


foreach ($methods as $method){
	echo $method . '<br>';
}


/*
$textToEncrypt = "DOORDATA_iOS_Pzm4eQFkxQzjN8WwZJpc";
$encryptionMethod = "AES-256-CBC";


$secretHash = "JBqtQoDovMFP2aTBavJLEGnwQ4FkeZyiqpgsbJom";
$iv = $oAuth->genetatePublicKey();
$encryptedText = openssl_encrypt($textToEncrypt,$encryptionMethod,$secretHash, 0, $iv);

echo $encryptedText;
exit;
*/

?>
<form method="post" action="encryption.php">
	Private key is in the MS Word Document;<br>
	Public key (will be generated in the application on install and sent over the internet): <input type="text" name="publicKey" value="<?= $publicKey ?>"><br>
	Encryption method: AES-256-CBC<br>
	Client Id: is in the MS Word document, before the : sign in the client field
	<input type="submit"/>
</form>

<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') exit; //we are done

if ($selectedMethod == '') exit; // no method was provided

echo 'When ' . $selectedMethod . ' encryption method is used with ' . $publicKey . ' public key, the request should look as follows:<br>';



$encryptedString = $oAuth->encrypt($publicKey);

echo '{"client": "' . $encryptedString . ':' . $publicKey . '", "methodIdentifier": "getLogin", "LOGIN": "BBBB",  "PASSWORD": "CCCC"}';
?>