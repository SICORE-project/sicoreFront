@props([])

<div class="notif-bell" x-data="notificationBell()" x-init="init()">
    <button type="button" @click.stop="toggle()" class="notif-bell__trigger" aria-label="Notifications">
        <i class="fa-solid fa-bell" aria-hidden="true"></i>
        <span x-show="unreadCount > 0" x-text="unreadCount" class="notif-bell__badge"></span>
    </button>

    <div x-show="open" @click.outside="open = false" @click.stop
         class="notif-bell__menu" style="display: none;">

        <div class="notif-bell__header">
            <span>Notifications</span>
            <button type="button" @click="markAllAsRead()" class="notif-bell__mark-all">
                Tout marquer comme lu
            </button>
        </div>

        <ul class="notif-bell__list">
            <template x-for="notif in notifications" :key="notif.id">
                <li @click="onClickNotification(notif)"
                    class="notif-bell__item"
                    :class="{ 'notif-bell__item--unread': !notif.lu }">
                    <p class="notif-bell__title" x-text="notif.titre"></p>
                    <p class="notif-bell__message" x-text="notif.message"></p>
                    <p class="notif-bell__date" x-text="notif.date"></p>
                </li>
            </template>

            <li x-show="notifications.length === 0" class="notif-bell__empty">
                Aucune notification.
            </li>
        </ul>
    </div>
</div>

<style>
.notif-bell {
    position: relative;
    display: inline-flex;
}

.notif-bell__trigger {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    padding: 0.4rem;
}

.notif-bell__trigger:focus {
    outline: none;
}

.notif-bell__badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #dc2626;
    color: #fff;
    font-size: 0.65rem;
    line-height: 1;
    border-radius: 999px;
    padding: 2px 5px;
}

.notif-bell__menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 320px;
    max-width: 90vw;
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 1000;
}

.notif-bell__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.notif-bell__header span {
    font-weight: 600;
}

.notif-bell__mark-all {
    background: none;
    border: none;
    color: #2563eb;
    font-size: 0.8rem;
    cursor: pointer;
}

.notif-bell__list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 24rem;
    overflow-y: auto;
}

.notif-bell__item {
    padding: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}

.notif-bell__item:hover {
    background: #f8fafc;
}

.notif-bell__item--unread {
    background: #eff6ff;
}

.notif-bell__title {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
}

.notif-bell__message {
    font-size: 0.875rem;
    color: #4b5563;
    margin: 0.15rem 0 0;
}

.notif-bell__date {
    font-size: 0.75rem;
    color: #9ca3af;
    margin: 0.25rem 0 0;
}

.notif-bell__empty {
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}
</style>

<script>
function notificationBell() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],

        init() {
            this.fetchUnreadCount();
            this.fetchNotifications();
            setInterval(() => this.fetchUnreadCount(), 30000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) this.fetchNotifications();
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]').content;
        },

        async handle(res) {
            if (res.status === 401) {
                const json = await res.json();
                window.location.href = json.redirect || '{{ route('login') }}';
                return null;
            }
            return res.json();
        },

        async fetchUnreadCount() {
            const res = await fetch('{{ route('indemnites.notifications.unread-count') }}');
            const json = await this.handle(res);
            if (json) this.unreadCount = json.count;
        },

        async fetchNotifications() {
            const res = await fetch('{{ route('indemnites.notifications.index') }}');
            const json = await this.handle(res);
            if (json) this.notifications = json.data;
        },

        async onClickNotification(notif) {
            if (!notif.lu) await this.markAsRead(notif.id);
            if (notif.url) window.location.href = notif.url;
        },

        async markAsRead(id) {
            const url = '{{ route('indemnites.notifications.read', ':id') }}'.replace(':id', id);
            await fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': this.csrf() } });
            this.fetchUnreadCount();
            this.fetchNotifications();
        },

        async markAllAsRead() {
            await fetch('{{ route('indemnites.notifications.read-all') }}', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': this.csrf() },
            });
            this.fetchUnreadCount();
            this.fetchNotifications();
        },
    }
}
</script>