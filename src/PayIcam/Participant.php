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
    	'17h30-19h30'=>'#000', // noir
    	'19h30-20h'=>'#000', // noir  >2000
	    '21h-21h45'=>'#670490', // Bordeaux    1500-2000
	    '21h45-22h30'=>'#0F129A', // bleue foncé 0-1000
	    '22h30-23h'=>'#137911' // vert vert 1000-1500
    );
    public static $plage_horaire_quotas = array(
    	'17h30-20h'=>500, // bleu clair  >2000
	    '21h-21h45'=>1500, // Bordeaux    1500-2000
	    '21h45-22h30'=>1500, // bleue foncé 0-1000
	    '22h30-23h'=>650 // vert vert 1000-1500
    );

	public static $promos = array(
		'Intégrés'             =>array(117=>117,118=>118,119=>119,120=>120,121=>121),
		'Apprentis'            =>array(2017=>2017,2018=>2018,2019=>2019,2020=>2020,2021=>2021),
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
		'121' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'120' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'119' => array('nbInvites' => 3,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'118' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'117' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'2021' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'2020' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'2019' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'2018' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		'2017' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		"Formations Continues" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		"Erasmus" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		"Permanents" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 22),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 22)),
		"Ingenieur" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 22),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 22)),
		"Parents" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 0, "soiree" => 22),
				'prixInvite' => array("repas" => NULL, "buffet" => NULL, "soiree" => NULL)),
		"Artistes" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		"Autre Site" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 22),
				'prixInvite' => array("repas" => NULL, "buffet" => 3, "soiree" => 22)),
		"VIP" => array('nbInvites' => 1,
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