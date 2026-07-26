const token   = "<?= $token ?>";
const apiBase = "<?= $baseUrl ?>/api/";

/* ---------- fichiers ---------- */
const listEl = document.getElementById('file-list');
function renderList_old(files){
  listEl.innerHTML = '';
  files.forEach(f=>{
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `<span>${f.name.length > 16 ? f.name.substring(0, 16) + '…' : f.name} <span class="badge bg-secondary">${Math.round(f.size / 1024)} Ko</span></span>
   <a class="btn btn-sm btn-outline-primary" href="${f.url}" download>↓</a>`;
    listEl.appendChild(li);
  });
}
function renderList(files){
  listEl.innerHTML = '';
  files.forEach(f => {
    // Déterminer l'icône ou la miniature
    let previewHtml = '';
    const lowerName = f.name.toLowerCase();
    const ext = lowerName.split('.').pop();

    // Types d'images supportés
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    if (imageExts.includes(ext)) {
      previewHtml = `<img src="${f.url}" alt="Miniature" class="img-thumbnail" style="width:40px; height:40px; object-fit:cover;">`;
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
      }
      previewHtml = `<i class="bi ${icon}" style="font-size: 1.5rem;"></i>`;
    }

    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
    li.innerHTML = `
      <div class="d-flex align-items-center gap-2 flex-grow-1">
        ${previewHtml}
        <div class="flex-grow-1 text-truncate">
          <div class="fw-medium">${f.name.length > 16 ? f.name.substring(0, 16) + '…' : f.name}</div>
          <small class="text-muted">${Math.round(f.size/1024)} Ko</small>
        </div>
      </div>
      <a class="btn btn-sm btn-outline-primary" href="${f.url}" download>↓</a>
    `;
    listEl.appendChild(li);
  });
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
  await fetch(apiBase+'close.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({token,action:'delete'})
  });
  listEl.innerHTML = '';
  txtField.value   = '';
  txtCache         = '';
});