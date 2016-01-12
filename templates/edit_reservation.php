<h1><?= ($UserId > 0) ? 'Edition de votre réservation' : 'Votre nouvelle réservation' ?></h1>
<?php if (!$canWeRegisterNewGuests): ?>
    <p><em>Attention, vous ne pouvez plus ajouter de nouveaux invités, par contre, vous pouvez encore modifier les informations ou les options de tout le monde, déjà invité.</em></p>
<?php endif ?>

<form action="<?= $editLink ?>" method="post" class="form-horizontal" role="form">
    <fieldset>
        <legend>Vous :</legend>
        <div>
            <?= $Form->input('id', 'hidden', array('value'=>$UserId)); ?>
            <div class="form-group ">
                <label class="col-sm-2 control-label" for="inputnom">Nom : </label>
                <?php if (empty($UserReservation)){ $user = $Auth->getUser(); ?>
                    <div class="col-sm-10 checkbox"><?= $user['firstname'] . ' ' . $user['lastname'] ?></div>
                <?php }else{ ?>
                    <div class="col-sm-10 checkbox"><?= $UserReservation['prenom'] . ' ' . $UserReservation['nom'] ?></div>
                <?php } ?>
            </div>
            <?= $Form->input('telephone', 'Votre telephone : ', array('maxlength'=>'255', 'class'=>"col-xs-3")); ?>
            <div class="form-group ">
                <label class="col-sm-2 control-label">Options : </label>
                <div class="col-sm-10">
                    <div class="checkbox"><em>Vous participez de base à la soirée</em> <small>+<?= $prixPromo['prixIcam']['soiree'] ?>€</small></div>
                    <?= $Form->input('repas','Participer au repas <small>+'.($prixPromo['prixIcam']['repas']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                    <?= $Form->input('buffet','Participer à la conférence <small>+'.($prixPromo['prixIcam']['buffet']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                </div>
            </div>
            <?= $Form->select('tickets_boisson', 'Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
            <?= $Form->select('champagne', 'Bouteille de Champagne : ', array('data'=>array(0=>0,14.5=>'Une bouteille 14,5 €'))); ?>
        </div>
    </fieldset>
    <fieldset class="isIcam">
        <p>En tant que <?= $prixPromo['gameDePrix'] ?>, vous avez le droit à <?= $prixPromo['nbInvites']; ?> invités</p>
        <?php $nb = ((count($UserGuests)>$prixPromo['nbInvites'])?count($UserGuests):$prixPromo['nbInvites']);
         for ($i=0; $i < $nb; $i+=2) {?>
            <div class="row">
                <div id="invite<?= ($i+1); ?>" class="col-sm-6 invite">
                    <legend>Invité <?= ($i+1); ?></legend>
                    <div>
                        <?= $Form->input('invites['.$i.'][id]', 'hidden'); ?>
                        <?= $Form->input('invites['.$i.'][nom]','Nom : ', array('maxlength'=>'155')); ?>
                        <?= $Form->input('invites['.$i.'][prenom]','Prénom : ', array('maxlength'=>'155')); ?>
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">Options : </label>
                            <div class="col-sm-10">
                                <div class="checkbox"><em>Inscrit de base à la soirée</em> <small>+<?= $prixPromo['prixInvite']['soiree'] ?>€</small></div>
                                <?= $Form->input('invites['.$i.'][repas]','Participe au repas <small>+'.($prixPromo['prixInvite']['repas']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                                <?= $Form->input('invites['.$i.'][buffet]','Participe à la conférence <small>+'.($prixPromo['prixInvite']['buffet']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                            </div>
                        </div>
                        <?= $Form->select('invites['.$i.'][tickets_boisson]','Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
                        <?= $Form->select('champagne','Bouteille de Champagne : ', array('data'=>array(0=>0,14.5=>'Une bouteille 14,5€'))); ?>
                    </div>
                </div>
                <?php $j = $i+1; if ($j+1 < $nb){; ?>
                    <div id="invite<?= ($j+1); ?>" class="col-sm-6 invite">
                        <legend>Invité <?= ($j+1); ?></legend>
                        <div>
                            <?= $Form->input('invites['.$j.'][id]', 'hidden'); ?>
                            <?= $Form->input('invites['.$j.'][nom]','Nom : ', array('maxlength'=>'155')); ?>
                            <?= $Form->input('invites['.$j.'][prenom]','Prénom : ', array('maxlength'=>'155')); ?>
                            <div class="form-group ">
                                <label class="col-sm-2 control-label">Options : </label>
                                <div class="col-sm-10">
                                    <div class="checkbox"><em>Inscrit de base à la soirée</em> <small>+<?= $prixPromo['prixInvite']['soiree'] ?>€</small></div>
                                    <?= $Form->input('invites['.$j.'][repas]','Participe au repas <small>+'.($prixPromo['prixInvite']['repas']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                                    <?= $Form->input('invites['.$j.'][buffet]','Participe à la conférence <small>+'.($prixPromo['prixInvite']['buffet']).'€</small>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)); ?>
                                </div>
                            </div>
                            <?= $Form->select('invites['.$j.'][tickets_boisson]','Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
                            <?= $Form->select('champagne','Bouteille de Champagne : ', array('data'=>array(0=>0,14.5=>'Une bouteille 14,5€'))); ?>
                        </div>
                    </div>
                <?php }?>
            </div>
        <?php } ?>
    </fieldset>
    <hr>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save changes</button>
        &nbsp;
        <button class="btn" type="reset">Cancel</button>
    </div>
</form>

<hr>

<pre><?php var_dump($Auth->getUser()); ?></pre>
<pre><?php var_dump($UserReservation); ?></pre>
<pre><?php var_dump($UserGuests); ?></pre>
<pre><?php var_dump($canWeRegisterNewGuests); ?></pre>
<pre><?php var_dump($canWeEditOurReservation); ?></pre>
<pre><?php var_dump($gingerUserCard); ?></pre>
<pre><?php var_dump($prixPromo); ?></pre>
