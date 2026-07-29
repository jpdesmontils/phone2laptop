(function () {
  'use strict';

  const config = window.P2L;
  const messages = config?.i18n || {};
  const message = (key, replacements = {}) => Object.entries(replacements).reduce(
    (text, [name, value]) => text.replace(`{${name}}`, value),
    messages[key] || key
  );
  const encoder = new TextEncoder();
  const decoder = new TextDecoder();
  const elements = {
    list: document.getElementById('file-list'),
    text: document.getElementById('shared-text'),
    copy: document.getElementById('btn-copy'),
    delete: document.getElementById('btn-delete'),
    input: document.getElementById('file-input'),
    progress: document.getElementById('upload-progress'),
    progressBar: document.getElementById('progress-bar'),
    countdown: document.getElementById('expiry-countdown'),
    expiryLabel: document.getElementById('expiry-label'),
    qr: document.getElementById('session-qr'),
    deleteModal: document.getElementById('delete-modal'),
    deleteModalMessage: document.getElementById('delete-modal-message'),
    deleteModalConfirm: document.getElementById('delete-modal-confirm')
  };
  if (!config || Object.values(elements).some(element => !element)) return;

  function encodeBase64Url(bytes) {
    let binary = '';
    bytes.forEach(byte => { binary += String.fromCharCode(byte); });
    return btoa(binary).replaceAll('+', '-').replaceAll('/', '_').replace(/=+$/, '');
  }

  function decodeBase64Url(value) {
    const binary = atob(value.replaceAll('-', '+').replaceAll('_', '/') + '==='.slice((value.length + 3) % 4));
    return Uint8Array.from(binary, char => char.charCodeAt(0));
  }

  async function sessionKey() {
    const params = new URLSearchParams(location.hash.slice(1));
    let encodedKey = params.get('key');
    if (!encodedKey) {
      encodedKey = encodeBase64Url(crypto.getRandomValues(new Uint8Array(32)));
      params.set('key', encodedKey);
      params.set('share', '');
      history.replaceState(null, '', `${location.pathname}${location.search}#${params}`);
    }
    const rawKey = decodeBase64Url(encodedKey);
    if (rawKey.length !== 32) throw new Error(message('invalidKey'));
    return { encodedKey, key: await crypto.subtle.importKey('raw', rawKey, 'AES-GCM', false, ['encrypt', 'decrypt']) };
  }

  async function encryptBytes(key, clearBytes) {
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const encrypted = new Uint8Array(await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, clearBytes));
    const result = new Uint8Array(iv.length + encrypted.length);
    result.set(iv);
    result.set(encrypted, iv.length);
    return result;
  }

  async function decryptBytes(key, encryptedBytes) {
    const bytes = new Uint8Array(encryptedBytes);
    return new Uint8Array(await crypto.subtle.decrypt({ name: 'AES-GCM', iv: bytes.slice(0, 12) }, key, bytes.slice(12)));
  }

  function packFile(file, bytes) {
    const metadata = encoder.encode(JSON.stringify({ name: file.name, type: file.type || 'application/octet-stream' }));
    const packed = new Uint8Array(4 + metadata.length + bytes.byteLength);
    new DataView(packed.buffer).setUint32(0, metadata.length);
    packed.set(metadata, 4);
    packed.set(new Uint8Array(bytes), 4 + metadata.length);
    return packed;
  }

  function unpackFile(bytes) {
    const metadataLength = new DataView(bytes.buffer, bytes.byteOffset, 4).getUint32(0);
    const metadata = JSON.parse(decoder.decode(bytes.slice(4, 4 + metadataLength)));
    return { ...metadata, content: bytes.slice(4 + metadataLength) };
  }

  async function api(url, options) {
    const response = await fetch(url, options);
    if (!response.ok) throw new Error(response.status === 410 ? message('expired') : message('serverError', { status: response.status }));
    return response;
  }

  const fileRows = new Map();

  function addFileRow(remote, file) {
    const item = document.createElement('li');
    item.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
    const label = document.createElement('div');
    label.className = 'flex-grow-1 text-truncate';
    const name = document.createElement('div');
    name.className = 'fw-medium';
    name.textContent = file.name;
    const size = document.createElement('small');
    size.className = 'text-muted';
    size.textContent = `${Math.round(file.content.byteLength / 1024)} ${message('kilobyte')}`;
    label.append(name, size);
    const actions = document.createElement('div');
    actions.className = 'd-flex gap-2 flex-shrink-0';
    const download = document.createElement('button');
    download.className = 'btn btn-sm btn-outline-primary';
    download.type = 'button';
    download.textContent = '↓';
    download.setAttribute('aria-label', message('download', { name: file.name }));
    download.addEventListener('click', () => {
      const url = URL.createObjectURL(new Blob([file.content], { type: file.type }));
      const anchor = document.createElement('a');
      anchor.href = url;
      anchor.download = file.name;
      anchor.click();
      setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    const remove = document.createElement('button');
    remove.className = 'btn btn-sm btn-outline-danger';
    remove.type = 'button';
    remove.innerHTML = '<i class="bi bi-trash" aria-hidden="true"></i>';
    remove.setAttribute('aria-label', message('deleteFile', { name: file.name }));
    remove.addEventListener('click', () => showDeleteConfirmation(
      message('deleteFileConfirm', { name: file.name }),
      async () => {
        await deleteFile(remote.id);
        item.remove();
        fileRows.delete(remote.id);
      }
    ));
    actions.append(download, remove);
    item.append(label, actions);
    elements.list.append(item);
    fileRows.set(remote.id, item);
  }

  let filesRefreshPromise = null;
  async function performFilesRefresh(key) {
    const response = await api(`${config.apiBase}files.php?token=${encodeURIComponent(config.token)}`);
    const expiresAt = response.headers.get('X-Session-Expires-At');
    config.expiresAt = expiresAt ? Number(expiresAt) : null;
    const files = await response.json();
    const remoteIds = new Set(files.map(file => file.id));
    fileRows.forEach((row, id) => {
      if (!remoteIds.has(id)) {
        row.remove();
        fileRows.delete(id);
      }
    });
    for (const remote of files) {
      if (fileRows.has(remote.id)) continue;
      try {
        const encrypted = await (await api(remote.url)).arrayBuffer();
        addFileRow(remote, unpackFile(await decryptBytes(key, encrypted)));
      } catch (error) {
        console.error(message('unreadable'), remote.id, error);
      }
    }
  }

  function refreshFiles(key) {
    if (!filesRefreshPromise) {
      filesRefreshPromise = performFilesRefresh(key).finally(() => { filesRefreshPromise = null; });
    }
    return filesRefreshPromise;
  }

  let lastText = '';
  async function loadText(key) {
    const payload = await (await api(`${config.apiBase}text.php?token=${encodeURIComponent(config.token)}`)).json();
    if (!payload.ciphertext) return;
    const clearText = decoder.decode(await decryptBytes(key, decodeBase64Url(payload.ciphertext)));
    if (clearText !== lastText) {
      lastText = clearText;
      elements.text.value = clearText;
    }
  }

  async function saveText(key) {
    const clearText = elements.text.value;
    if (clearText === lastText) return;
    const ciphertext = encodeBase64Url(await encryptBytes(key, encoder.encode(clearText)));
    await api(`${config.apiBase}text.php`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: config.token, ciphertext })
    });
    lastText = clearText;
  }

  async function uploadFile(key, file) {
    const encrypted = await encryptBytes(key, packFile(file, await file.arrayBuffer()));
    const data = new FormData();
    data.append('token', config.token);
    const id = typeof crypto.randomUUID === 'function'
      ? crypto.randomUUID()
      : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
          const random = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
          return (character === 'x' ? random : (random & 3) | 8).toString(16);
        });
    data.append('file', new Blob([encrypted], { type: 'application/octet-stream' }), `${id}.enc`);
    const response = await api(`${config.apiBase}upload.php`, { method: 'POST', body: data });
    const result = await response.json();
    config.expiresAt = result.expiresAt;
  }

  async function deleteSession() {
    await api(`${config.apiBase}close.php`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: config.token, action: 'delete' })
    });
    elements.list.replaceChildren();
    fileRows.clear();
    elements.text.value = '';
  }

  async function deleteFile(name) {
    await api(`${config.apiBase}close.php`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: config.token, action: 'delete-file', name })
    });
  }

  const deleteModal = new bootstrap.Modal(elements.deleteModal);
  let pendingDeletion = null;
  function showDeleteConfirmation(text, deletion) {
    elements.deleteModalMessage.textContent = text;
    pendingDeletion = deletion;
    deleteModal.show();
  }

  elements.deleteModal.addEventListener('hidden.bs.modal', () => { pendingDeletion = null; });
  elements.deleteModalConfirm.addEventListener('click', async () => {
    if (!pendingDeletion) return;
    const deletion = pendingDeletion;
    pendingDeletion = null;
    elements.deleteModalConfirm.disabled = true;
    try {
      await deletion();
      deleteModal.hide();
    } catch (error) {
      alert(message('deleteError', { message: error.message }));
    } finally {
      elements.deleteModalConfirm.disabled = false;
    }
  });

  function startCountdown() {
    let deletionRequested = false;
    const update = async () => {
      if (config.expiresAt === null) {
        elements.expiryLabel.hidden = false;
        elements.countdown.textContent = '';
        return;
      }
      elements.expiryLabel.hidden = true;
      const remaining = Math.max(0, config.expiresAt - Date.now());
      const totalSeconds = Math.ceil(remaining / 1000);
      const minutes = Math.floor(totalSeconds / 60);
      const seconds = totalSeconds % 60;
      elements.countdown.textContent = `${minutes} min ${String(seconds).padStart(2, '0')} s`;
      if (remaining === 0 && !deletionRequested) {
        deletionRequested = true;
        try { await deleteSession(); } catch (error) { console.error(error); }
        elements.input.disabled = true;
        elements.text.disabled = true;
        elements.countdown.textContent = message('sessionExpired');
      }
    };
    update();
    setInterval(update, 1000);
  }

  async function initialize() {
    const { encodedKey, key } = await sessionKey();
    const shareUrl = `${config.shareUrl}#key=${encodeURIComponent(encodedKey)}&share=`;
    if (typeof QRCode === 'function') {
      new QRCode(elements.qr, { text: shareUrl, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
    } else {
      elements.qr.textContent = message('qrUnavailable');
      console.error(message('qrError'));
    }
    startCountdown();
    await Promise.all([refreshFiles(key), loadText(key)]);
    setInterval(() => Promise.all([refreshFiles(key), loadText(key)]).catch(console.error), 2000);

    let typingTimer;
    elements.text.addEventListener('input', () => {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => saveText(key).catch(console.error), 500);
    });
    const sendFiles = async files => {
      if (files.length === 0) return;
      elements.progressBar.style.width = '0%';
      elements.progress.style.display = 'block';
      try {
        for (let index = 0; index < files.length; index++) {
          await uploadFile(key, files[index]);
          elements.progressBar.style.width = `${Math.round(((index + 1) / files.length) * 100)}%`;
        }
        await refreshFiles(key);
      } catch (error) { alert(`❌ ${message('uploadError', { message: error.message })}`); }
      finally { elements.progress.style.display = 'none'; elements.input.value = ''; }
    };
    elements.input.addEventListener('change', event => sendFiles(Array.from(event.target.files)));
    const uploadForm = elements.input.closest('form');
    uploadForm.addEventListener('dragover', event => event.preventDefault());
    uploadForm.addEventListener('drop', event => {
      event.preventDefault();
      sendFiles(Array.from(event.dataTransfer.files));
    });
    elements.copy.addEventListener('click', () => navigator.clipboard.writeText(elements.text.value));
    elements.delete.addEventListener('click', () => showDeleteConfirmation(message('deleteConfirm'), deleteSession));
  }

  initialize().catch(error => {
    console.error(error);
    alert(message('openError', { message: error.message }));
  });
})();
