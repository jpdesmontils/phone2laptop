<?php if (!defined('PHONE2LAPTOP_APP')) exit; ?>
<section id="transfer" class="py-5">
  <div class="text-center">
    <div class="container mt-4">
      <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm">
        <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code – Ouvrir sur mobile" class="img-fluid" style="max-width:180px; height:auto;">
      </div>
      <h1 class="fw-bold mb-3 text-primary"><?= $texts['desktop_title'] ?></h1>
    </div>
  </div>

  <div class="container mt-4">
    <?php if (!$hasParam): ?>
      <p class="small text-muted mt-2" align="center"><?= $texts['sync_info'] ?></p>
    <?php endif; ?>

    <div class="row g-4" id="share">
      <div class="col-md-6">
        <div class="border rounded-3 p-4 text-center shadow-sm h-100 d-flex flex-column">
          <h5 class="mb-3"><?= $texts['share_files'] ?></h5>
          <form id="upload-form" class="flex-grow-1 d-flex flex-column justify-content-center">
            <label for="file-input" class="btn btn-outline-primary mb-2"><?= $texts['choose_files'] ?></label>
            <input id="file-input" class="d-none" type="file" multiple>
            <p class="small text-muted mt-2"><?= $texts['drag_drop'] ?></p>
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

      <div class="col-md-6">
        <div class="border rounded-3 p-4 shadow-sm h-100 d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="mb-0"><?= $texts['share_notes'] ?></h5>
            <button id="btn-copy" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
              <i class="bi bi-clipboard"></i> <?= $texts['copy_button'] ?>
            </button>
          </div>
          <textarea id="shared-text" class="form-control flex-grow-1" placeholder="Écrivez un message…"></textarea>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <button id="btn-delete" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash"></i> <?= $texts['delete_all'] ?>
      </button>
    </div>
  </div>

  <div class="mt-5" align="center">
    <div class="col-lg-8 text-center">
      <h5 class="mb-3"><?= $texts['donate_title'] ?></h5>
      <p class="text-muted mb-4"><?= $texts['donate_text'] ?></p>
      <a href="https://donate.stripe.com/4gM5kE2z69M21qFbDAew807" target="_blank" class="btn btn-success btn-lg px-5">
        <i class="bi bi-heart me-2"></i> <?= $texts['donate_button'] ?>
      </a>
      <p class="small text-muted mt-2"><?= $texts['donate_footer'] ?></p>
    </div>
  </div>
</section>

<script>
  window.P2L = {
    token: <?= json_encode($token) ?>,
    apiBase: <?= json_encode($baseUrl . '/api/') ?>,
    baseUrl: <?= json_encode($baseUrl) ?>
  };
</script>
<script src="/public/js/sync.js"></script>