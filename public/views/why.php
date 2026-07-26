<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<?php if (!$hasParam): ?>
<section id="why" >
<div class="container" >
      <div class="col-lg-12 text-center mb-5" align="center">
        <h2 class="fw-bold mb-4">Pourquoi phone2laptop existe ?</h2>
        <p class="lead text-muted">
          Parce que transférer un fichier de son téléphone à son ordinateur ne devrait <strong>pas être une corvée</strong>.
        </p>
      </div>

      <!-- Problème -->
	  <div class="row">
      <div class="col-md-6">
        <div class="p-4 h-100">
          <h4 class="text-danger mb-3">❌ Le problème aujourd’hui</h4>
          <ul class="list-unstyled">
            <li class="mb-3"><i class="bi bi-arrow-right-circle text-muted me-2"></i> <strong>WhatsApp Web</strong> met 10 à 30 secondes à charger… pour envoyer une photo à soi-même.</li>
            <li class="mb-3"><i class="bi bi-arrow-right-circle text-muted me-2"></i> <strong>Email</strong> : pièces jointes limitées, spam, perte de temps.</li>
            <li class="mb-3"><i class="bi bi-arrow-right-circle text-muted me-2"></i> <strong>Cloud (Google Drive, iCloud…)</strong> : compte requis, synchronisation lente, stockage limité.</li>
            <li class="mb-3"><i class="bi bi-arrow-right-circle text-muted me-2"></i> <strong>Câble USB</strong> : où est-ce qu’il est ? Et si on est sur Mac avec un adaptateur introuvable ?</li>
          </ul>
          <p class="mt-3">
            Résultat : on perd du temps, on s’énerve, et on oublie ce qu’on voulait transférer.
          </p>
        </div>
      </div>

      <!-- Solution -->
      <div class="col-md-6">
        <div class="p-4 h-100 border-start border-primary border-3 ps-4">
          <h4 class="text-success mb-3">✅ Notre solution</h4>
          <ul class="list-unstyled">
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>Instantané</strong> : scan → partage. Moins de 5 secondes.</li>
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>100 % gratuit</strong>, sans compte, sans pub, sans limite.</li>
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>Aucune donnée personnelle</strong> n’est collectée, stockée ou analysée.</li>
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>Chiffrement de bout en bout</strong> : la clé reste sur vos appareils, nous ne pouvons pas accéder à vos fichiers ni à vos notes.</li>
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>Auto-suppression</strong> après 30 minutes — vos fichiers disparaissent seuls.</li>
            <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong>Open-source</strong> : transparence totale, sécurité vérifiable.</li>
          </ul>
        </div>
      </div>
	</div>
</div>


</section>

<?php endif; ?>
