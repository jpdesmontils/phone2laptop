// sync.js – Gestion de la synchronisation fichiers & notes
(function () {
  'use strict';

  // Vérification que les variables nécessaires sont présentes
  if (!window.P2L || !window.P2L.token || !window.P2L.apiBase) {
    console.error('Configuration manquante : window.P2L non définie correctement.');
    return;
  }

  const { token, apiBase, baseUrl } = window.P2L;

  // === Éléments DOM ===
  const listEl = document.getElementById('file-list');
  const txtField = document.getElementById('shared-text');
  const btnCopy = document.getElementById('btn-copy');
  const btnDelete = document.getElementById('btn-delete');
  const fileInput = document.getElementById('file-input');
  const uploadProgress = document.getElementById('upload-progress');
  const progressBar = document.getElementById('progress-bar');

  if (!listEl || !txtField || !btnCopy || !btnDelete || !fileInput) {
    console.warn('Certains éléments requis sont absents. sync.js ignoré.');
    return;
  }

  // === État local ===
  let txtCache = '';
  let lastContent = '';

  // === Utilitaires ===
  function formatFileSize(bytes) {
    return Math.round(bytes / 1024) + ' Ko';
  }

  function getFileIcon(ext) {
    const iconMap = {
      pdf: 'bi-file-earmark-pdf text-danger',
      doc: 'bi-file-earmark-word text-primary',
      docx: 'bi-file-earmark-word text-primary',
      xls: 'bi-file-earmark-excel text-success',
      xlsx: 'bi-file-earmark-excel text-success',
      ppt: 'bi-file-earmark-ppt text-warning',
      pptx: 'bi-file-earmark-ppt text-warning',
      txt: 'bi-file-earmark-text text-secondary',
      zip: 'bi-file-earmark-zip text-info',
      rar: 'bi-file-earmark-zip text-info',
      '7z': 'bi-file-earmark-zip text-info'
    };
    return iconMap[ext] || 'bi-file-earmark';
  }

  // === Rendu de la liste de fichiers ===
  function renderList(files) {
    listEl.innerHTML = '';
    const imageExts = new Set(['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);

    files.forEach(f => {
      const lowerName = f.name.toLowerCase();
      const ext = lowerName.split('.').pop();

      let previewHtml = '';
      if (imageExts.has(ext)) {
        previewHtml = `<img src="${f.url}" alt="Miniature" class="img-thumbnail" style="width:40px; height:40px; object-fit:cover;">`;
      } else {
        const iconClass = getFileIcon(ext);
        previewHtml = `<a href="${f.url}" class="bi ${iconClass}" style="font-size: 1.5rem;"></a>`;
      }

      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
      li.innerHTML = `
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          ${previewHtml}
          <div class="flex-grow-1 text-truncate">
            <div class="fw-medium">${f.name}</div>
            <small class="text-muted">${formatFileSize(f.size)}</small>
          </div>
        </div>
        <a class="btn btn-sm btn-outline-primary" href="${f.url}" download>↓</a>
      `;
      listEl.appendChild(li);
    });
  }

  // === Requêtes API ===
  async function refreshFiles() {
    try {
      const res = await fetch(`${apiBase}files.php?token=${encodeURIComponent(token)}`);
      if (!res.ok) throw new Error('Erreur lors du chargement des fichiers');
      const files = await res.json();
      renderList(files);
    } catch (err) {
      console.error('refreshFiles:', err);
    }
  }

  async function refreshText() {
    try {
      const res = await fetch(`${apiBase}text.php?token=${encodeURIComponent(token)}`);
      if (!res.ok) throw new Error('Erreur lors du chargement du texte');
      const data = await res.json();
      if (data.text !== undefined && data.text !== txtCache) {
        txtCache = data.text;
        txtField.value = data.text;
      }
    } catch (err) {
      console.error('refreshText:', err);
    }
  }

  // === Upload de fichiers avec progression ===
  fileInput.addEventListener('change', async (e) => {
    const files = e.target.files;
    if (files.length === 0) return;

    let uploadedSize = 0;
    const totalSize = Array.from(files).reduce((sum, f) => sum + f.size, 0);

    // Réinitialiser la barre
    progressBar.style.width = '0%';
    uploadProgress.style.display = 'block';

    const uploadFile = (file) => {
      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        const fd = new FormData();
        fd.append('token', token);
        fd.append('file', file);

        xhr.open('POST', apiBase + 'upload.php');

        xhr.upload.onprogress = (event) => {
          if (event.lengthComputable) {
            const totalUploaded = uploadedSize + event.loaded;
            const percent = Math.min(100, Math.round((totalUploaded / totalSize) * 100));
            progressBar.style.width = percent + '%';
          }
        };

        xhr.onload = () => {
          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              const res = JSON.parse(xhr.responseText);
              if (res && res.ok) {
                uploadedSize += file.size;
                resolve();
              } else {
                reject(new Error(res.error || 'Erreur serveur'));
              }
            } catch {
              reject(new Error('Réponse invalide'));
            }
          } else {
            reject(new Error(`HTTP ${xhr.status}`));
          }
        };

        xhr.onerror = () => reject(new Error('Connexion perdue'));
        xhr.send(fd);
      });
    };

    try {
      for (const file of files) {
        await uploadFile(file);
      }
      await refreshFiles();
    } catch (err) {
      alert('❌ Erreur : ' + err.message);
    } finally {
      uploadProgress.style.display = 'none';
      e.target.value = ''; // reset input
    }
  });

  // === Synchronisation texte (polling + debounce) ===
  async function loadText() {
    try {
      const res = await fetch(`${apiBase}text.php?token=${encodeURIComponent(token)}`);
      const data = await res.json();
      if (data.text !== undefined && data.text !== lastContent) {
        lastContent = data.text;
        txtField.value = data.text;
      }
    } catch (err) {
      console.error('loadText polling:', err);
    }
  }

  let typingTimer;
  txtField.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(saveText, 500);
  });

  async function saveText() {
    const content = txtField.value;
    if (content === lastContent) return;
    lastContent = content;

    try {
      await fetch(apiBase + 'text.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, text: content })
      });
    } catch (err) {
      console.error('saveText:', err);
    }
  }

  // Démarrer le polling
  loadText();
  setInterval(loadText, 2000);

  // === Copier le texte ===
  btnCopy.addEventListener('click', async () => {
    const text = txtField.value.trim();
    if (!text) return;

    try {
      await navigator.clipboard.writeText(text);
      const originalHTML = btnCopy.innerHTML;
      btnCopy.innerHTML = '<i class="bi bi-check2"></i> Copié !';
      btnCopy.classList.replace('btn-outline-secondary', 'btn-outline-success');
      setTimeout(() => {
        btnCopy.innerHTML = originalHTML;
        btnCopy.classList.replace('btn-outline-success', 'btn-outline-secondary');
      }, 1500);
    } catch (err) {
      alert('Impossible de copier. Veuillez sélectionner et copier manuellement.');
    }
  });

  // === Tout supprimer ===
  btnDelete.addEventListener('click', async () => {
    if (!confirm('Êtes-vous sûr de vouloir tout supprimer ?')) return;

    try {
      await fetch(apiBase + 'close.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, action: 'delete' })
      });
      listEl.innerHTML = '';
      txtField.value = '';
      txtCache = '';
      lastContent = '';
    } catch (err) {
      console.error('Suppression échouée:', err);
    }
  });

  // === Initialisation ===
  refreshFiles();
  refreshText();

  // Optionnel : EventSource (SSE) si tu veux remplacer le polling
  /*
  const es = new EventSource(`${baseUrl}/stream.php?token=${encodeURIComponent(token)}`);
  es.onmessage = () => {
    refreshFiles();
    refreshText();
  };
  es.onerror = (err) => console.warn('SSE error:', err);
  */

})();