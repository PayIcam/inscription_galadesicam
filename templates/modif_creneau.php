<?php

global $settings;
$confSQL = $settings['settings']['confSQL'];
try{
    $db_creneau = new PDO('mysql:host='.$confSQL['sql_host'].';dbname='.$confSQL['sql_db'],$confSQL['sql_user'], $confSQL['sql_pass'], array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }
catch(PDOException $e) {
    die('<h1>Impossible de se connecter a la base de donnee</h1><p>'.$e->getMessage().'<p>');
    };


$email = $Auth->getUserField('email');
$place=$db_creneau->prepare('SELECT * FROM guests WHERE email= :email');
$place -> bindParam('email', $email, PDO::PARAM_STR);
$place->execute();
$icam=$place->fetch();

$place_invite=$db_creneau->prepare('SELECT * FROM guests WHERE id IN (SELECT guest_id FROM icam_has_guest JOIN guests ON guests.id = icam_has_guest.icam_id WHERE guests.email= :email)');
$place_invite -> bindParam('email', $email, PDO::PARAM_STR);
$place_invite->execute();
$invite=$place_invite->fetchAll();?>
<h1>Changement de créneau</h1>

<form action="enreg" method="post" id='creneau_change'>
		<h3>Votre place </h3>
		<?php if (is_null($icam['bracelet_id'])){?> 	<!-- Verifie si pas bracelet -->
		<select class="form-control" name='<?php echo($icam['id'])?>' form='creneau_change'>
		  <option <?php if ($icam['plage_horaire_entrees']=="21h-21h45"){?> selected <?php } ?> value="21h-21h45">21h-21h35: 1er créneau</option>
		  <option <?php if ($icam['plage_horaire_entrees']=="21h45-22h30"){?> selected <?php } ?> value="21h45-22h30">21h50-22h25: 2ème créneau</option>
		  <option <?php if ($icam['plage_horaire_entrees']=="22h30-23h"){?> selected <?php } ?> value="22h30-23h">22h40-23h10: 3ème créneau</option>
		</select>
	<?php }
	else{ ?>
		<em>Vous avez déjà retiré votre bracelet, vous ne pouvez donc pas changer de créneau</em>

	<?php } 

	if (!empty($invite)) { ?>
		<h3>Vos invités </h3>
		<?php foreach ($invite as $guest){ ?>
			<h4><?php echo($guest['prenom'].' '.$guest['nom']) ?></h4>

			<?php if (is_null($guest['bracelet_id'])){?>	<!-- Verifie si pas bracelet -->

				<select class="form-control" name='<?php echo($guest['id'])?>' form='creneau_change'>
				  <option <?php if ($guest['plage_horaire_entrees']=="21h-21h45"){?> selected <?php } ?> value="21h-21h45">21h-21h35: 1er créneau</option>
				  <option <?php if ($guest['plage_horaire_entrees']=="21h45-22h30"){?> selected <?php } ?> value="21h45-22h30">21h50-22h25: 2ème créneau</option>
				  <option <?php if ($guest['plage_horaire_entrees']=="22h30-23h"){?> selected <?php } ?> value="22h30-23h">22h40-23h10: 3ème créneau</option>
				</select>
		<?php }
		else{ ?>
		<em>Cet invité a déjà un bracelet, vous ne pouvez donc pas changer son créneau</em>

	<?php } 
		}
	}?>
	<br>
	 <input type="submit" class="btn btn-primary" value='Enregistrer'>
	 <button type="button" class="btn btn-default">Annuler</button>
 </form>