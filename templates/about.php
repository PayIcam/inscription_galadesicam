<h1>A propos</h1>
<p>
    Cette application web va permettre à un étudiant Icam de réserver sa place et celle(s) de ses invités pour le Spring Festival.<br>
    Pour cela, vous devez pouvoir vous authentifier avec votre mail Icam et avoir un compte sur PayIcam. <br>
    En cas de problème avec votre réservation, contactez nous! <a href="mailto:spring.icam@gmail.com"> spring.icam@gmail.com</a>
<p>
    <?php if ($Auth->isLogged()){ ?>
        <a href="<?= $deconnexionUrl ?>" class="btn btn-default">déconnexion</a>
    <?php } else { ?>
        <a href="<?= $casUrl ?>" class="btn btn-primary">Connectez-vous !</a>
    <?php } ?>
</p>