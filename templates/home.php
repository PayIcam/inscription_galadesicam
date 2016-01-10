<h1>Inscription au Gala des Icam</h1>
<?php $user = $Auth->getUser() ?>
<p>Bonjour <strong><?= $user['firstname'] ?></strong>, ne bouge pas on va regarder si tu as déjà réserver ta place !</p>

<hr>

<?php 

if (count($UserReservation) == 0) {
    echo "Vous n'avez pas encore de réservation.";
}elseif(count($UserReservation) > 1){
    echo "Oulala rien ne va, on a plusieurs réservations à votre nom O.o ... Contactez nous !";
}elseif(count($UserReservation) == 1){
    echo "youpi, on a bien une réservation à votre nom !";
    var_dump(current($UserReservation));
}

?>