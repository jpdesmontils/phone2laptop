<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container">
    <div class="row">
      <div class="col-md-6 mb-4 mb-md-0">
        <h5 class="mb-3">À propos de Solenis Studio</h5>
        <p class="opacity-75">
          Nous sommes une agence de conseil et de développement logiciel spécialisée dans la création de MVP (Minimum Viable Product). 
          Nous nous engageons à transformer vos idées en produits concrets en moins de 3 mois grâce à une méthodologie innovante 
          et l’utilisation de composants logiciels réutilisables et open-source.
        </p>
      </div>
      <div class="col-md-3 mb-4 mb-md-0">
        <h5 class="mb-3">Liens utiles</h5>
        <ul class="list-unstyled opacity-75">
          <li><a href="#why" class="text-white text-decoration-none">Pourquoi phone2laptop</a></li>
          <li><a href="#about" class="text-white text-decoration-none">Fonctionnalités</a></li>
          <li><a href="#contribute" class="text-white text-decoration-none">Contribuer</a></li>
          <li><a href="#blog" class="text-white text-decoration-none">Blog</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <h5 class="mb-3">phone2laptop</h5>
        <p class="opacity-75 small">
          Outil open-source • Sécurisé • Éphémère<br>
          © <?= date('Y') ?> Solenis Studio
        </p>
		<?php if (!$hasParam): ?>
          <a href="#transfer" class="btn btn-primary">
      <i class="bi bi-qr-code-scan me-2"></i>Commencer maintenant
    </a>
        <?php endif; ?>
      </div>
    </div>
    <hr class="my-4 opacity-25">
    <div class="text-center opacity-50 small">
      Données auto-détruites après 30 minutes • Aucun stockage persistant
    </div>
  </div>
</footer>
</body>


</html>