<h1 class="page-header">Admin configuration</h1>

<form action="<?= $editLink ?>" method="post" class="form-horizontal" role="form">
    <fieldset class="recap">
        <legend>Configs</legend>
        <?php foreach ($configs as $key => $value): if (in_array($key, ['authentification', 'maintenance', 'websitename'])) continue;?>
            <div class="form-group">
                <label for="<?= $key ?>" class="col-sm-2 control-label"><?= $key ?></label>
                <div class="col-sm-10">
                    <?php if (in_array($key, ['authentification', 'inscriptions', 'maintenance', 'modifications_places'])) { ?>
                        <input name="<?= $key ?>" type="checkbox" id="<?= $key ?>" <?= ($value*1)?'checked="checked"':'' ?>">
                    <?php } else {?>
                        <input name="<?= $key ?>" type="text" class="form-control" id="<?= $key ?>" placeholder="<?= $key ?>" value="<?= $value ?>">
                    <?php } ?>
                </div>
            </div>
        <?php endforeach ?>
    </fieldset>
    <hr>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Sauvegarder</button>
    </div>
</form>

<h2 class="page-header">Export <?= $countParticipants['total'] ?> participants <small><?= $countParticipants['icam'] ?> icam, <?= $countParticipants['guest'] ?> invités</small></h2>
<a href="<?= $exportLink ?>" class="btn btn-info">Export participants</a>