<h1><?= ($UserId > 0) ? 'Edition de votre réservation' : 'Votre nouvelle réservation' ?></h1>
<?php if (!$canWeRegisterNewGuests): ?>
    <p class="alert alert-warning"><em>Attention, vous ne pouvez plus ajouter de nouveaux invités, par contre, vous pouvez encore modifier les informations ou les options de tout le monde (déjà invité).</em></p>
<?php endif ?>
    <p class="alert alert-warning"><em>Attention, vous ne pouvez pas prendre une offre inférieure à celle déjà prise: vous ne pouvez pas annuler les options une fois payées.</em></p>
<div ng-app="editGuestApp">
<form action="<?= $editLink ?>" method="post" class="form-horizontal" role="form" ng-controller="EditGuestFormController">
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
                    <div class="checkbox"><em>Vous participez de base à la soirée</em> <span class="label label-<?= (isset($Form->data['price']) && $Form->data['price']>=$prixPromo['prixIcam']['soiree'])?'success':'info' ?>">+<?= $prixPromo['prixIcam']['soiree'] ?>€</span></div>
                    <?= (isset($Form->data['repas']) && $Form->data['repas']) ? '<div class="checkbox">Vous participez déjà au repas <span class="label label-success">(+'.$prixPromo['prixIcam']['repas'].'€)</span></div>'
                            : (($prixPromo['prixIcam']['repas'] == null) ? ''
                                : $Form->input('repas','Participer au repas <span class="label label-default">+'.($prixPromo['prixIcam']['repas']).'€</span>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                    <?= (isset($Form->data['buffet']) && $Form->data['buffet']) ? '<div class="checkbox">Vous participez déjà à la conférence <span class="label label-success">(+'.$prixPromo['prixIcam']['buffet'].'€)</span></div>'
                            : (($prixPromo['prixIcam']['buffet'] == null) ? ''
                                : $Form->input('buffet','Participer à la conférence <span class="label label-default">+'.($prixPromo['prixIcam']['buffet']).'€</span>', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                </div>
            </div>
            <?= $Form->select('tickets_boisson', 'Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
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
                                <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($Form->data['invites'][$i]) && $Form->data['invites'][$i])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>
                                <?= (isset($Form->data['invites'][$i]['repas']) && $Form->data['invites'][$i]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">(+'.$prixPromo['prixInvite']['repas'].'€)</span></div>'
                                    : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                        : $Form->input('invites['.$i.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                <?= (isset($Form->data['invites'][$i]['buffet']) && $Form->data['invites'][$i]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">(+'.$prixPromo['prixInvite']['buffet'].'€)</span></div>'
                                    : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                        : $Form->input('invites['.$i.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                            </div>
                        </div>
                        <?= $Form->select('invites['.$i.'][tickets_boisson]','Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
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
                                    <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($Form->data['invites'][$j]) && $Form->data['invites'][$j])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>
                                    <?= (isset($Form->data['invites'][$j]['repas']) && $Form->data['invites'][$j]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">(+'.$prixPromo['prixInvite']['repas'].'€)</span></div>'
                                        : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                            : $Form->input('invites['.$j.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                    <?= (isset($Form->data['invites'][$j]['buffet']) && $Form->data['invites'][$j]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">(+'.$prixPromo['prixInvite']['buffet'].'€)</span></div>'
                                        : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                            : $Form->input('invites['.$j.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                </div>
                            </div>
                            <?= $Form->select('invites['.$j.'][tickets_boisson]','Tickets boisson : ', array('data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
                        </div>
                    </div>
                <?php }?>
            </div>
        <?php } ?>
    </fieldset>
    <fieldset class="recap">
        <legend>Récaputilatif - prix à payer</legend>
        <h3>Déjà payé <small>{{dejaPaye}}€</small></h3>
        <ul>
            <li ng-repeat="guest in guestsDejaPaye">{{guest.nom}}: <span style="margin-right:5px;" class="label label-success" ng-repeat="option in guest.options">{{option.nom}} : {{option.price}}€</span></li>
        </ul>
        <h3>Nouvelles options <small>{{newPrice}}€</small></h3>
        <ul>
            <li ng-repeat="guest in guestsDoitEncorePayer">{{guest.nom}}: <span style="margin-right:5px;" class="label label-success" ng-repeat="option in guest.options">{{option.nom}} : {{option.price}}€</span></li>
        </ul>
        <p ng-hide="{{newPrice}}">Vous n'avez rien pour le moment à payer, les modifications portant sur le nom des invités sont sans coûts.</p>
    </fieldset>
    <hr>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Valider</button>
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

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
<script>
angular.module('editGuestApp', [])
  .controller('EditGuestFormController', function($scope) {
    $scope.newPrice = 0;
    $scope.dejaPaye = 0;

    $scope.UserReservation = <?= json_encode($UserReservation) ?>;
    $scope.UserGuests = <?= json_encode($UserGuests) ?>;
    $scope.UserNewReservation = <?= json_encode($UserReservation) ?>;
    $scope.prixPromo = <?= json_encode($prixPromo) ?>;

    $scope.guestsDejaPaye = [];
    $scope.guestsDoitEncorePayer = [];

    function getOptions(user, typeUser){
        options = [];
        options.push({'nom':'Soirée', 'price':$scope.prixPromo[typeUser]['soiree']});
        $scope.dejaPaye += $scope.prixPromo[typeUser]['soiree'];
        console.log(parseInt(user.repas), 'parseInt(user.repas)', user.repas); 
        if (parseInt(user.repas)){
            $scope.dejaPaye += $scope.prixPromo[typeUser]['repas'];
            options.push({'nom':'Repas', 'price':$scope.prixPromo[typeUser]['repas']});
        }
        console.log(parseInt(user.buffet), 'parseInt(user.buffet)', user.buffet); 
        if (parseInt(user.buffet)){
            $scope.dejaPaye += $scope.prixPromo[typeUser]['buffet'];
            options.push({'nom':'Conférence', 'price':$scope.prixPromo[typeUser]['buffet']});
        }
        return options;
    }

    // Préparation données Réservation déjà effectuée par l'utilisateur
    if ($scope.UserReservation.prenom != undefined) {
        $scope.guestsDejaPaye.push({'nom':'Vous', 'options':getOptions($scope.UserReservation, 'prixIcam')});
    };

    angular.forEach($scope.UserGuests, function(value, key) {
        this.push({'nom':value['prenom']+' '+value['nom'], 'options':getOptions(value, 'prixInvite')});
    }, $scope.guestsDejaPaye);

    // Préparation données Réservation déjà effectuée par l'utilisateur
    if ($scope.UserNewReservation.prenom != undefined) {
        $scope.guestsDoitEncorePayer.push({'nom':'Vous', 'options':getOptions($scope.UserNewReservation, 'prixIcam')});
    };

    angular.forEach($scope.UserNewReservation.invites, function(value, key) {
        this.push({'nom':value['prenom']+' '+value['nom'], 'options':getOptions(value, 'prixInvite')});
    }, $scope.guestsDoitEncorePayer);

  });
</script>
</div>