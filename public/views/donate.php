<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<?php if (!$hasParam): ?>
<section id="donate" class="py-5 bg-light">
  <div class="container">

    <div class="row justify-content-center mt-5">
      <div class="col-lg-8">
        <div class="bg-white p-4 rounded-3 shadow-sm border">
          <h5 class="mb-3 text-center">Pourquoi est-ce gratuit ?</h5>
          <p class="text-muted">
            <strong>phone2laptop</strong> a été créé par <a href="https://solenis.studio" target="_blank" class="text-decoration-none">Solenis Studio</a>, une agence spécialisée dans le développement de MVP (Minimum Viable Product).
          </p>
          <p class="text-muted mb-0">
            Ce service un vecteur de communication pour nous : il démontre notre philosophie — simplicité, efficacité, respect de la vie privée.  
            Nous croyons qu’un bon outil doit être accessible à tous, sans friction.
          </p>
        </div>
      </div>
    </div>

    <div class="row justify-content-center mt-5">
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

<?php endif; ?>