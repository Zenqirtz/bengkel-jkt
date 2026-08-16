const NOTIF_URL = document.getElementById('notif-config')?.dataset.url;
const MARK_ALL_URL = document.getElementById('notif-config')?.dataset.markAllUrl;
const MARK_READ_URL = document.getElementById('notif-config')?.dataset.markReadUrl;
const CSRF_TOKEN = document.getElementById('notif-config')?.dataset.csrf;

function renderNotifications(data, unread) {
  const list = document.getElementById('notif-list');
  const count = document.getElementById('notif-count');
  const dot = document.getElementById('notif-dot');

  if (!list || !count || !dot) return;

  count.textContent = unread + ' Baru';
  // dot.classList.toggle('d-none', unread === 0);
  // SESUDAH
  if (unread === 0) {
    dot.classList.add('d-none');
    dot.style.cssText = '';
  } else {
    dot.classList.remove('d-none');
    dot.classList.remove('badge-dot');
    dot.classList.add('rounded-pill');
    dot.textContent = unread > 99 ? '99+' : unread;

    // Pindahkan badge ke nav-item (parent dari button) agar tidak terpotong
    const navItem = dot.closest('.nav-item');
    if (navItem) {
      navItem.style.position = 'relative';
      navItem.appendChild(dot); // pindah ke nav-item
    }

    const size = unread > 9 ? '18px' : '14px';
    dot.style.cssText = `position:absolute; top: 0px; right: 0px; font-size:7px; width:${size}; height:${size}; display:flex; align-items:center; justify-content:center; z-index:9999; background:#ea5455; color:#fff; border-radius:50%; line-height:1;`;
  }

  if (data.length === 0) {
    list.innerHTML = `
            <li class="list-group-item text-center text-muted py-4">
                Tidak ada notifikasi
            </li>`;
    return;
  }

  list.innerHTML = data
    .map(
      n => `
        <li class="list-group-item list-group-item-action dropdown-notifications-item ${n.is_read ? '' : 'marked-as-unread'}"
            data-id="${n.id}" data-url="${n.url ?? ''}">
            <div class="d-flex">
                <div class="flex-grow-1">
                    <h6 class="small mb-1">${n.title}</h6>
                    <small class="mb-1 d-block text-body">${n.message}</small>
                    <small class="text-body-secondary">${n.time_ago}</small>
                </div>
                <div class="flex-shrink-0 dropdown-notifications-actions">
                    ${
                      !n.is_read
                        ? `
                    <a href="javascript:void(0)" class="dropdown-notifications-read btn-mark-read" data-id="${n.id}">
                        <span class="badge badge-dot"></span>
                    </a>`
                        : ''
                    }
                </div>
            </div>
        </li>
    `
    )
    .join('');

  list.querySelectorAll('.list-group-item[data-id]').forEach(item => {
    item.addEventListener('click', function (e) {
      if (e.target.closest('.btn-mark-read')) return;
      markRead(this.dataset.id, this.dataset.url);
    });
  });

  list.querySelectorAll('.btn-mark-read').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      markRead(this.dataset.id, null);
    });
  });
}

function markRead(id, redirectUrl) {
  const url = MARK_READ_URL.replace(':id', id);
  fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' }
  })
    .then(() => {
      loadNotifications();
      if (redirectUrl) window.location.href = redirectUrl;
    })
    .catch(err => console.error('Mark read error:', err));
}

function markAllRead(callback) {
  if (!MARK_ALL_URL) return;
  fetch(MARK_ALL_URL, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' }
  })
    .then(() => {
      if (callback) callback();
    })
    .catch(err => console.error('Mark all read error:', err));
}

function loadNotifications() {
  if (!NOTIF_URL) return;
  fetch(NOTIF_URL)
    .then(res => {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(({ data, unread }) => renderNotifications(data, unread))
    .catch(err => console.error('Notif error:', err));
}

// Tombol mark all read di navbar dropdown
document.getElementById('btn-read-all')?.addEventListener('click', function () {
  markAllRead(() => loadNotifications());
});

// Tombol mark all read di halaman semua notifikasi
document.getElementById('btn-read-all-page')?.addEventListener('click', function () {
  markAllRead(() => location.reload());
});

loadNotifications();
setInterval(loadNotifications, 30000);
