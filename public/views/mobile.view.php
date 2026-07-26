<?php if (!defined('PHONE2LAPTOP_APP')) exit; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/public/css/styles.css">

<section id="transfer" class="py-5 bg-light">
  <div class="container">
    <div class="text-center py-5">
      <div class="mt-4 d-inline-block p-3 bg-white rounded-3 shadow-sm border">
        <h2 class="mb-4 fw-bold"><?= $texts['mobile_title'] ?></h2>
        <div class="laptop-icon mb-4">
          <i class="fas fa-laptop fa-7x text-primary"></i>
        </div>
      </div>
      <div class="mt-4 d-inline-block p-3 bg-white rounded-3 shadow-sm border">
        <h3 class="mb-3 fw-semibold"><?= $texts['mobile_subtitle'] ?></h3>
        <button id="openCameraBtn" class="btn btn-success btn-lg px-4 py-2">
          <i class="fas fa-camera me-2"></i><?= $texts['open_camera'] ?>
        </button>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><?= $texts['scan_qr'] ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <video id="video" width="100%" autoplay playsinline></video>
          <canvas id="canvas" style="display:none;"></canvas>
          <div id="qrResult" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script src="/public/js/camera.js"></script>
<script>
  // Passer les variables au JS via data-attributes ou objet global sécurisé
  window.P2L = {
    baseUrl: <?= json_encode($baseUrl) ?>
  };
</script>