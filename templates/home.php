<h1>Inscription au Gala des Icam</h1>

<?php if (count($UserReservation) == 0 && $canWeRegisterNewGuests) { ?>
    <p>
        Vous n'avez pas encore de réservation,<br>
        mais vous pouvez encore vous enregistrer:
    </p>
    <p><a href="<?= $editLink ?>" class="btn btn-primary">S'inscrire au Gala</a></p>
<?php }elseif (count($UserReservation) == 0) { ?>
    <p>
        Vous n'avez pas eu le temps de prendre votre réservation ...<br>
        et malheureusement les ventes de places sont maintenant finies ...<br>
        On se dit à l'année prochaine ?
    </p>
<?php }elseif(count($UserReservation) > 1){ ?>
    <p>Nous avons plusieurs réservations enregistrées à votre email...<br>
        <a href="mailto:<?= $emailContactGala ?>">Contactez nous</a> svp !</p>
<?php }elseif(count($UserReservation) == 1){ $UserReservation = current($UserReservation); ?>
    <h2>
        Votre réservation:
        <small><a href="<?= ($canWeEditOurReservation)?$editLink:'#' ?>" class="btn btn-primary" <?= ($canWeEditOurReservation)?'':' title="Vous ne pouvez plus éditer vos réservations. On se retrouve au Gala." disabled="disabled"' ?>>éditer sa place</a></small>
    </h2>
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
            <li><span class="label label-<?= ($UserReservation['champagne'])?'success':'default' ?>"><?= $UserReservation['champagne'] ?> bouteille de champagne</span></li>
        </ul></dd>
        <dt>Prix payé:</dt>
        <dd><?= $UserReservation['price']; ?> <em><small>par <?= $UserReservation['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
        <dt>Numéro de bracelet:</dt>
        <dd><?= ($UserReservation['bracelet_id'])?$UserReservation['bracelet_id']:'<em>Vous avez bien réservé votre place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
    </dl>
    <h2>Vos invités:</h2>
    <?php if (count($UserGuests) == 0){ ?>
        <p><em>Vous n'avez pas encore d'invités. Il est cependant encore temps d'en rajouter !</em></p>
    <?php } else { ?>
        <?php foreach ($UserGuests as $key => $guest): ?>
            <h3>Invité #<?= $key+1 ?></h3>
            <dl class="dl-horizontal">
                <dt>Nom:</dt>
                <dd><?= $guest['prenom'] . ' ' . $guest['nom'] ?></dd>
                <dt>Options:</dt>
                <dd><ul class="list-unstyled">
                    <li><span class="label label-success">Soirée</span></li>
                    <li><span class="label label-<?= ($guest['repas'])?'success':'default' ?>">Repas</span></li>
                    <li><span class="label label-<?= ($guest['buffet'])?'success':'default' ?>">Conférence</span></li>
                    <li><span class="label label-<?= ($guest['tickets_boisson'])?'success':'default' ?>"><?= $guest['tickets_boisson'] ?> tickets boisson</span></li>
                    <li><span class="label label-<?= ($guest['champagne'])?'success':'default' ?>"><?= $guest['champagne'] ?> bouteille de champagne</span></li>
                </ul></dd>
                <dt>Prix payé:</dt>
                <dd><?= $guest['price']; ?> <em><small>par <?= $guest['paiement'] ?> le <?= substr($UserReservation['inscription'], 0, 10) ?></small></em></dd>
                <dt>Numéro de bracelet:</dt>
                <dd><?= ($guest['bracelet_id'])?$guest['bracelet_id']:'<em>Vous avez bien réservé sa place. Cependant, vous devez récupérer votre bracelet.</em>'; ?></dd>
            </dl>
        <?php endforeach ?>
    <?php } // endelse ?>
    <pre><?php var_dump($UserReservation); ?></pre>
    <pre><?php var_dump($UserGuests); ?></pre>
<?php } ?>