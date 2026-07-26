<?php if (!defined('PHONE2LAPTOP_APP')) exit('Accès direct interdit.'); ?>
<?php
function isMobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Mots-clés typiques des appareils mobiles
    $mobileKeywords = [
        'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry',
        'IEMobile', 'Opera Mini', 'Opera Mobi', 'Windows Phone',
        'BB10', 'PlayBook', 'Mobile Safari', 'webOS', 'Kindle', 'Silk'
    ];
	// echo "$userAgent";

    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
?>
<!--
######################################
	
######################################
-->

<?php if (!$hasParam and isMobile()): ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.laptop-icon {
  position: relative;
  margin: 20px auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.laptop-icon i {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
</style>

<section id="transfer" class="py-5 bg-light">
  <div class="container">
    <div class="text-center py-5">
      <div class="mt-4 d-inline-block p-3 bg-white rounded-3 shadow-sm border">
        <h2 class="mb-4 fw-bold">Ouvrez <b>phone2laptop.com</b> sur votre ordinateur</h2>
        <div class="laptop-icon mb-4">
          <i class="fas fa-laptop fa-7x text-primary"></i>
        </div>
      </div>
      <div class="mt-4 d-inline-block p-3 bg-white rounded-3 shadow-sm border">
        <h3 class="mb-3 fw-semibold">Puis, scannez le QR Code avec votre mobile</h3>
        <!-- Bouton qui ouvre la caméra -->
        <button id="openCameraBtn" class="btn btn-success btn-lg px-4 py-2">
          <i class="fas fa-camera me-2"></i>Ouvrir la caméra
        </button>
      </div>
    </div>
  </div>

  <!-- Modal pour afficher la caméra -->
  <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Scanner le QR Code</h5>
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

<!-- Charger les scripts dans l'ordre -->

<script>
document.addEventListener('DOMContentLoaded', function () {
  const openCameraBtn = document.getElementById('openCameraBtn');
  if (!openCameraBtn) {
    console.error("Le bouton #openCameraBtn n'a pas été trouvé !");
    return;
  }

  // Fonction utilitaire pour valider une URL
  function isValidHttpUrl(string) {
    let url;
    try {
      url = new URL(string);
    } catch (_) {
      return false;
    }
    return url.protocol === "http:" || url.protocol === "https:";
  }

  openCameraBtn.addEventListener('click', async () => {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        if (!video || !canvas) {
          console.error("Les éléments vidéo ou canvas sont introuvables !");
          return;
        }

        video.srcObject = stream;

        video.addEventListener('play', () => {
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;

          const modalElement = document.getElementById('cameraModal');
          if (!modalElement) {
            console.error("La modal #cameraModal n'existe pas !");
            return;
          }

          if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            console.error("Bootstrap Modal n'est pas chargé !");
            return;
          }

          const modal = new bootstrap.Modal(modalElement);
          modal.show();

          let scanning = true;

          const scan = () => {
            if (!scanning) return;

            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
              const url = code.data.trim();

              // ✅ Validation de sécurité : on n'ouvre que les URLs HTTP/HTTPS
              if (isValidHttpUrl(url)) {
                // Option 1 : Redirection automatique (décommente si souhaité)
                // window.location.href = url;

                // Option 2 : Ouvrir dans un nouvel onglet (recommandé pour la sécurité UX)
                window.open(url, '_blank');

                // Arrêter le scan et fermer la caméra
                scanning = false;
                modal.hide();
                stream.getTracks().forEach(track => track.stop());
              } else {
                // Afficher un message si l'URL est invalide
                const resultElement = document.getElementById('qrResult');
                if (resultElement) {
                  resultElement.innerHTML = `<p class="text-warning">Contenu non valide : ${url}</p>`;
                }
                // Continuer à scanner
                requestAnimationFrame(scan);
              }
            } else {
              requestAnimationFrame(scan);
            }
          };

          requestAnimationFrame(scan);

          // Fermer proprement si la modal est fermée manuellement
          modalElement.addEventListener('hidden.bs.modal', () => {
            scanning = false;
            stream.getTracks().forEach(track => track.stop());
          });
        });
      } catch (err) {
        alert("Impossible d'accéder à la caméra : " + err.message);
      }
    } else {
      alert("Votre navigateur ne supporte pas l'accès à la caméra.");
    }
  });
});
</script>
<?php endif; ?>

<!--
######################################
	
######################################
-->
<?php if ($hasParam or !isMobile() ): ?>

<section id="transfer" class="py-5">
  <div class="text-center">
    <div class="container mt-4">
      
	  
      <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm">
        <img src="<?= $qrUrl ?>" alt="QR Code – Ouvrir sur mobile" class="img-fluid" style="max-width:180px; height:auto;">
      </div>
	  <h1 class="fw-bold mb-3 text-primary" >Scannez pour synchroniser un autre appareil</h1>
    </div>
  </div>

  <div class="container mt-4">
    <?php if (!$hasParam): ?>
		
		<p class="small text-muted mt-2" align="center">Synchronisation en temps réel entre appareils.</p>
	<?php endif; ?>

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
const token   = "<?= $token ?>";
const apiBase = "<?= $baseUrl ?>/api/";
const mobileDevice = <?= isMobile() ? 'true' : 'false' ?>;

/* ---------- fichiers ---------- */
const listEl = document.getElementById('file-list');
function renderList(files){
  listEl.innerHTML = '';
  files.forEach(f => {
    // Déterminer l'icône ou la miniature
    let previewHtml = '';
    const lowerName = f.name.toLowerCase();
    const ext = lowerName.split('.').pop();

    // Types d'images supportés
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp', 'svg'];
    if (imageExts.includes(ext) && !mobileDevice) {
      previewHtml = `<a href="${f.url}"><img src="${f.url}" alt="Miniature" class="img-thumbnail" style="width:40px; height:40px; object-fit:cover;"></a>`;

    } else {
      // Icônes par type de fichier
      let icon = 'bi-file-earmark'; // icône par défaut
      if (ext === 'pdf') {
        icon = 'bi-file-earmark-pdf text-danger';
      } else if (['doc', 'docx'].includes(ext)) {
        icon = 'bi-file-earmark-word text-primary';
      } else if (['xls', 'xlsx'].includes(ext)) {
        icon = 'bi-file-earmark-excel text-success';
      } else if (['ppt', 'pptx'].includes(ext)) {
        icon = 'bi-file-earmark-ppt text-warning';
      } else if (ext === 'txt') {
        icon = 'bi-file-earmark-text text-secondary';
      } else if (['zip', 'rar', '7z'].includes(ext)) {
        icon = 'bi-file-earmark-zip text-info';
      } else if (imageExts.includes(ext)) {
        icon = 'bi-file-earmark-image text-primary';
      }
      previewHtml = `<a class="bi ${icon}" href="${f.url}"  style="font-size: 1.5rem;"></a>`;
    }

    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
    li.innerHTML = `
      <div class="d-flex align-items-center gap-2 flex-grow-1">
        ${previewHtml}
        <div class="flex-grow-1 text-truncate">
          <div class="fw-medium">${f.name.length > 16 ? f.name.substring(0, 16) + '…' + f.name.slice(-3) : f.name}</div>
          <small class="text-muted">${Math.round(f.size/1024)} Ko</small>
        </div>
      </div>
      <div class="d-flex gap-1">
        <a class="btn btn-sm btn-outline-primary" href="${f.url}" download aria-label="Télécharger le fichier" title="Télécharger">
          <i class="bi bi-download"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger delete-file" type="button" aria-label="Supprimer le fichier" title="Supprimer">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
    li.querySelector('.delete-file').addEventListener('click', () => deleteFile(f.name));
    listEl.appendChild(li);
  });
}

async function deleteFile(name){
  if (!confirm(`Supprimer « ${name} » ?`)) return;

  try {
    const response = await fetch(apiBase+'close.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({token,action:'delete_file',name})
    });
    if (!response.ok) {
      throw new Error(`Échec de la suppression (${response.status})`);
    }
    await refreshFiles();
  } catch (error) {
    console.error('Delete file error:', error);
    alert('❌ Erreur : ' + error.message);
  }
}


async function refreshFiles(){
  const r = await fetch(`${apiBase}files.php?token=`+token);
  renderList(await r.json());
}

/* ---------- texte ---------- */
const txtField = document.getElementById('shared-text');
let txtCache   = '';
async function refreshText(){
  const r = await fetch(`${apiBase}text.php?token=`+token);
  const j = await r.json();
  if (j.text !== undefined && j.text !== txtCache){
    txtCache      = j.text;
    txtField.value= j.text;
  }
}

/* ---------- init + SSE ---------- */
refreshFiles(); refreshText();
const es = new EventSource("<?= $baseUrl ?>/stream.php?token="+token);
es.onmessage = () => { refreshFiles(); refreshText(); };

/* ---------- upload avec progression (CORRIGÉ) ---------- */
document.getElementById('file-input').addEventListener('change', e => {
  const files = e.target.files;
  if (files.length === 0) return;

  const progressContainer = document.getElementById('upload-progress');
  const progressBar = document.getElementById('progress-bar');
  
  // Réinitialiser et afficher la barre
  progressBar.style.width = '0%';
  progressContainer.style.display = 'block';

  let uploadedCount = 0;
  let totalSize = 0;
  let uploadedSize = 0;

  // Calculer la taille totale
  for (const file of files) totalSize += file.size;

  const uploadFile = (file) => {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      const fd = new FormData();
      fd.append('token', token);
      fd.append('file', file);

      // 🔑 IMPORTANT : ouvrir la requête AVANT d'envoyer
      xhr.open('POST', apiBase + 'upload.php');

      // Gestion de la progression
      xhr.upload.onprogress = (event) => {
        if (event.lengthComputable) {
          const currentFileUploaded = event.loaded;
          const totalUploadedSoFar = uploadedSize + currentFileUploaded;
          const percent = Math.min(100, Math.round((totalUploadedSoFar / totalSize) * 100));
          progressBar.style.width = percent + '%';
        }
      };

      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const res = JSON.parse(xhr.responseText);
            if (res && res.ok) {
              uploadedSize += file.size;
              uploadedCount++;
              resolve();
            } else {
              reject(new Error(res.error || 'Erreur serveur'));
            }
          } catch (e) {
            reject(new Error('Réponse invalide du serveur'));
          }
        } else {
          reject(new Error('Échec de l’envoi (' + xhr.status + ')'));
        }
      };

      xhr.onerror = () => reject(new Error('Connexion perdue'));
      xhr.onabort = () => reject(new Error('Envoi annulé'));

      // ✅ Maintenant on peut envoyer
      xhr.send(fd);
    });
  };

  // Lancer les uploads (séquentiellement)
  let uploadChain = Promise.resolve();
  for (const file of files) {
    uploadChain = uploadChain.then(() => uploadFile(file));
  }

  uploadChain
    .then(() => {
      refreshFiles(); // Mettre à jour la liste
    })
    .catch(err => {
      console.error('Upload error:', err);
      alert('❌ Erreur : ' + err.message);
    })
    .finally(() => {
      progressContainer.style.display = 'none';
      e.target.value = ''; // Réinitialiser l'input
    });
});
/* ---------- envoi texte (debounce) ---------- */
const txtEl=document.getElementById('shared-text'); let lastContent="",typingTimer;
async function loadText(){const r=await fetch(`${apiBase}text.php?token=`+token);const j=await r.json();if(j.text!==undefined&&j.text!==lastContent){lastContent=j.text;txtEl.value=j.text;}}
loadText(); setInterval(loadText,2000);
txtEl.addEventListener('input',()=>{clearTimeout(typingTimer);typingTimer=setTimeout(saveText,500);});
async function saveText(){const c=txtEl.value;if(c===lastContent)return;lastContent=c;await fetch(apiBase+'text.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token,text:c})});}

/* ---------- copier le texte ---------- */
document.getElementById('btn-copy').addEventListener('click', async () => {
  const text = txtField.value;
  if (!text.trim()) return;

  try {
    await navigator.clipboard.writeText(text);
    const btn = document.getElementById('btn-copy');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2"></i> Copié !';
    btn.classList.replace('btn-outline-secondary', 'btn-outline-success');
    setTimeout(() => {
      btn.innerHTML = originalHTML;
      btn.classList.replace('btn-outline-success', 'btn-outline-secondary');
    }, 1500);
  } catch (err) {
    alert('Impossible de copier. Veuillez sélectionner et copier manuellement.');
  }
});

/* ---------- tout supprimer ---------- */
document.getElementById('btn-delete').addEventListener('click', async ()=>{
  if (!confirm('Êtes-vous sûr de vouloir tout supprimer ?')) return;

  try {
    const response = await fetch(apiBase+'close.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({token,action:'delete'})
    });
    if (!response.ok) {
      throw new Error(`Échec de la suppression (${response.status})`);
    }
    listEl.innerHTML = '';
    txtField.value   = '';
    txtCache         = '';
  } catch (error) {
    console.error('Delete error:', error);
    alert('❌ Erreur : ' + error.message);
  }
});
</script>
<?php endif; ?>
