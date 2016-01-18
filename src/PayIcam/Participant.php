<?php 

namespace PayIcam;

/**
* Classe des participants au Gala
*/
class Participant{
    public static $plage_horaire_entrees = array(
    	'17h30-18h'=>'18h-20h: Conférence',
    	'20h-20h30'=>'20h-20h30: Diner',
	    '21h-21h30'=>'21h-21h30: 1er créneau',
	    '21h30-22h15'=>'21h30-22h15: 2ème créneau',
	    '22h15-23h'=>'22h15-23h: 3ème créneau'
    );
    public static $plage_horaire_couleurs = array(
    	'17h30-18h'=>'#07AAFF', // bleu clair
    	'20h-20h30'=>'#07AAFF', // bleu clair  >2000
	    '21h-21h30'=>'#670490', // Bordeaux    1500-2000
	    '21h30-22h15'=>'#0F129A', // bleue foncé 0-1000
	    '22h15-23h'=>'#137911' // vert vert 1000-1500
    );
    public static $plage_horaire_quotas = array(
    	'17h30-20h30'=>500, // bleu clair  >2000
	    '21h-21h30'=>500, // Bordeaux    1500-2000
	    '21h30-22h15'=>500, // bleue foncé 0-1000
	    '22h15-23h'=>1000 // vert vert 1000-1500
    );

	public static $promos = array(
		'Intégrés'             =>array(116=>116,117=>117,118=>118,119=>119,120=>120),
		115                    =>115,
		'Apprentissage'            =>array(2016=>2016,2017=>2017,2018=>2018,2019=>2019,2020=>2020),
		'Erasmus'              =>'Erasmus',
		'Formations Continues' =>'Formations Continues',
		'Permanent'            =>'Permanent',
		'Ingenieur'            =>'Ingénieur',
		'Parent'               =>'Parent',
		'Artiste'              =>'Artiste',
		'Artiste Icam'         =>'Artiste Icam',
		'Extras'               =>'Extras',
		'VIP'                  =>'VIP',
    );

	public static $prixParPromo = array(
		'120' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'119' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'118' => array('nbInvites' => 3,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'117' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'116' => array('nbInvites' => 2,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'2020' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'2019' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'2018' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'2017' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'2016' => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Formations Continues" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		'115' => array('nbInvites' => 4,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Erasmus" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 2, "soiree" => 18),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Permanents" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Ingenieur" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Parents" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 20, "buffet" => 3, "soiree" => 20),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Artistes" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"Extras" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => NULL, "buffet" => NULL, "soiree" => 0),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20)),
		"VIP" => array('nbInvites' => 1,
				'prixIcam' => array("repas" => 0, "buffet" => 0, "soiree" => 0),
				'prixInvite' => array("repas" => 20, "buffet" => 3, "soiree" => 20))
	);

    public static $paiement = array('espece'=>'En espèces','CB'=>'Carte bancaire','cheque'=>'Par Chèque','Lydia'=>'Avec Lydia');
    public static $sexe = array('1'=>'Homme','2'=>'Femme');
    public static $formule = array('Repas'=>'Repas','Buffet'=>'Conférence','Soirée'=>'Soirée');

    public static function getPricePromo($promo = 'Ingenieur'){
    	return self::$prixParPromo[$promo];
    }

}