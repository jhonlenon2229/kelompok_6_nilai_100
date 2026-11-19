const $ = (s, r = document) => r.querySelector(s);

// Konfirmasi logout
const lo = $('#lo');
if (lo) lo.onclick = () => {
  const o = document.createElement('div'); o.className = 'ov show';
  o.innerHTML = '<div class="dlg c"><div class="dic">' + lo.querySelector('svg').outerHTML + '</div><h3>Logout</h3><p>Apakah anda ingin logout king?</p>' +
    '<div class="bar"><button class="btn y" id="no">Batal</button><button class="btn" id="yes">Ya, logout</button></div></div>';
  document.body.appendChild(o);
  o.onclick = e => { if (e.target == o) o.remove(); };
  $('#no').onclick = () => o.remove();
  $('#yes').onclick = () => location.href = lo.dataset.href;
};

// Modal tambah / edit
const m = $('#modal');
if (m) {
  const f = $('form', m);
  const open = r => {
    f.reset(); f.elements.id.value = r ? r.id : '';
    $('#mt').textContent = (r ? 'Edit ' : 'Tambah ') + f.dataset.t;
    [...f.elements].forEach(el => {
      if (!el.name || el.name == 'csrf' || el.name == 'id') return;
      if (r) el.value = r[el.name] ?? '';
      else if (el.type == 'date') el.value = new Date().toISOString().slice(0, 10);
    });
    m.classList.add('show');
  };
  document.querySelectorAll('[data-add]').forEach(b => b.onclick = () => open());
  document.querySelectorAll('[data-edit]').forEach(b => b.onclick = () => open(JSON.parse(b.dataset.edit)));
  m.onclick = e => { if (e.target == m || e.target.hasAttribute('data-close')) m.classList.remove('show'); };
}

// Pencarian tabel
const q = $('#q');
if (q) q.oninput = () => {
  const v = q.value.toLowerCase();
  document.querySelectorAll('#tbl tbody tr').forEach(r => r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none');
};
