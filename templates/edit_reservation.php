<h1><?= ($UserId > 0) ? 'Edition de votre réservation' : 'Votre nouvelle réservation' ?></h1>
<?php if (!$canWeRegisterNewGuests): ?>
    <p class="alert alert-warning"><em>Attention, vous ne pouvez plus ajouter de nouveaux invités, par contre, vous pouvez encore modifier les informations ou les options de tout le monde (déjà invité).</em></p>
<?php endif ?>
    <p class="alert alert-warning"><em>Attention, vous ne pouvez pas prendre une offre inférieure à celle déjà prise: vous ne pouvez pas annuler les options une fois payées.</em></p>
<?php
    $dataPlageHoraireEntree = \PayIcam\Participant::$plage_horaire_entrees;
    $dataPlageHoraireEntreeShort = \PayIcam\Participant::$plage_horaire_entrees;
    unset($dataPlageHoraireEntreeShort['17h30-19h30'], $dataPlageHoraireEntreeShort['19h30-20h']);
    global $settings;
    $confSQL = $settings['settings']['confSQL'];
    try{
        $db_bracelet = new PDO('mysql:host='.$confSQL['sql_host'].';dbname='.$confSQL['sql_db'],$confSQL['sql_user'], $confSQL['sql_pass'], array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        }
    catch(PDOException $e) {
        die('<h1>Impossible de se connecter a la base de donnee</h1><p>'.$e->getMessage().'<p>');
        };

$is_set_bracelet=$db_bracelet->prepare("SELECT bracelet_id FROM guests WHERE id=?" );

if (isset($UserReservation['id'])){
    $is_set_bracelet->execute(array($UserReservation['id']));
    $result_bracelet=$is_set_bracelet->fetch();
    $num_bracelet=$result_bracelet["bracelet_id"];}
else
{
    $num_bracelet=null;
}

?>
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
            <div class="form-group ">
                <label class="col-sm-2 control-label">Options : </label>
                <div class="col-sm-10">
                    <div class="checkbox"><em>Vous participez de base à la soirée</em> <span class="label label-<?= (isset($UserReservation['price']) && $UserReservation['price']>=$prixPromo['prixIcam']['soiree'])?'success':'info' ?>">+<?= $prixPromo['prixIcam']['soiree'] ?>€</span></div>


                    <?= (isset($UserReservation['repas']) && $UserReservation['repas']) ? '<div class="checkbox">Vous participez déjà au repas <span class="label label-success">+'.$prixPromo['prixIcam']['repas'].'€</span></div>'
                            : (($prixPromo['prixIcam']['repas'] == null) ? ''
                                : $Form->input('resa[repas]','Participer au repas <span class="label label-default">+'.($prixPromo['prixIcam']['repas']).'€</span>', array('ng-model'=>'resa.repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>


                    <?= (isset($UserReservation['buffet']) && $UserReservation['buffet']) ? '<div class="checkbox">Vous participez déjà à la conférence <span class="label label-success">+'.$prixPromo['prixIcam']['buffet'].'€</span></div>'
                            : (($prixPromo['prixIcam']['buffet'] == null) ? ''
                                : $Form->input('resa[buffet]','Participer à la conférence <span class="label label-default">+'.($prixPromo['prixIcam']['buffet']).'€</span>', array('ng-model'=>'resa.buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>


                    <?= (isset($UserReservation['tickets_boisson']) && $UserReservation['tickets_boisson']) ?
                        '<div class="checkbox">Vous avez déjà réservé '.$UserReservation['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$UserReservation['tickets_boisson']*0.9.'€</span></div>' : ''; ?>


                    <?php 

                    if (isset($UserReservation['id'])){

                        if (!is_null($num_bracelet))
                        {?>
                            <?= (isset($UserReservation['plage_horaire_entrees']) && $UserReservation['plage_horaire_entrees']) ?
                            '<div class="checkbox">Vous avez deja réservé la plage horaire d\'entrée de '.corriger_horaire($dataPlageHoraireEntree)[$UserReservation['plage_horaire_entrees']].' et vous avez déja pris votre bracelet</div>' : ''; 
                        }
                        else
                        {?>
                        <?= (isset($UserReservation['plage_horaire_entrees']) && $UserReservation['plage_horaire_entrees']) ?
                            '<div class="checkbox">Vous avez deja réservé la plage horaire d\'entrée de '.corriger_horaire($dataPlageHoraireEntree)[$UserReservation['plage_horaire_entrees']].'</div>' : ''; ?>

                        <input type="button" href="<?php $lien_creneau ?>" class="btn btn-default" value="Changer d'horaire">

                        <?php }
                    }?>
                </div>
            </div>
            <?= (isset($UserReservation['tickets_boisson']) && $UserReservation['tickets_boisson']) ? '' :
                $Form->select('resa[tickets_boisson]', 'Tickets boisson : ', array('ng-model'=>'resa.tickets_boisson', 'data'=>array(0=>0 ,10=>'10 tickets 9€', 20=>'20 tickets 18€', 30=>'30 tickets 27€', 40=>'40 tickets 36€', 50=>'50 tickets 45€'))); // On veut pas afficher les tickets boissons si il en a déjà pris ! ?>

            <?php 

            if (!isset($UserReservation['id'])){?>

                <?= (isset($UserReservation['plage_horaire_entrees']) && $UserReservation['plage_horaire_entrees']) ? '' : 
                    $Form->select('resa[plage_horaire_entrees]', 'Plage horaire entrée : ', array('ng-model'=>'resa.plage_horaire_entrees', 'data'=>corriger_horaire($dataPlageHoraireEntreeShort)));
            }?> 
           

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
                                <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($UserGuests[$i]['prenom']) && $UserGuests[$i]['prenom'])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>


                                <?= (isset($UserGuests[$i]['repas']) && $UserGuests[$i]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">+'.$prixPromo['prixInvite']['repas'].'€</span></div>'
                                    : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                        : $Form->input('resa[invites]['.$i.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$i.'].repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>


                                <?= (isset($UserGuests[$i]['buffet']) && $UserGuests[$i]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">+'.$prixPromo['prixInvite']['buffet'].'€</span></div>'
                                    : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                        : $Form->input('resa[invites]['.$i.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$i.'].buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>


                                <?= (isset($UserGuests[$i]['tickets_boisson']) && $UserGuests[$i]['tickets_boisson']) ?
                                    '<div class="checkbox">Vous avez déjà réservé '.$UserGuests[$i]['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$UserGuests[$i]['tickets_boisson'].'€</span></div>' : ''; ?>


                                <?= (isset($UserGuests[$i]['plage_horaire_entrees']) && $UserGuests[$i]['plage_horaire_entrees']) ?
                                    '<div class="checkbox">Vous avez réservé la plage horaire d\'entrée de '.corriger_horaire($dataPlageHoraireEntree)[$UserGuests[$i]['plage_horaire_entrees']].'</div>' : ''; ?>
                            </div>
                        </div>
                        <?= (isset($UserGuests[$i]['tickets_boisson']) && $UserGuests[$i]['tickets_boisson']) ? '' :
                            $Form->select('resa[invites]['.$i.'][tickets_boisson]','Tickets boisson : ', array('ng-model' => 'resa.invites['.$i.'].tickets_boisson', 'data'=>array(0=>0,10=>'10 tickets 9€', 20=>'20 tickets 18€', 30=>'30 tickets 27€', 40=>'40 tickets 36€', 50=>'50 tickets 45€'))); ?>
                            
                        <?= (isset($UserGuests[$i]['plage_horaire_entrees']) && $UserGuests[$i]['plage_horaire_entrees']) ? '' :
                            $Form->select('resa[invites]['.$i.'][plage_horaire_entrees]', 'Plage horaire entrée : ', array('ng-model'=>'resa.invites['.$i.'].plage_horaire_entrees', 'data'=>corriger_horaire($dataPlageHoraireEntreeShort))); ?>
                    </div>
                </div>
                <?php $j = $i+1; if ($j+1 <= $nb){; ?>
                    <div id="invite<?= ($j+1); ?>" class="col-sm-6 invite">
                        <legend>Invité <?= ($j+1); ?></legend>
                        <div>
                            <?= $Form->input('resa[invites]['.$j.'][id]', 'hidden', array('ng-model' => 'resa.invites['.$j.'].id', )); ?>
                            <?= $Form->input('resa[invites]['.$j.'][nom]','Nom : ', array('ng-model' => 'resa.invites['.$j.'].nom', 'maxlength'=>'155')); ?>
                            <?= $Form->input('resa[invites]['.$j.'][prenom]','Prénom : ', array('ng-model' => 'resa.invites['.$j.'].prenom', 'maxlength'=>'155')); ?>
                            <div class="form-group ">
                                <label class="col-sm-2 control-label">Options : </label>
                                <div class="col-sm-10">
                                    <div class="checkbox"><em>Inscrit de base à la soirée</em> <span class="label label-<?= (isset($UserGuests[$j]['prenom']) && $UserGuests[$j]['prenom'])?'success':'info' ?>">+<?= $prixPromo['prixInvite']['soiree'] ?>€</span class="label label-default"></div>


                                    <?= (isset($UserGuests[$j]['repas']) && $UserGuests[$j]['repas']) ? '<div class="checkbox">Participe déjà au repas <span class="label label-success">+'.$prixPromo['prixInvite']['repas'].'€</span></div>'
                                        : (($prixPromo['prixInvite']['repas'] == null) ? ''
                                            : $Form->input('resa[invites]['.$j.'][repas]','Participe au repas <span class="label label-default">+'.($prixPromo['prixInvite']['repas']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$j.'].repas', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>


                                    <?= (isset($UserGuests[$j]['buffet']) && $UserGuests[$j]['buffet']) ? '<div class="checkbox">Participe déjà à la conférence <span class="label label-success">+'.$prixPromo['prixInvite']['buffet'].'€</span></div>'
                                        : (($prixPromo['prixInvite']['buffet'] == null) ? ''
                                            : $Form->input('resa[invites]['.$j.'][buffet]','Participe à la conférence <span class="label label-default">+'.($prixPromo['prixInvite']['buffet']).'€</span class="label label-default">', array('ng-model' => 'resa.invites['.$j.'].buffet', 'type'=>'checkbox', 'checkboxNoClassControl'=>1)) ); ?>

                                    <?= (isset($UserGuests[$j]['tickets_boisson']) && $UserGuests[$j]['tickets_boisson']) ?
                                        '<div class="checkbox">Vous avez déjà réservé '.$UserGuests[$j]['tickets_boisson'].' tickets boisson <span class="label label-success">+'.$UserGuests[$j]['tickets_boisson'].'€</span></div>' : ''; ?>


                                    <?= (isset($UserGuests[$j]['plage_horaire_entrees']) && $UserGuests[$j]['plage_horaire_entrees']) ?
                                        '<div class="checkbox">Vous avez réservé la plage horaire d\'entrée de '.corriger_horaire($dataPlageHoraireEntree[$UserGuests[$j]['plage_horaire_entrees']]).'</div>' : ''; ?>
                                </div>
                            </div>

                            <?= (isset($UserGuests[$j]['tickets_boisson']) && $UserGuests[$j]['tickets_boisson']) ? '' :
                                $Form->select('resa[invites]['.$j.'][tickets_boisson]','Tickets boisson : ', array('ng-model' => 'resa.invites['.$j.'].tickets_boisson', 'data'=>array(0=>0,10=>'10 tickets 9€', 20=>'20 tickets 18€', 30=>'30 tickets 27€', 40=>'40 tickets 36€', 50=>'50 tickets 45€'))); ?>

                            <?= (isset($UserGuests[$j]['plage_horaire_entrees']) && $UserGuests[$j]['plage_horaire_entrees']) ? '' :
                                $Form->select('resa[invites]['.$j.'][plage_horaire_entrees]', 'Plage horaire entrée : ', array('ng-model'=>'resa.invites['.$j.'].plage_horaire_entrees', 'data'=>corriger_horaire($dataPlageHoraireEntreeShort))); ?>
                        </div>
                    </div>
                <?php }?>
            </div>
        <?php } ?>
    </fieldset>
    <fieldset class="recap">
        <legend>Récapitulatif - prix à payer</legend>
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

<!-- {{resa}} -->
<!-- <pre><?php // var_dump($Auth->getUser()); ?></pre> -->
<!-- <pre><?php // var_dump($UserReservation); ?></pre> -->
<!-- <pre><?php // var_dump($UserGuests); ?></pre> -->
<!-- <pre><?php // var_dump($canWeRegisterNewGuests); ?></pre> -->
<!-- <pre><?php // var_dump($canWeEditOurReservation); ?></pre> -->
<!-- <pre><?php // var_dump($gingerUserCard); ?></pre> -->
<!-- <pre><?php // var_dump($prixPromo); ?></pre> -->
<!-- <pre><?php // var_dump($Form->data); ?></pre> -->
<!-- <pre><?php // var_dump($Form->data['resa']['invites']); ?></pre> -->
<!-- <pre><?php // var_dump($Form); ?></pre> -->
<?php
    function corriger_horaire($fakehoraire){
        $vraihoraire=array( "21h-21h45"=> "21h-21h35: 1er créneau", "21h45-22h30"=> "21h50-22h25: 2ème créneau", "22h30-23h"=> "22h40-23h10: 3ème créneau"); return $vraihoraire;}
?>

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

    // On s'assure que les données vont être lisibles par AngularJS et qu'il ne va pas faire n'importe quoi
    function setCheckBoxToTrueFalse(guest){
        guest.repas = Boolean(parseInt(guest.repas));
        guest.buffet = Boolean(parseInt(guest.buffet));
        guest.tickets_boisson = guest.tickets_boisson+"";
    }
    setCheckBoxToTrueFalse($scope.UserReservation);
    setCheckBoxToTrueFalse($scope.resa);
    for (var i = $scope.UserGuests.length - 1; i >= 0; i--) {
        setCheckBoxToTrueFalse($scope.UserGuests[i]);
    };
    for (var i = $scope.resa.invites.length - 1; i >= 0; i--) {
        setCheckBoxToTrueFalse($scope.resa.invites[i]);
    };


    function getOptions(user, typeUser){
        options = [];
        if (user.prenom == undefined && user.nom == undefined) return options;
        options.push({'nom':'Soirée', 'price':$scope.prixPromo[typeUser]['soiree']});
        if (user.repas == true || parseInt(user.repas))
            options.push({'nom':'Repas', 'price':$scope.prixPromo[typeUser]['repas']});
        if (user.buffet == true || parseInt(user.buffet))
            options.push({'nom':'Conférence', 'price':$scope.prixPromo[typeUser]['buffet']});
        if (parseInt(user.tickets_boisson))
            options.push({'nom':'Tickets boisson', 'price':parseInt(user.tickets_boisson)*0.9});
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
                }else if(!(guest.prenom == '' && guest.nom == '') && !(guest.prenom == undefined && guest.nom == undefined) && !(guest.prenom == '' && guest.nom == undefined) && !(guest.prenom == undefined && guest.nom == '')){ // On en avait pas encore, tout est à payer
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