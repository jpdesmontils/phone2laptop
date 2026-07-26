<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= $baseUrl ?>">
	<img src="/public/assets/images/icon.svg" width="48" alt=""/>
	phone2laptop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="#why">Pourquoi ?</a></li>
        <li class="nav-item"><a class="nav-link" href="#donate">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="https://donate.stripe.com/4gM5kE2z69M21qFbDAew807">Contribuer</a></li>
        <!--<li class="nav-item"><a class="nav-link" href="#blog">Blog</a></li>-->
        <?php if (!$hasParam): ?>
          <a href="#transfer" class="btn btn-primary shadow">
      <i class="bi bi-qr-code-scan me-2"></i>Commencer
    </a>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
