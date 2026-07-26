document.addEventListener('DOMContentLoaded', function () {
  const openCameraBtn = document.getElementById('openCameraBtn');
  if (!openCameraBtn || !window.P2L) return;

  function isValidHttpUrl(string) {
    try {
      const url = new URL(string);
      return url.protocol === "http:" || url.protocol === "https:";
    } catch (_) {
      return false;
    }
  }

  openCameraBtn.addEventListener('click', async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      alert("Votre navigateur ne supporte pas l'accès à la caméra.");
      return;
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
      const video = document.getElementById('video');
      const canvas = document.getElementById('canvas');
      const ctx = canvas.getContext('2d');

      if (!video || !canvas) return;

      video.srcObject = stream;

      video.addEventListener('play', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const modalElement = document.getElementById('cameraModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        let scanning = true;

        const scan = () => {
          if (!scanning) return;
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const code = jsQR(imageData.data, imageData.width, imageData.height);

          if (code && isValidHttpUrl(code.data)) {
            window.open(code.data.trim(), '_blank');
            scanning = false;
            modal.hide();
            stream.getTracks().forEach(t => t.stop());
          } else {
            requestAnimationFrame(scan);
          }
        };

        requestAnimationFrame(scan);

        modalElement.addEventListener('hidden.bs.modal', () => {
          scanning = false;
          stream.getTracks().forEach(t => t.stop());
        });
      });
    } catch (err) {
      alert("Impossible d'accéder à la caméra : " + err.message);
    }
  });
});