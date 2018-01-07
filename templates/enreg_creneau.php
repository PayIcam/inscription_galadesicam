<?php include 'config.php';

$participants = $bd->prepare('SELECT COUNT(*) FROM guests');
$participants->execute();
$nb_participants = $participants->fetch();

$premier_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees =\'21h-21h45\'');
$premier_creneau->execute();
$nb_creneau1 = $premier_creneau->fetch();

$deuxieme_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees = \'21h45-22h30\'');
$deuxieme_creneau->execute();
$nb_creneau2 = $deuxieme_creneau->fetch();

$troisieme_creneau = $bd->prepare('SELECT COUNT(*) FROM guests WHERE plage_horaire_entrees = \'22h30-23h\'');
$troisieme_creneau->execute();
$nb_creneau3 = $troisieme_creneau->fetch();

$quotat_creneau1 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_21h_21h45\'');
$quotat_creneau1->execute();
$quotat1 = $quotat_creneau1->fetch();

$quotat_creneau2 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_21h45_22h30\'');
$quotat_creneau2->execute();
$quotat2 = $quotat_creneau2->fetch();

$quotat_creneau3 = $bd->prepare('SELECT value FROM configs WHERE name = \'quota_entree_22h30_23h\'');
$quotat_creneau3->execute();
$quotat3 = $quotat_creneau3->fetch();
 
if ($quotat1<$nb_creneau1){  //verifie si creneau dispo
	$creneau1=false;
}else{
	$creneau1=true;
} 

if ($quotat2<$nb_creneau2){  //verifie si creneau dispo
	$creneau2=false;
}else{
	$creneau2=true;
} 

if ($quotat3<$nb_creneau3){  //verifie si creneau dispo
	$creneau3=false;
}else{
	$creneau3=true;
}

foreach ($_POST as $creneau) {
	if ($creneau == '21h-21h45' && $creneau1==false){
			// Functions::setFlash("Le premier créneau est complet",'danger');
		echo('Premier créneau complet');
	}
	if ($creneau == '21h45-22h30' && $creneau2==false){
			// Functions::setFlash("Le second créneau est complet",'danger');
		echo('Deuxième créneau complet');
	}
	if ($creneau == '22h30-23h' && $creneau3==false){
			// Functions::setFlash("Le troisième créneau est complet",'danger');
		echo('Troisième créneau complet');
	}
}
foreach ($_POST as $key => $value) {

	$new_creneau = $bd->prepare('UPDATE guests SET plage_horaire_entrees = :horaire WHERE id = :login ');
	$new_creneau -> bindParam('horaire', $value, PDO::PARAM_STR);
	$new_creneau -> bindParam('login', $key, PDO::PARAM_INT);
	$new_creneau->execute();
}

var_dump($_POST);

var_dump($creneau2);
?>