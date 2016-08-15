<?php 
// check to prevent eMail injections
function checkmail($email)
{
	//filter_var() santitizes the eMail address using FILTER_SANITIZE_EMAIL
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	
	//filter_var() validates the eMail address using FILTER_VALIDATE_EMAIL
	if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

//random password generator
function generatePassword($length=9, $strength=1) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}

?>