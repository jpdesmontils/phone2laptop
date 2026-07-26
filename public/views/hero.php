<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<?php if (!$hasParam): ?>
<!-- ============ LANDING (visible avant scan) ============ -->
<header class="hero text-center">
    <br/>
	<h1 class="display-4 fw-bold">Transférez vos fichiers<br/>entre téléphone et ordi<span class="text-primary"><br/> en 1 scan. Sans rien installer.</span></h1>
  <div class="container py-5">
    <p class="lead mb-4 mt-3">Oubliez les e-mails à vous-même, les messages WhatsApp ou les clés USB.<br/>
      <strong>phone2laptop</strong> crée un lien temporaire et sécurisé — <em>effacé automatiquement après 30 minutes</em>.</p>

    <div class="d-flex justify-content-center gap-4 flex-wrap align-items-center mb-4">
      <ul class="list-unstyled text-start ms-4">
        <li>📱 ↔ 💻 Transfert <strong>instantané dans les deux sens</strong></li>
        <li>🖼️ 📄  Fichiers, photos, vidéos, notes… <strong>tout est synchronisé en temps réel</strong></li>
        <li>🚫 Aucun compte, aucune pub, <strong>100 % anonyme</strong></li>
		<li>🔒 <strong>Auto-suppression</strong> après 30 min — vos données ne traînent pas</li>
      </ul>
    </div>

    <a href=<?php echo "?token=$token#transfer" ?> class="btn btn-primary btn-lg px-5 py-3 shadow">
      <i class="bi bi-qr-code-scan me-2"></i>Commencer maintenant
    </a>
    <p class="small text-muted mt-3">Votre session s’efface automatiquement — même si vous oubliez de tout supprimer.</p>
  </div>
  <div class="upload-counter py-3" aria-label="Nombre de fichiers échangés">
    <div class="container d-flex justify-content-center align-items-center gap-2">
      <i class="bi bi-arrow-left-right text-primary" aria-hidden="true"></i>
      <strong class="upload-counter-value fs-5"><?= number_format($uploadCount, 0, ',', '&nbsp;') ?></strong>
      <span class="text-muted">fichiers échangés</span>
    </div>
  </div>
</header>

<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-4">Comment ça marche ?</h2>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="step-icon mb-2">1</div>
        <h5>Ouvrez cette page sur votre ordi</h5>
        <p>Un QR code sécurisé apparaît instantanément.</p>
      </div>
      <div class="col-md-4">
        <div class="step-icon mb-2">2</div>
        <h5>Scannez-le avec votre téléphone</h5>
        <p>Votre mobile se connecte <strong>sans mot de passe</strong>.</p>
      </div>
      <div class="col-md-4">
        <div class="step-icon mb-2">3</div>
        <h5>Partagez librement</h5>
        <p>Glissez un fichier, tapez une note… <strong>ça apparaît en direct sur l’autre écran</strong>.</p>
      </div>
    </div>
  </div>
</section>




<?php endif; ?>
