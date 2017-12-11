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
		'Intégrés'             =>array(118=>118,119=>119,120=>120,121=>121,122=>122,),
		'Apprentis'            =>array(2018=>2018,2019=>2019,2020=>2020,2021=>2021, 2022=>2022),
		'Erasmus'              =>'Erasmus',
		'Formations Continues' =>'Formations Continues',
		'Permanent'            =>'Permanent',
		'Ingenieur'            =>'Ingénieur',
		'Parent'               =>'Parent',
		'Artiste'              =>'Artiste',
		'Artiste Icam'         =>'Artiste Icam',
		'Autre Site'           =>'Autre Site',
		'VIP'                  =>'VIP',
    );

	public static $prixParPromo = array(
		'122' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'121' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'120' => array('nbInvites' => 3,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'119' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'118' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'2022' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'2021' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'2020' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'2019' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		'2018' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		"Formations Continues" => array('nbInvites' => 0,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		"Erasmus" => array('nbInvites' => 0,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		"Permanents" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 23)),
		"Ingenieur" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 21),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 23)),
		"Parents" => array('nbInvites' => 0,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 30),
				'prixInvite' => array("repas" => NULL, "buffet" => NULL, "soiree" => NULL)),
		"Artistes" => array('nbInvites' => 0,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		"Autre Site" => array('nbInvites' => 0,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 23),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 23)),
		"VIP" => array('nbInvites' => 3,
				'prixIcam' => array("repas" => 0, "buffet" => 0, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 20))
	);

    public static $paiement = array('espece'=>'En espèces','CB'=>'Carte bancaire','cheque'=>'Par Chèque','Pumpkin'=>'Avec Pumpkin','PayIcam'=>'Avec PayIcam');
    public static $sexe = array('1'=>'Homme','2'=>'Femme');
    public static $formule = array('Repas'=>'Repas','Buffet'=>'Conférence','Soirée'=>'Soirée');

    public static function getPricePromo($promo = 'Ingenieur') {
    	if (!isset(self::$prixParPromo[$promo]))
    		throw new \Exception("Promo inconnue", 1);
    	return self::$prixParPromo[$promo];
    }

}