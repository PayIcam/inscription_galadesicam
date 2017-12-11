<h2 class="page-header">Précisions pour le soir même:</h2>
<div class="row">
    <div class="col-sm-6">
        <h3>Horaires:</h3>
        <ul>
            <li>
                17h30-19h: Conférence
                <?php if ($quotas['buffet'] - $stats['buffetsG'] <= 0): ?>
                    <span class="label label-danger">complet</span>
                <?php endif ?>
            </li>
            <div>
                <strong>Soirée:</strong>
                <?php if ($quotas['soiree'] - $stats['soireesG'] <= 0): ?>
                    <span class="label label-danger">complet</span>
                <?php endif ?>
            </div>
            <li>
                21h-21h35: 1er créneau
                <?php if ($quotas['creneau_21h_21h45'] - $stats['creneau_21h_21h45'] <= 0): ?>
                    <span class="label label-danger">complet</span>
                <?php endif ?>
            </li>
            <li>
                21h50-22h25: 2ème créneau
                <?php if ($quotas['creneau_21h45_22h30'] - $stats['creneau_21h45_22h30'] <= 0): ?>
                    <span class="label label-danger">complet</span>
                <?php endif ?>
            </li>
            <li>
                22h40-23h10: 3ème créneau
                <?php if ($quotas['creneau_22h30_23h'] - $stats['creneau_22h30_23h'] <= 0): ?>
                    <span class="label label-danger">complet</span>
                <?php endif ?>
            </li>
        </ul>
    </div>
</div>

<?php if (!empty($newResa)) { ?>
    <p class="alert alert-warning">
        Vous avez bien soumis une réservation mais vous ne l'avez pas encore réglée.<br>
        Ne tardez pas, vous avez 15 min après quoi elle sera annulée.<br>
        <br>
        <a href="<?= $newResa->tra_url_payicam ?>" class="btn btn-primary">Régler la réservation</a> - <a href="<?= $RouteHelper->getPathFor('cancel') ?>" class="btn btn-danger">Annuler la réservation</a>
    </p>

<?php } elseif ($userResaCount == 0 && $canWeRegisterNewGuests) { ?>
    <p>
        Vous n'avez pas encore de réservation,<br>
        mais vous pouvez encore vous enregistrer:
    </p>
    <p><a data-toggle="modal" data-target="#myModal" class="btn btn-primary">S'inscrire au Gala</a></p>
<?php } elseif ($userResaCount == 0) { ?>
    <p>
        Vous n'avez pas eu le temps de prendre votre réservation ...<br>
        et malheureusement les ventes de places sont maintenant finies ...<br>
        On se dit à l'année prochaine ?
    </p>
<?php } elseif($userResaCount > 1){ ?>
    <p>Nous avons plusieurs réservations enregistrées à votre email...<br>
        <a href="mailto:<?= $emailContactGala ?>">Contactez nous</a> svp !</p>
<?php } ?>
<!-- MODAL-->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Conditions de vente</h4>
      </div>
      <div class="modal-body">
        <h5>Après paiement :</h5>    
    <p> AUCUN REMBOURSEMENT N'EST POSSIBLE APRES PAIEMENT. <br>

    Il est possible d'acheter des tickets boissons par carte bleue le jour du gala. <br> </p>



    <h5>Avant la soirée :</h5>

    <p>Il vous sera transmis un bracelet nominatif après votre inscription au gala. Vous devrez venir le chercher à une pause auprès de membres de la promotion 120 dans un stand prévu à cet effet. <br>

    Il est obligatoire de venir chercher ce bracelet et vous présenter à notre promotion après le paiement sur PayIcam. <br>

    Il est totalement interdit de changer d'invité sans en informer le Gala. Si votre invité n'est pas sur la liste de participants au Gala, même s'il a un bracelet, il sera refusé.</p>



    <h5>Conditions d'entrée à la soirée :</h5>  

    <p>Vous êtes obligés de vous présenter à votre créneau attribué, sinon vous ne pourrez rentrer. Le Gala se réserve le droit de ne pas vous accepter si vous vous présentez au mauvais créneau.<br>

    Une pièce d'identité est nécessaire pour entrer au Gala. Vous ne pourrez rentrer si vous n'avez pas 18 ans.<br>

    Vous êtes obligés de quitter le site du gala après la conférence, et vous n'y entrerez de nouveau qu'à votre créneau attribué. Toute sortie du Gala après 21 heures est définitive.</p>



    <h5>Sécurité :</h5>

    <p>Le Gala se réserve le droit de ne pas vous accepter si vous ou vos invités vous présentez en état d'ébriété.<br>

    Le Gala se réserve le droit de vous expulser de la soirée si votre comportement n'est pas adapté. Des vigiles seront présents et veilleront à cela.</p>

      </div>
      <div class="modal-footer">
        <a href="http://www.villardieres.com/"><button type="button" class="btn btn-default">Je refuse</button></a> <!-- CLIQUE GODDAMNIT -->
        <a href="<?= $editLink ?>"><button type="button" class="btn btn-primary">J'accepte les conditions</button></a>
      </div>
    </div>
  </div>
</div>
<!-- FIN MODAL-->

<?php if(!empty($newResa)){ ?>
    <h2 class="page-header">
        Votre nouvelle réservation:
        <small><a href="<?= ($canWeEditOurReservation)?$editLink:'#' ?>" class="btn btn-primary" <?= ($canWeEditOurReservation && empty($newResa))?'':' title="Vous devez payer votre réservation pour pouvoir l\'éditer à nouveau" disabled="disabled"' ?>>Modifier mes achats</a></small>
    </h2>
    <?php if (!empty($newResa->icamData['nom'])): ?>
    <h3>Vous:</h3>
    <dl class="dl-horizontal">
        <dt>Nom:</dt>
        <dd><?= $newResa->icamData['prenom'] . ' ' . $newResa->icamData['nom'] ?></dd>
        <dt>Mail:</dt>
        <dd><?= $newResa->icamData['email'] ?></dd>
        <dt>Promo:</dt>
        <dd><?= $newResa->icamData['promo'] ?></dd>
        <dt>Téléphone:</dt>
        <dd><?= $newResa->icamData['telephone'] ?></dd>
        <dt>Options:</dt>
        <dd><ul class="list-unstyled">
            <li><span class="label label-<?= (!empty($UserReservation['guest_id'])&&$UserReservation['guest_id']>0)?'success':'info'?>">Soirée</span></li>
            <li><span class="label label-<?= (!empty($UserReservation['buffet']))?'success':(($newResa->icamData['buffet'])?'info':'default') ?>">Conférence</span></li>
            <li><span class="label label-<?= (!empty($UserReservation['tickets_boisson']))?'success':(($newResa->icamData['tickets_boisson'])?'info':'default') ?>"><?= $newResa->icamData['tickets_boisson'] ?> Tickets boisson</span></li>
        </ul></dd>
        <dt>Prix payé:</dt>
        <dd><?= $newResa->icamData['price']; ?> <em><small>par <?= $newResa->icamData['paiement'] ?> le <?= substr($newResa->date_option, 0, 10) ?></small></em></dd>
        <dt>Plage horaire d'entrée :</dt>
        <dd><?= $newResa->icamData['plage_horaire_entrees'] ?></dd>
        <dt>Numéro de bracelet:</dt>
        <dd><?= ($newResa->icamData['bracelet_id'])?$newResa->icamData['bracelet_id']:'<em>Vous avez bien réservé votre place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
    </dl>
    <?php endif ?>
    <?php if (!empty($newResa->guestsData[0]['nom'])): ?>
    <?php if (count($newResa->guestsData) > 0) { ?>
        <h3>Vos invités:</h3>
        <?php foreach ($newResa->guestsData as $key => $guest): ?>
            <?php if ($guest['guest_id'] != -1){ $oldGuest = null;// on va cherche la résa de cet invité
                    foreach ($UserGuests as $g) {
                        if ($g['guest_id'] == $guest['guest_id']) $oldGuest = $g;
                    }
                } ?>
            <h4>Invité #<?= $key+1 ?></h4>
            <dl class="dl-horizontal">
                <dt>Nom:</dt>
                <dd><?= $guest['prenom'] . ' ' . $guest['nom'] ?></dd>
                <dt>Options:</dt>
                <dd><ul class="list-unstyled">
                    <li><span class="label label-<?= (!empty($oldGuest))?'success':'info'?>">Soirée</span></li>
                    <li><span class="label label-<?= (!empty($oldGuest['buffet']))?'success':(($guest['buffet'])?'info':'default') ?>">Conférence</span></li>
                    <li><span class="label label-<?= (!empty($oldGuest['tickets_boisson']))?'success':(($guest['tickets_boisson'])?'info':'default') ?>"><?= $guest['tickets_boisson'] ?> tickets boisson</span></li>
                </ul></dd>
                <dt>Prix payé:</dt>
                <dd><?= $guest['price']; ?> <em><small>par <?= $guest['paiement'] ?> le <?= substr($newResa->date_option, 0, 10) ?></small></em></dd>
                <dt>Horaire d'entrée :</dt>
                <dd><?= corriger_horaire($guest['plage_horaire_entrees']) ?></dd>
                <dt>Numéro de bracelet:</dt>
                <dd><?= ($guest['bracelet_id'])?$guest['bracelet_id']:'<em>Vous avez bien réservé sa place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
            </dl>
        <?php endforeach ?>
    <?php } // endelse ?>
    <?php endif ?>

<?php } ?>
<?php if ($userResaCount == 1 && !empty($newResa)): ?>
    <hr>
<?php endif ?>
<?php if($userResaCount == 1){ ?>
    <h2 class="page-header">
        Votre réservation actuelle:
        <small><a href="<?= ($canWeEditOurReservation)?$editLink:'#' ?>" class="btn btn-primary" <?= ($canWeEditOurReservation && empty($newResa))?'':' title="Vous ne pouvez pas ou plus éditer vos réservations. On se retrouve au Gala." disabled="disabled"' ?>>Modifier mes achats</a></small>
    </h2>
    <h3>Vous:</h3>
    <dl class="dl-horizontal">
        <dt>Nom:</dt>
        <dd><?= $UserReservation['prenom'] . ' ' . $UserReservation['nom'] ?></dd>
        <dt>Mail:</dt>
        <dd><?= $UserReservation['email'] ?></dd>
        <dt>Promo:</dt>
        <dd><?= $UserReservation['promo'] ?></dd>
        <dt>Téléphone:</dt>
        <dd><?= $UserReservation['telephone'] ?></dd>
        <dt>Options:</dt>
        <dd><ul class="list-unstyled">
            <li><span class="label label-success">Soirée</span></li>
            <li><span class="label label-<?= ($UserReservation['buffet'])?'success':'default' ?>">Conférence</span></li>
            <li><span class="label label-<?= ($UserReservation['tickets_boisson'])?'success':'default' ?>"><?= $UserReservation['tickets_boisson'] ?> tickets boisson <small><em>(<?= $UserReservation['tickets_boisson']*0.9 ?>€)</em></small></span></li>
        </ul></dd>
        <dt>Prix payé:</dt>
        <dd><?= $UserReservation['price']; ?> <em><small>par <?= $UserReservation['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
        <dt>Plage horaire d'entrée :</dt>
        <dd><?= corriger_horaire($UserReservation['plage_horaire_entrees']) ?></dd>
        <dt>Numéro de bracelet:</dt>
        <dd><?= ($UserReservation['bracelet_id'])?$UserReservation['bracelet_id']:'<em>Vous avez bien réservé votre place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
    </dl>
    <h3>Vos invités:</h3>
    <?php if (count($UserGuests) == 0){ ?>
        <p><em>Vous n'avez pas encore d'invités.<?= ($canWeRegisterNewGuests)?" Il est cependant encore temps d'en rajouter !":"" ?></em></p>
    <?php } else { ?>
        <?php $j=0; foreach ($UserGuests as $key => $guest): if(empty($guest['id']) || $guest['id'] == -1) continue; $j++;?>
            <h4>Invité #<?= $key+1 ?></h4>
            <dl class="dl-horizontal">
                <dt>Nom:</dt>
                <dd><?= $guest['prenom'] . ' ' . $guest['nom'] ?></dd>
                <dt>Options:</dt>
                <dd><ul class="list-unstyled">
                    <li><span class="label label-success">Soirée</span></li>
                    <li><span class="label label-<?= ($guest['buffet'])?'success':'default' ?>">Conférence</span></li>
                    <li><span class="label label-<?= ($guest['tickets_boisson'])?'success':'default' ?>"><?= $guest['tickets_boisson'] ?> tickets boisson <small><em>(<?= $UserReservation['tickets_boisson'] ?>€)</em></small></span></li>
                </ul></dd>
                <dt>Prix payé:</dt>
                <dd><?= $guest['price']; ?> <em><small>par <?= $guest['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
                <dt>Plage horaire d'entrée :</dt>
                <dd><?= corriger_horaire($guest['plage_horaire_entrees']) ?></dd>
                <dt>Numéro de bracelet:</dt>
                <dd><?= ($guest['bracelet_id'])?$guest['bracelet_id']:'<em>Vous avez bien réservé sa place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
            </dl>
        <?php endforeach ?>
        <?php if ($j == 0){ ?>
            <p><em>Vous n'avez pas encore d'invités.<?= ($canWeRegisterNewGuests)?" Il est cependant encore temps d'en rajouter !":"" ?></em></p>
        <?php } ?>
    <?php } // endelse ?>
<?php } // fin réservation 

function corriger_horaire($fakehoraire){
        if ($fakehoraire == '21h-21h45' ){$vraihoraire = '21h-21h35';}
        if ($fakehoraire == '21h45-22h30') {$vraihoraire = '21h50-22h25';}
        if ($fakehoraire == '22h30-23h' ){$vraihoraire = '22h40-23h10';}
    return $vraihoraire;
    }?>