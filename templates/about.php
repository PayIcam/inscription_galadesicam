<h1>A propos</h1>
<p>
    Cette application web va permettre à un étudiant / ingénieur Icam de réserver sa place et celle(s) de ses invités pour le Gala des Icam.<br>
    Pour cela, vous devez pouvoir vous authentifier avec votre mail Icam et avoir un compte sur PayIcam.
</p>
<p>
    <?php if ($Auth->isLogged()){ ?>
        <a href="<?= $casUrl ?>" class="btn btn-default">déconnexion</a>
    <?php } else { ?>
        <a href="<?= $casUrl ?>" class="btn btn-primary">Connectez-vous !</a>
    <?php } ?>
</p>