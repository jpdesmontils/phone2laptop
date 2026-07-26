<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<section id="transfer" class="py-5">
  <div class="text-center">
    <div class="container mt-4">


	  <h1 class="fw-bold mb-3 text-primary">Scannez pour synchroniser un autre appareil</h1>
      <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm">
        <div id="session-qr" role="img" aria-label="QR Code – Ouvrir sur mobile"></div>
      </div>
    </div>
  </div>

  <div class="container mt-4">
    <?php if (!$hasParam): ?>

		<p class="small text-muted mt-2" align="center">Synchronisation en temps réel entre appareils.</p>
	<?php endif; ?>

    <div class="alert alert-primary text-center" role="status">
      <i class="bi bi-shield-lock-fill me-1"></i>
      Données chiffrées de bout en bout — <span id="expiry-label">le délai de suppression démarrera au premier fichier</span><strong id="expiry-countdown"></strong>.
    </div>

    <div class="row g-4" id="share">
      <!-- Fichiers -->
      <div class="col-md-6">
        <div class="border rounded-3 p-4 text-center shadow-sm h-100 d-flex flex-column">
          <h5 class="mb-3" >📁 Partager vos fichiers</h5>
          <form id="upload-form" class="flex-grow-1 d-flex flex-column justify-content-center">
            <label for="file-input" class="btn btn-outline-primary mb-2">Choisir des fichiers</label>
            <input id="file-input" class="d-none" type="file" multiple>
            <p class="small text-muted mt-2">Glissez-déposez aussi possible<br/>(max. 50 Mo/fichier)</p>

            <!-- Barre de progression -->
            <div id="upload-progress" class="mt-3" style="display:none;">
              <div class="progress" style="height:6px;">
                <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width:0%"></div>
              </div>
              <small class="text-muted mt-1 d-block">Envoi en cours…</small>
            </div>
          </form>
          <ul id="file-list" class="list-group mt-3 flex-grow-1 overflow-auto" style="max-height:250px;"></ul>
        </div>
      </div>

      <!-- Bloc-notes -->
      <div class="col-md-6">
        <div class="border rounded-3 p-4 shadow-sm h-100 d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="mb-0">📝 Partager vos notes</h5>
            <button id="btn-copy" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
              <i class="bi bi-clipboard"></i> Copier
            </button>
          </div>
          <textarea id="shared-text" class="form-control flex-grow-1" placeholder="Écrivez un message…"></textarea>

        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <button id="btn-delete" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash"></i> Tout supprimer
      </button>
    </div>
  </div>

     <div class="mt-5" align="center">
      <div class="col-lg-8 text-center">
        <h5 class="mb-3">❤️ Vous aimez phone2laptop ?</h5>
        <p class="text-muted mb-4">
          Soutenez son développement ! Vos dons nous permettent de maintenir l’infrastructure, améliorer la sécurité, et garder le service gratuit.
        </p>
        <!-- Bouton de don via Stripe (tu remplaceras par ton vrai lien) -->
        <a href="https://donate.stripe.com/4gM5kE2z69M21qFbDAew807" target="_blank" class="btn btn-success btn-lg px-5">
          <i class="bi bi-heart me-2"></i> Faire un don
        </a>
        <p class="small text-muted mt-2">
          Tous les dons sont libres, sans contrepartie. Merci de votre confiance !
        </p>
      </div>
    </div>

  </div>
</section>




<script>
window.P2L = <?= json_encode([
  'token' => $token,
  'apiBase' => $baseUrl . '/api/',
  'baseUrl' => $baseUrl,
  'shareUrl' => $link,
  'expiresAt' => $expiresAt === null ? null : $expiresAt * 1000,
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= htmlspecialchars($baseUrl) ?>/js/sync.js"></script>
