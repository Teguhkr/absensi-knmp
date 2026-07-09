@auth
<script>
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js')
            .then(function (registration) {
                initPushSubscription(registration);
            })
            .catch(function (error) {
                console.error('Service Worker registration failed:', error);
            });
    }

    function initPushSubscription(registration) {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    subscribeUser(registration);
                }
            });
        } else if (Notification.permission === 'granted') {
            subscribeUser(registration);
        }
    }

    function subscribeUser(registration) {
        const vapidPublicKey = "{{ env('VAPID_PUBLIC_KEY') }}";
        if (!vapidPublicKey) {
            return;
        }

        const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);

        registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
        .then(function (subscription) {
            sendSubscriptionToBackend(subscription);
        })
        .catch(function (err) {
            console.error('Failed to subscribe the user to push notifications:', err);
        });
    }

    function sendSubscriptionToBackend(subscription) {
        const csrfToken = "{{ csrf_token() }}";
        
        fetch('/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(subscription)
        })
        .catch(error => {
            console.error('Error saving push subscription to database:', error);
        });
    }
</script>
@endauth
