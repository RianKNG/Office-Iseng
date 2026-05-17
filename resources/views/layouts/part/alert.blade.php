<ul class="navbar-nav ml-auto">
<style>
    /* Notifikasi Badge Animation */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    #notificationBadge {
        animation: pulse 2s infinite;
    }

    /* Dropdown Notifikasi */
    .dropdown-menu[aria-labelledby="notificationDropdown"] {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        min-width: 350px;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        white-space: normal;
    }

    .dropdown-item:not(:last-child) {
        border-bottom: 1px solid #e9ecef;
    }
</style>

<!-- Notifikasi Dropdown - Bootstrap 4 -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" 
       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell" style="font-size: 1.3rem;"></i>
        <span id="notificationBadge" 
              class="badge badge-danger position-absolute"
              style="top: 0; right: 0; font-size: 0.7rem; display: none;">
            0
        </span>
    </a>
    
    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" 
         aria-labelledby="notificationDropdown">
        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell me-2"></i>Notifikasi</span>
            <button class="btn btn-sm btn-link p-0" onclick="markAllAsRead()" style="font-size: 0.8rem;">
                Tandai dibaca
            </button>
        </h6>
        <div class="dropdown-divider"></div>
        
        <!-- Content akan diisi via JavaScript -->
        <div id="notificationContent" style="max-height: 400px; overflow-y: auto;">
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <small class="d-block mt-2">Memuat notifikasi...</small>
            </div>
        </div>
    </div>
</li>

@push('scripts')
<script>
// Auto-refresh notifikasi setiap 30 detik
let notificationInterval;

function fetchNotifications() {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.total);
            updateNotificationContent(data);
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

function updateNotificationBadge(total) {
    const badge = document.getElementById('notificationBadge');
    if (total > 0) {
        badge.textContent = total > 99 ? '99+' : total;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

function updateNotificationContent(data) {
    const content = document.getElementById('notificationContent');
    
    if (data.total === 0) {
        content.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                <p class="mb-0 mt-2">Tidak ada notifikasi baru</p>
            </div>
        `;
        return;
    }
    
    // Fetch detail notifikasi
    fetch('/api/notifications/details')
        .then(response => response.json())
        .then(details => {
            let html = '';
            
            if (details.disposisi && details.disposisi.length > 0) {
                details.disposisi.forEach(disposisi => {
                    const badgeColor = disposisi.status === 'pending' ? 'warning' : 'info';
                    const statusText = disposisi.status === 'pending' ? 'BARU' : 'DIBACA';
                    
                    html += `
                        <a class="dropdown-item" href="/disposisi/${disposisi.id}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 mr-2">
                                    <span class="badge badge-${badgeColor}">${statusText}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="font-weight-bold">${disposisi.letter.nomor_surat}</small>
                                    <p class="mb-1 text-truncate" style="max-width: 250px;">
                                        ${disposisi.letter.perihal}
                                    </p>
                                    <small class="text-muted">
                                        Dari: ${disposisi.dari.nama_lengkap}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        ${timeAgo(disposisi.created_at)}
                                    </small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                });
            }
            
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-3 text-danger">
                    <small>Gagal memuat notifikasi</small>
                </div>
            `;
        });
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Baru saja';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} menit yang lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} jam yang lalu`;
    const days = Math.floor(hours / 24);
    return `${days} hari yang lalu`;
}

function markAllAsRead() {
    // Implementasi untuk tandai semua sudah dibaca
    fetchNotifications();
}

// Start auto-refresh
document.addEventListener('DOMContentLoaded', function() {
    fetchNotifications(); // Fetch pertama kali
    notificationInterval = setInterval(fetchNotifications, 30000); // Refresh setiap 30 detik
});

// Refresh saat dropdown dibuka
document.getElementById('notificationDropdown').addEventListener('click', function(e) {
    e.preventDefault();
    fetchNotifications();
});
</script>
@endpush