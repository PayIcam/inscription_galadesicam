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
            <?= $Form->input('resa[id]', 'hidden', array('ng-model'=>'resa.id', 'value'=>$UserId)); ?>
            <div class="form-group ">
                <label class="col-sm-2 control-label" for="inputnom">Nom : </label>
                <?php if (empty($UserReservation)){ $user = $Auth->getUser(); ?>
                    <div class="col-sm-10 checkbox"><?= $user['firstname'] . ' ' . $user['lastname'] ?></div>
                <?php }else{ ?>
                    <div class="col-sm-10 checkbox"><?= $UserReservation['prenom'] . ' ' . $UserReservation['nom'] ?></div>
                <?php } ?>
            </div>
            <?= $Form->input('resa[telephone]', 'Votre telephone : ', array('ng-model'=>'resa.telephone', 'maxlength'=>'255', 'class'=>"col-xs-3")); ?>
            {{resa}}
            <div class="form-group ">
                <label class="col-sm-2 control-label">Options : </label>
                <div class="col-sm-10">
                    <div class="checkbox"><em>Vous participez de base à la soirée</em> <span class="label label-<?= (isset($Form->data['resa']['price']) && $Form->data['resa']['price']>=$prixPromo['prixIcam']['soiree'])?'success':'info' ?>">+<?= $prixPromo['prixIcam']['soiree'] ?>€</span></div>
                    <?= (isset($Form->data['resa']['repas']) && $Form->data['resa']['repas']) ? '<div class="checkbox">Vous participez déjà au repas <span class="label label-success">+'.$prixPromo['prixIcam']['repas'].'€</span></div>'
                            : (($prixPromo['prixIcam']['repas'] == null) ? ''
                                : $Form->input('resa[repas]','Participer au repas <span class="label label-default">+'.($prixPromo['prixIcam']['repas']).'€</span>', array('ng-model'=>'resa.repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                    <?= (isset($Form->data['resa']['buffet']) && $Form->data['resa']['buffet']) ? '<div class="checkbox">Vous participez déjà à la conférence <span class="label label-success">+'.$prixPromo['prixIcam']['buffet'].'€</span></div>'
                            : (($prixPromo['prixIcam']['buffet'] == null) ? ''
                                : $Form->input('resa[buffet]','Participer à la conférence <span class="label label-default">+'.($prixPromo['prixIcam']['buffet']).'€</span>', array('ng-model'=>'resa.buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                    <?= (isset($Form->data['resa']['tickets_boisson']) && $Form->data['resa']['tickets_boisson']) ?
                        '<div class="checkbox">Vous avez déjà réservé '.$Form->data['resa']['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$Form->data['resa']['tickets_boisson'].'€</span></div>' : ''; ?>
                </div>
            </div>
            <?= (isset($Form->data['resa']['tickets_boisson']) && $Form->data['resa']['tickets_boisson']) ? '' :
                $Form->select('resa[tickets_boisson]', 'Tickets boisson : ', array('ng-model'=>'resa.tickets_boisson', 'data'=>array(0=>0,10=>'10 tickets 10€'))); // On veut pas afficher les tickets boissons si il en a déjà pris ! ?>
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
                        <?= $Form->input('resa[invites]['.$i.'][id]', 'hidden', array('ng-model' => 'resa.invites['.$i.'].id', )); ?>
                        <?= $Form->input('resa[invites]['.$i.'][nom]','Nom : ', array('ng-model' => 'resa.invites['.$i.'].nom', 'maxlength'=>'155')); ?>
                        <?= $Form->input('resa[invites]['.$i.'][prenom]','Prénom : ', array('ng-model' => 'resa.invites['.$i.'].prenom', 'maxlength'=>'155')); ?>
                        <div class="form-group ">
                            <label class="col-sm-2 control-label">Options : </label>
                            <div class="col-sm-10">
                                <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($Form->data['resa']['invites'][$i]) && $Form->data['resa']['invites'][$i])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>
                                <?= (isset($Form->data['resa']['invites'][$i]['repas']) && $Form->data['resa']['invites'][$i]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">+'.$prixPromo['prixInvite']['repas'].'€</span></div>'
                                    : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                        : $Form->input('resa[invites]['.$i.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$i.'].repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                <?= (isset($Form->data['resa']['invites'][$i]['buffet']) && $Form->data['resa']['invites'][$i]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">+'.$prixPromo['prixInvite']['buffet'].'€</span></div>'
                                    : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                        : $Form->input('resa[invites]['.$i.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$i.'].buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                <?= (isset($Form->data['resa']['invites'][$i]['tickets_boisson']) && $Form->data['resa']['invites'][$i]['tickets_boisson']) ?
                                    '<div class="checkbox">Vous avez déjà réservé '.$Form->data['resa']['invites'][$i]['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$Form->data['resa']['invites'][$i]['tickets_boisson'].'€</span></div>' : ''; ?>
                            </div>
                        </div>
                        <?= (isset($Form->data['resa']['invites'][$i]['tickets_boisson']) && $Form->data['resa']['invites'][$i]['tickets_boisson']) ? '' :
                            $Form->select('resa[invites]['.$i.'][tickets_boisson]','Tickets boisson : ', array('ng-model' => 'resa.invites['.$i.'].tickets_boisson', 'data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
                    </div>
                </div>
                <?php $j = $i+1; if ($j+1 < $nb){; ?>
                    <div id="invite<?= ($j+1); ?>" class="col-sm-6 invite">
                        <legend>Invité <?= ($j+1); ?></legend>
                        <div>
                            <?= $Form->input('resa[invites]['.$j.'][id]', 'hidden', array('ng-model' => 'resa.invites['.$j.'].id', )); ?>
                            <?= $Form->input('resa[invites]['.$j.'][nom]','Nom : ', array('ng-model' => 'resa.invites['.$j.'].nom', 'maxlength'=>'155')); ?>
                            <?= $Form->input('resa[invites]['.$j.'][prenom]','Prénom : ', array('ng-model' => 'resa.invites['.$j.'].prenom', 'maxlength'=>'155')); ?>
                            <div class="form-group ">
                                <label class="col-sm-2 control-label">Options : </label>
                                <div class="col-sm-10">
                                    <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($Form->data['resa']['invites'][$j]) && $Form->data['resa']['invites'][$j])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>
                                    <?= (isset($Form->data['resa']['invites'][$j]['repas']) && $Form->data['resa']['invites'][$j]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">+'.$prixPromo['prixInvite']['repas'].'€</span></div>'
                                        : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                            : $Form->input('resa[invites]['.$j.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$j.'].repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                    <?= (isset($Form->data['resa']['invites'][$j]['buffet']) && $Form->data['resa']['invites'][$j]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">+'.$prixPromo['prixInvite']['buffet'].'€</span></div>'
                                        : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                            : $Form->input('resa[invites]['.$j.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$j.'].buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>
                                    <?= (isset($Form->data['resa']['invites'][$j]['tickets_boisson']) && $Form->data['resa']['invites'][$j]['tickets_boisson']) ? 
                                        '<div class="checkbox">Vous avez déjà réservé '.$Form->data['resa']['invites'][$j]['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$Form->data['resa']['invites'][$j]['tickets_boisson'].'€</span></div>' : ''; ?>
                                </div>
                            </div>
                            <?= (isset($Form->data['resa']['invites'][$j]['tickets_boisson']) && $Form->data['resa']['invites'][$j]['tickets_boisson']) ? '' :
                                $Form->select('resa[invites]['.$j.'][tickets_boisson]','Tickets boisson : ', array('ng-model' => 'resa.invites['.$j.'].tickets_boisson', 'data'=>array(0=>0,10=>'10 tickets 10€'))); ?>
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
            <li ng-repeat="guest in guestsDoitEncorePayer">{{guest.nom}}: <span style="margin-right:5px;" class="label label-info" ng-repeat="option in guest.options">{{option.nom}} : {{option.price}}€</span></li>
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
<pre><?php var_dump($Form->data); ?></pre>
<pre><?php var_dump($Form->data['resa']['invites']); ?></pre>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
<script>
angular.module('editGuestApp', [])
  .controller('EditGuestFormController', function($scope) {
    $scope.newPrice = 0;
    $scope.dejaPaye = 0;

    $scope.UserReservation = <?= json_encode($UserReservation) ?>;
    $scope.UserGuests = <?= json_encode($UserGuests) ?>;
    $scope.resa = <?= json_encode($Form->data['resa']) ?>;
    $scope.prixPromo = <?= json_encode($prixPromo) ?>;

    $scope.guestsDejaPaye = [];
    $scope.guestsDoitEncorePayer = [];

    function getOptions(user, typeUser){
        options = [];
        options.push({'nom':'Soirée', 'price':$scope.prixPromo[typeUser]['soiree']});
        if (user.repas == true || parseInt(user.repas))
            options.push({'nom':'Repas', 'price':$scope.prixPromo[typeUser]['repas']});
        if (user.buffet == true || parseInt(user.buffet))
            options.push({'nom':'Conférence', 'price':$scope.prixPromo[typeUser]['buffet']});
        if (parseInt(user.tickets_boisson))
            options.push({'nom':'Tickets boisson', 'price':parseInt(user.tickets_boisson)});
        return options;
    }

    function getOptionsPrice(options) {
        sumPrice = 0;
        for (var i = 0; i < options.length; i++) {
            sumPrice += options[i].price;
        };
        return sumPrice;
    }

    // Préparation données Réservation déjà effectuée par l'utilisateur
    if ($scope.UserReservation.prenom != undefined) {
        options = getOptions($scope.UserReservation, 'prixIcam');
        if (options.length > 0 && $scope.UserReservation.price >= $scope.prixPromo['prixIcam']['soiree']){
            $scope.dejaPaye += getOptionsPrice(options);
            $scope.guestsDejaPaye.push({'nom':'Vous', 'options':options});
        }
    };

    if ($scope.UserGuests != undefined){
        for (var i = 0; i < $scope.UserGuests.length; i++) {
            guest = $scope.UserGuests[i];
            options = getOptions(guest, 'prixInvite');
            if (options.length > 0){
                $scope.dejaPaye += getOptionsPrice(options);
                $scope.guestsDejaPaye.push({'nom':guest['prenom']+' '+guest['nom'], 'options': options});
            }
        };
    };

    function getNonCommonOptions(curOptions, newOptions) {
        options = [];
        for (var i = 0; i < newOptions.length; i++) {
            var exists = false;
            for (var j = 0; j < curOptions.length; j++) {
                if (newOptions[i].nom == curOptions[j].nom){exists = true;break;};
            };
            if (!exists) {options.push(newOptions[i])};
        };
        return options;
    }

    $scope.checkNewOptions = function(){
        $scope.newPrice = 0;
        $scope.guestsDoitEncorePayer = [];

        // Préparation données Réservation déjà effectuée par l'utilisateur
        if ($scope.resa.prenom != undefined) {
            options = getOptions($scope.resa, 'prixIcam');
            if ($scope.UserReservation.prenom != undefined && $scope.guestsDejaPaye[0] != undefined) {
                optionsCurRes = $scope.guestsDejaPaye[0].options;
                options = getNonCommonOptions(optionsCurRes, options);
                if (options.length > 0) {
                    $scope.newPrice += getOptionsPrice(options);
                    $scope.guestsDoitEncorePayer.push({'nom':'Vous', 'options': options});
                };
            }else{ // On en avait pas encore, tout est à payer
                $scope.newPrice += getOptionsPrice(options);
                $scope.guestsDoitEncorePayer.push({'nom':'Vous', 'options': options});
            };
        };

        if ($scope.resa.invites != undefined){
            for (var i = 0; i < $scope.resa.invites.length; i++) {
                guest = $scope.resa.invites[i];
                options = getOptions(guest, 'prixInvite')
                if ($scope.UserGuests[i] != undefined && $scope.UserGuests[i].prenom != undefined && $scope.UserGuests[i].nom != undefined ) {
                    optionsCurRes = $scope.guestsDejaPaye[i+1].options;
                    options = getNonCommonOptions(optionsCurRes, options);
                    if (options.length > 0) {
                        $scope.newPrice += getOptionsPrice(options);
                        $scope.guestsDoitEncorePayer.push({'nom':guest['prenom']+' '+guest['nom'], 'options': options});
                    };
                }else if(!(guest.prenom == '' && guest.nom == '')){ // On en avait pas encore, tout est à payer
                    $scope.newPrice += getOptionsPrice(options);
                    $scope.guestsDoitEncorePayer.push({'nom':guest['prenom']+' '+guest['nom'], 'options': options});
                };
            };
        };
    }

    $scope.checkNewOptions();

    $scope.$watch('resa', function(newValue, oldValue) {
        $scope.checkNewOptions();
    }, true);

  });
</script>
</div>