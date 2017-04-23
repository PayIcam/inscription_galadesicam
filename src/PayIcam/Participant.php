<?php

namespace PayIcam;

/**
* Classe des participants au Gala
*/
class Participant {
    public static $plage_horaire_entrees = array(
	    '17h30-19h30'=>'17h30-19h30: Conférence',
	    '19h30-20h'=>'19h30-20h: Diner',
	    '21h-21h45'=>'21h-21h45: 1er créneau',
	    '21h45-22h30'=>'21h45-22h30: 2ème créneau',
	    '22h30-23h'=>'22h30-23h: 3ème créneau'
	);
    public static $plage_horaire_couleurs = array(
    	'17h30-19h30'=>'#000', // noir 				0-700
    	'19h30-20h'=>'#000', // noir  >2000
	    '21h-21h45'=>'#670490', // Bordeaux    		701-1500
	    '21h45-22h30'=>'#0F129A', // bleue foncé 	1501-2300
	    '22h30-23h'=>'#137911' // vert vert  		2301-3000
    );
    public static $plage_horaire_quotas = array(
    	'17h30-20h'=>500, // bleu clair  	0-700
	    '21h-21h45'=>925, // Bordeaux    	701-1500
	    '21h45-22h30'=>925, // bleue foncé  1501-2300
	    '22h30-23h'=>650 // vert vert 		2301-3000
    );

	public static $promos = array(
		'Intégrés'             =>array(117=>117,118=>118,119=>119,120=>120,121=>121),
		'Apprentis'            =>array(2017=>2017,2018=>2018,2019=>2019,2020=>2020,2021=>2021),
    );

	public static $prixParPromo = array(
		'121' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'120' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'119' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => NULL),
				'prixInvite' => array("soiree" => 15)),
		'118' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'117' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'2021' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'2020' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'2019' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'2018' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => NULL),
				'prixInvite' => array("soiree" => 15)),
		'2017' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15)),
		'Ingenieur' => array('nbInvites' => 2,
				'prixIcam' => array("soiree" => 15),
				'prixInvite' => array("soiree" => 15))
	);

    public static $paiement = array('espece'=>'En espèces','CB'=>'Carte bancaire','cheque'=>'Par Chèque','Pumpkin'=>'Avec Pumpkin','PayIcam'=>'Avec PayIcam');
    public static $sexe = array('1'=>'Homme','2'=>'Femme');
    public static $formule = array('Soirée'=>'Soirée');

    public static function getPricePromo($promo = 'Ingenieur') {
    	if (!isset(self::$prixParPromo[$promo]))
    		throw new \Exception("Promo inconnue", 1);
    	return self::$prixParPromo[$promo];
    }

}