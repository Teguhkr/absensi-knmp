self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: 'Pengingat Presensi',
                body: event.data.text()
            };
        }
    }

    var title = data.title || 'Pengingat Presensi';
    var options = {
        body: data.body || 'Jangan lupa untuk melakukan presensi hari ini!',
        icon: data.icon || '/logo-knmp.png',
        badge: data.badge || '/logo-knmp.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.data?.url || '/pegawai'
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/pegawai/absensi-saya';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // Cari apakah ada tab yang sudah terbuka dengan URL yang sama
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // Jika tidak ada tab yang sudah terbuka, buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
