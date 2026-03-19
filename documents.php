<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Documents — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }

    .dash-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0; z-index: 100;
    }
    .dash-header-inner {
      display: flex; align-items: center; justify-content: space-between; height: 64px;
    }
    .dash-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary); text-decoration: none;
    }
    .dash-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }

    .page-content { padding: 32px 0; }

    /* Upload card */
    .upload-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 28px; margin-bottom: 28px;
    }
    .upload-card h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
    .upload-form-grid {
      display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: end;
    }
    .form-field label {
      display: block; font-size: 13px; font-weight: 600;
      margin-bottom: 6px; color: var(--foreground);
    }
    .form-field select, .form-field input[type="text"], .form-field textarea {
      width: 100%; padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
    }
    .form-field select:focus, .form-field input:focus, .form-field textarea:focus {
      outline: none; border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(11,111,255,.12);
    }
    .file-drop-zone {
      border: 2px dashed var(--border); border-radius: var(--radius-lg);
      padding: 28px 20px; text-align: center; cursor: pointer;
      transition: border-color .2s, background .2s;
      background: var(--muted);
    }
    .file-drop-zone:hover, .file-drop-zone.dragover {
      border-color: var(--primary); background: var(--secondary);
    }
    .file-drop-zone iconify-icon { font-size: 32px; color: var(--primary); display: block; margin-bottom: 8px; }
    .file-drop-zone p { font-size: 14px; color: var(--muted-foreground); margin: 0; }
    .file-drop-zone strong { color: var(--primary); }
    #fileInput { display: none; }
    #selectedFileName { font-size: 13px; color: var(--muted-foreground); margin-top: 8px; }

    /* Toolbar */
    .toolbar {
      display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .toolbar-search { position: relative; flex: 1; min-width: 220px; }
    .toolbar-search iconify-icon {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: var(--muted-foreground); font-size: 16px;
    }
    .toolbar-search input {
      width: 100%; padding: 9px 12px 9px 36px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
    }
    .toolbar-search input:focus { outline: none; border-color: var(--primary); }
    .toolbar select {
      padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit; cursor: pointer;
    }

    /* Documents table */
    .docs-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); overflow: hidden;
    }
    .docs-card-header {
      padding: 20px 24px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .docs-card-header h2 { font-size: 16px; font-weight: 700; }
    .docs-table { width: 100%; border-collapse: collapse; }
    .docs-table th {
      background: var(--muted); padding: 12px 16px; text-align: left;
      font-size: 12px; font-weight: 600; color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .03em;
    }
    .docs-table td { padding: 14px 16px; border-top: 1px solid var(--border); font-size: 14px; }
    .docs-table tr:hover td { background: var(--muted); }
    .doc-icon { font-size: 20px; color: var(--primary); vertical-align: middle; margin-right: 8px; }
    .category-badge {
      display: inline-block; padding: 3px 10px; font-size: 12px; font-weight: 600;
      border-radius: 20px; background: var(--secondary); color: var(--primary);
    }
    .empty-state {
      text-align: center; padding: 56px 24px; color: var(--muted-foreground);
    }
    .empty-state iconify-icon { font-size: 48px; opacity: .4; display: block; margin-bottom: 12px; }
    .empty-state p { margin: 0; font-size: 15px; }

    /* Toast */
    #toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--foreground); color: #fff;
      padding: 12px 20px; border-radius: var(--radius-md);
      font-size: 14px; z-index: 9999; display: none;
      box-shadow: var(--shadow-xl); max-width: 360px;
    }
    #toast.show { display: block; animation: slideUp .3s ease; }
    #toast.success { background: var(--success); }
    #toast.error   { background: var(--destructive); }
    @keyframes slideUp { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }

    /* Responsive */
    @media (max-width: 768px) {
      .upload-form-grid { grid-template-columns: 1fr; }
      .docs-table th:nth-child(4), .docs-table td:nth-child(4) { display: none; }
    }

    /* Upload progress */
    .upload-progress { display: none; margin-top: 12px; }
    .upload-progress.show { display: block; }
    progress { width: 100%; height: 8px; border-radius: 4px; }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="/" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Documents</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="messages" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:message-circle" style="font-size:15px;margin-right:6px"></iconify-icon>
          Messages
        </a>
        <a href="/" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <div class="page-content">
    <div class="container">

      <!-- Upload section -->
      <div class="upload-card">
        <h2><iconify-icon icon="lucide:upload" style="vertical-align:middle;margin-right:8px;color:var(--primary)"></iconify-icon>Upload Document</h2>
        <div class="upload-form-grid">

          <!-- Category -->
          <div class="form-field">
            <label for="categorySelect">Select type</label>
            <select id="categorySelect">
              <option value="">— Select type —</option>
              <option>Bill of lading (BOL)</option>
              <option>Broker agreement</option>
              <option>Cargo insurance</option>
              <option>Carrier agreement</option>
              <option>Certificate of insurance</option>
              <option>Driver's license</option>
              <option>Fuel receipt</option>
              <option>Invoice</option>
              <option>Liability insurance</option>
              <option>Lumper receipt</option>
              <option>Operating authority</option>
              <option>Other</option>
              <option>Packing list</option>
              <option>Proof of delivery (POD)</option>
              <option>Rate confirmation</option>
              <option>References</option>
              <option>Scale receipt</option>
              <option>Shipper agreement</option>
              <option>Tax info</option>
              <option>Truck wash receipt</option>
              <option>Void cheque</option>
              <option>W-9</option>
              <option>Weight tickets</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="form-field">
            <label for="docNotes">Notes (optional)</label>
            <input type="text" id="docNotes" placeholder="e.g. Invoice #1234 for shipment …" maxlength="500" />
          </div>

          <!-- File + Upload button -->
          <div class="form-field">
            <label>File</label>
            <div class="file-drop-zone" id="dropZone">
              <iconify-icon icon="lucide:file-up"></iconify-icon>
              <p>Drag &amp; drop or <strong>browse</strong></p>
              <p style="font-size:12px;margin-top:4px;">PDF, Word, Excel, Images • max 20 MB</p>
              <input type="file" id="fileInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv" />
            </div>
            <div id="selectedFileName"></div>
          </div>
        </div>

        <div style="margin-top:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <button class="btn btn-primary" id="uploadBtn" style="padding:10px 24px;">
            <iconify-icon icon="lucide:upload-cloud" style="margin-right:8px;vertical-align:middle;"></iconify-icon>
            Upload Document
          </button>
          <span id="uploadStatus" style="font-size:13px;color:var(--muted-foreground);"></span>
        </div>
      </div>

      <!-- Filter toolbar -->
      <div class="toolbar">
        <div class="toolbar-search">
          <iconify-icon icon="lucide:search"></iconify-icon>
          <input type="text" id="searchInput" placeholder="Search by filename or notes…" />
        </div>
        <select id="filterCategory">
          <option value="">All categories</option>
          <option>Bill of lading (BOL)</option>
          <option>Broker agreement</option>
          <option>Cargo insurance</option>
          <option>Carrier agreement</option>
          <option>Certificate of insurance</option>
          <option>Driver's license</option>
          <option>Fuel receipt</option>
          <option>Invoice</option>
          <option>Liability insurance</option>
          <option>Lumper receipt</option>
          <option>Operating authority</option>
          <option>Other</option>
          <option>Packing list</option>
          <option>Proof of delivery (POD)</option>
          <option>Rate confirmation</option>
          <option>References</option>
          <option>Scale receipt</option>
          <option>Shipper agreement</option>
          <option>Tax info</option>
          <option>Truck wash receipt</option>
          <option>Void cheque</option>
          <option>W-9</option>
          <option>Weight tickets</option>
        </select>
      </div>

      <!-- Documents list -->
      <div class="docs-card">
        <div class="docs-card-header">
          <h2 id="docsHeading">My Documents</h2>
          <span id="docCount" style="font-size:13px;color:var(--muted-foreground);"></span>
        </div>
        <div id="docsTableWrap">
          <div class="empty-state">
            <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon>
            <p>Loading documents…</p>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div id="toast"></div>

  <script>
  (function () {
    'use strict';

    var API = 'documents_data.php';
    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}

    if (!user || !user.id) {
      window.location.href = 'login';
      return;
    }

    var userId = user.id;
    var allDocs = [];

    // ── Toast ─────────────────────────────────────────────
    function toast(msg, type) {
      var el = document.getElementById('toast');
      el.textContent = msg;
      el.className = 'show ' + (type || '');
      clearTimeout(el._t);
      el._t = setTimeout(function () { el.className = ''; }, 3500);
    }

    // ── Format file size ─────────────────────────────────
    function fmtSize(bytes) {
      if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
      if (bytes >= 1024)    return (bytes / 1024).toFixed(0) + ' KB';
      return bytes + ' B';
    }

    // ── Format date ──────────────────────────────────────
    function fmtDate(dt) {
      if (!dt) return '—';
      return dt.slice(0, 16).replace('T', ' ');
    }

    // ── Icon for mime ─────────────────────────────────────
    function mimeIcon(mime) {
      if (!mime) return 'lucide:file';
      if (mime.startsWith('image/')) return 'lucide:image';
      if (mime === 'application/pdf') return 'lucide:file-text';
      if (mime.includes('word')) return 'lucide:file-text';
      if (mime.includes('excel') || mime.includes('sheet')) return 'lucide:table-2';
      return 'lucide:file';
    }

    // ── Render table ─────────────────────────────────────
    function renderTable(docs) {
      var wrap = document.getElementById('docsTableWrap');
      document.getElementById('docCount').textContent = docs.length + ' document' + (docs.length !== 1 ? 's' : '');

      if (!docs.length) {
        wrap.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:folder-open"></iconify-icon><p>No documents found.</p></div>';
        return;
      }

      var rows = docs.map(function (d) {
        var icon = mimeIcon(d.mime_type);
        var safe = function (s) { return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); };
        return '<tr>' +
          '<td><iconify-icon icon="' + icon + '" class="doc-icon"></iconify-icon>' + safe(d.original_name) + '</td>' +
          '<td><span class="category-badge">' + safe(d.category) + '</span></td>' +
          '<td>' + safe(d.notes || '—') + '</td>' +
          '<td>' + fmtSize(d.file_size) + '</td>' +
          '<td>' + fmtDate(d.uploaded_at) + '</td>' +
          '<td>' +
            '<a href="documents_data?action=download&doc_id=' + encodeURIComponent(d.id) + '&user_id=' + encodeURIComponent(userId) + '" ' +
               'class="btn btn-outline" style="padding:5px 10px;font-size:12px;margin-right:6px;" title="Download">' +
              '<iconify-icon icon="lucide:download" style="font-size:14px;vertical-align:middle;"></iconify-icon>' +
            '</a>' +
            '<button class="btn btn-outline" style="padding:5px 10px;font-size:12px;color:var(--destructive);border-color:var(--destructive);" ' +
              'onclick="deleteDoc(\'' + safe(d.id) + '\')" title="Delete">' +
              '<iconify-icon icon="lucide:trash-2" style="font-size:14px;vertical-align:middle;"></iconify-icon>' +
            '</button>' +
          '</td>' +
        '</tr>';
      });

      wrap.innerHTML = '<table class="docs-table">' +
        '<thead><tr>' +
          '<th>File name</th>' +
          '<th>Category</th>' +
          '<th>Notes</th>' +
          '<th>Size</th>' +
          '<th>Uploaded</th>' +
          '<th>Actions</th>' +
        '</tr></thead><tbody>' + rows.join('') + '</tbody></table>';
    }

    // ── Load documents ────────────────────────────────────
    function loadDocs() {
      fetch(API + '?action=list&user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) { toast('Failed to load documents.', 'error'); return; }
          allDocs = data.documents || [];
          applyFilters();
        })
        .catch(function () { toast('Network error.', 'error'); });
    }

    // ── Filters ───────────────────────────────────────────
    function applyFilters() {
      var q        = (document.getElementById('searchInput').value || '').toLowerCase();
      var category = document.getElementById('filterCategory').value;
      var filtered = allDocs.filter(function (d) {
        var matchQ = !q || (d.original_name || '').toLowerCase().includes(q) || (d.notes || '').toLowerCase().includes(q);
        var matchC = !category || d.category === category;
        return matchQ && matchC;
      });
      renderTable(filtered);
    }

    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('filterCategory').addEventListener('change', applyFilters);

    // ── File drop zone ────────────────────────────────────
    var dropZone   = document.getElementById('dropZone');
    var fileInput  = document.getElementById('fileInput');
    var fileNameEl = document.getElementById('selectedFileName');
    var selectedFile = null;

    dropZone.addEventListener('click', function () { fileInput.click(); });
    dropZone.addEventListener('dragover', function (e) { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', function () { dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', function (e) {
      e.preventDefault(); dropZone.classList.remove('dragover');
      if (e.dataTransfer.files.length) { selectedFile = e.dataTransfer.files[0]; fileNameEl.textContent = selectedFile.name; }
    });
    fileInput.addEventListener('change', function () {
      if (fileInput.files.length) { selectedFile = fileInput.files[0]; fileNameEl.textContent = selectedFile.name; }
    });

    // ── Upload ────────────────────────────────────────────
    document.getElementById('uploadBtn').addEventListener('click', function () {
      var category = document.getElementById('categorySelect').value;
      var notes    = document.getElementById('docNotes').value.trim();
      var statusEl = document.getElementById('uploadStatus');

      if (!category) { toast('Please select a document type.', 'error'); return; }
      if (!selectedFile) { toast('Please select a file to upload.', 'error'); return; }

      var btn = document.getElementById('uploadBtn');
      btn.disabled = true;
      statusEl.textContent = 'Uploading…';

      var fd = new FormData();
      fd.append('action', 'upload');
      fd.append('user_id', userId);
      fd.append('category', category);
      fd.append('notes', notes);
      fd.append('document', selectedFile);

      fetch(API, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          btn.disabled = false;
          statusEl.textContent = '';
          if (data.success) {
            toast('Document uploaded successfully.', 'success');
            selectedFile = null;
            fileNameEl.textContent = '';
            fileInput.value = '';
            document.getElementById('categorySelect').value = '';
            document.getElementById('docNotes').value = '';
            loadDocs();
          } else {
            toast(data.message || 'Upload failed.', 'error');
          }
        })
        .catch(function () {
          btn.disabled = false;
          statusEl.textContent = '';
          toast('Network error during upload.', 'error');
        });
    });

    // ── Delete ────────────────────────────────────────────
    window.deleteDoc = function (docId) {
      if (!confirm('Delete this document? This cannot be undone.')) return;
      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', doc_id: docId, user_id: userId }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) { toast('Document deleted.', 'success'); loadDocs(); }
          else toast(data.message || 'Delete failed.', 'error');
        })
        .catch(function () { toast('Network error.', 'error'); });
    };

    // ── Spin animation ────────────────────────────────────
    var style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

    loadDocs();
  })();
  </script>
</body>
</html>
