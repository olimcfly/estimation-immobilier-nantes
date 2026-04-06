</main>
</div>
<footer class="border-t bg-[var(--admin-header-bg)]" style="border-color: var(--admin-border)">
    <div class="mx-auto grid max-w-[1700px] gap-4 px-4 py-5 text-xs text-slate-500 md:grid-cols-2 md:px-6">
        <div>© <?= date('Y') ?> <?= htmlspecialchars((string) SITE_NAME, ENT_QUOTES, 'UTF-8') ?> · Admin SaaS premium</div>
        <div class="md:text-right">
            <p class="mb-1 font-semibold uppercase tracking-wide">Ressources</p>
            <div class="flex flex-wrap gap-3 md:justify-end">
                <?php foreach (($adminResources ?? []) as $resource): ?>
                    <a href="<?= htmlspecialchars((string) ($resource['href'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>" class="underline-offset-2 hover:underline">
                        <?= htmlspecialchars((string) ($resource['label'] ?? 'Ressource'), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</footer>
<script>
    (function () {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');
        const submenuToggles = document.querySelectorAll('[data-submenu-toggle]');
        const themeToggle = document.getElementById('theme-toggle');
        const compactToggle = document.getElementById('sidebar-compact-toggle');
        const notifToggle = document.getElementById('notif-toggle');
        const notifPanel = document.getElementById('notif-panel');
        const notifList = document.getElementById('notif-list');
        const body = document.body;

        if (sidebar && overlay && toggle) {
            const openSidebar = function () {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            };

            const closeSidebar = function () {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', function () {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                    return;
                }
                closeSidebar();
            });

            overlay.addEventListener('click', closeSidebar);
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    overlay.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    sidebar.classList.remove('-translate-x-full');
                    return;
                }
                sidebar.classList.add('-translate-x-full');
            });
        }

        submenuToggles.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-submenu-toggle');
                const target = targetId ? document.getElementById(targetId) : null;
                if (!target) {
                    return;
                }

                const expanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                target.classList.toggle('hidden', expanded);
            });
        });

        if (themeToggle && body) {
            const storedTheme = window.localStorage.getItem('admin-theme');
            if (storedTheme === 'dark' || storedTheme === 'light') {
                body.setAttribute('data-theme', storedTheme);
                themeToggle.setAttribute('aria-pressed', storedTheme === 'dark' ? 'true' : 'false');
            }

            themeToggle.addEventListener('click', function () {
                const currentTheme = body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                body.setAttribute('data-theme', nextTheme);
                window.localStorage.setItem('admin-theme', nextTheme);
                themeToggle.setAttribute('aria-pressed', nextTheme === 'dark' ? 'true' : 'false');
            });
        }

        if (compactToggle && body) {
            const storedCompactMode = window.localStorage.getItem('admin-sidebar-compact') === 'true';
            body.classList.toggle('sidebar-compact', storedCompactMode);
            compactToggle.setAttribute('aria-pressed', storedCompactMode ? 'true' : 'false');

            compactToggle.addEventListener('click', function () {
                const nextState = !body.classList.contains('sidebar-compact');
                body.classList.toggle('sidebar-compact', nextState);
                window.localStorage.setItem('admin-sidebar-compact', nextState ? 'true' : 'false');
                compactToggle.setAttribute('aria-pressed', nextState ? 'true' : 'false');
            });
        }

        if (notifToggle && notifPanel && notifList) {
            const notifications = (window.ADMIN_MOCK_DATA && Array.isArray(window.ADMIN_MOCK_DATA.notifications))
                ? window.ADMIN_MOCK_DATA.notifications
                : [];

            notifList.innerHTML = notifications.map(function (item) {
                return '<article class="rounded-lg border p-2" style="border-color: var(--admin-border)">' +
                    '<p class="text-xs font-semibold text-slate-500">' + item.time + '</p>' +
                    '<p class="mt-1 text-sm font-semibold">' + item.title + '</p>' +
                    '<p class="text-xs text-slate-500">' + item.detail + '</p>' +
                '</article>';
            }).join('');

            notifToggle.addEventListener('click', function () {
                notifPanel.classList.toggle('hidden');
            });

            document.addEventListener('click', function (event) {
                if (!notifPanel.contains(event.target) && event.target !== notifToggle) {
                    notifPanel.classList.add('hidden');
                }
            });
        }
    })();
</script>
</body>
</html>
