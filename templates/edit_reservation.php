<h1>Edition de votre réservation</h1>
<?php if (!$canWeRegisterNewGuests): ?>
    <p><em>Attention, vous ne pouvez plus ajouter de nouveaux invités, par contre, vous pouvez encore modifier les informations ou les options de tout le monde, déjà invité.</em></p>
<?php endif ?>

<?php var_dump($UserReservation); ?>
<?php var_dump($UserGuests); ?>
<?php var_dump($canWeRegisterNewGuests); ?>
