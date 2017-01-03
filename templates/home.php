<h2 class="page-header">Précisions pour le soir même:</h2>
<div class="row">
    <div class="col-sm-6">
        <h3>Horaires:</h3>
        <ul>
            <?php foreach (\PayIcam\Participant::$plage_horaire_entrees as $key => $plage): ?>
                <li><?= $plage ?></li>
            <?php endforeach ?>
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
    <p><a href="<?= $editLink ?>" class="btn btn-primary">S'inscrire au Gala</a></p>
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
            <li><span class="label label-<?= (!empty($UserReservation['repas']))?'success':(($newResa->icamData['repas'])?'info':'default') ?>">Repas</span></li>
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
                    <li><span class="label label-<?= (!empty($oldGuest['repas']))?'success':(($guest['repas'])?'info':'default') ?>">Repas</span></li>
                    <li><span class="label label-<?= (!empty($oldGuest['buffet']))?'success':(($guest['buffet'])?'info':'default') ?>">Conférence</span></li>
                    <li><span class="label label-<?= (!empty($oldGuest['tickets_boisson']))?'success':(($guest['tickets_boisson'])?'info':'default') ?>"><?= $guest['tickets_boisson'] ?> tickets boisson</span></li>
                </ul></dd>
                <dt>Prix payé:</dt>
                <dd><?= $guest['price']; ?> <em><small>par <?= $guest['paiement'] ?> le <?= substr($newResa->date_option, 0, 10) ?></small></em></dd>
                <dt>Plage horaire d'entrée :</dt>
                <dd><?= $guest['plage_horaire_entrees'] ?></dd>
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
            <li><span class="label label-<?= ($UserReservation['repas'])?'success':'default' ?>">Repas</span></li>
            <li><span class="label label-<?= ($UserReservation['buffet'])?'success':'default' ?>">Conférence</span></li>
            <li><span class="label label-<?= ($UserReservation['tickets_boisson'])?'success':'default' ?>"><?= $UserReservation['tickets_boisson'] ?> Tickets boisson</span></li>
        </ul></dd>
        <dt>Prix payé:</dt>
        <dd><?= $UserReservation['price']; ?> <em><small>par <?= $UserReservation['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
        <dt>Plage horaire d'entrée :</dt>
        <dd><?= $UserReservation['plage_horaire_entrees'] ?></dd>
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
                    <li><span class="label label-<?= ($guest['repas'])?'success':'default' ?>">Repas</span></li>
                    <li><span class="label label-<?= ($guest['buffet'])?'success':'default' ?>">Conférence</span></li>
                    <li><span class="label label-<?= ($guest['tickets_boisson'])?'success':'default' ?>"><?= $guest['tickets_boisson'] ?> tickets boisson</span></li>
                </ul></dd>
                <dt>Prix payé:</dt>
                <dd><?= $guest['price']; ?> <em><small>par <?= $guest['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
                <dt>Plage horaire d'entrée :</dt>
                <dd><?= $guest['plage_horaire_entrees'] ?></dd>
                <dt>Numéro de bracelet:</dt>
                <dd><?= ($guest['bracelet_id'])?$guest['bracelet_id']:'<em>Vous avez bien réservé sa place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
            </dl>
        <?php endforeach ?>
        <?php if ($j == 0){ ?>
            <p><em>Vous n'avez pas encore d'invités.<?= ($canWeRegisterNewGuests)?" Il est cependant encore temps d'en rajouter !":"" ?></em></p>
        <?php } ?>
    <?php } // endelse ?>
<?php } // fin réservation ?>