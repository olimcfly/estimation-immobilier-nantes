<?php

declare(strict_types=1);

if (defined('ADMIN_SIDEBAR_RENDERED')) {
    return;
}

define('ADMIN_SIDEBAR_RENDERED', true);

$currentPage = isset($currentPage) && is_string($currentPage) ? $currentPage : 'dashboard';
if (!isset($adminMenu) || !is_array($adminMenu)) {
    require_once __DIR__ . '/navigation.php';
}
?>
<aside id="admin-sidebar" class="fixed inset-y-16 left-0 z-40 w-[280px] shrink-0 border-r p-4 text-[var(--admin-sidebar-text)] transition-all duration-200 lg:static lg:inset-auto lg:translate-x-0 -translate-x-full" style="border-color: rgba(255,255,255,.08); background: var(--admin-sidebar-bg)">
    <div class="mb-4 flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wide text-[var(--admin-sidebar-muted)]" data-compact-hide>Navigation</span>
        <div class="flex items-center gap-2">
            <button type="button" id="sidebar-compact-toggle" class="focus-visible-ring hidden rounded-md px-2 py-1 text-xs font-semibold text-[var(--admin-sidebar-muted)] hover:bg-white/10 lg:inline-flex" aria-pressed="false">Compact</button>
            <button type="button" id="sidebar-close" class="focus-visible-ring rounded-md p-1 text-[var(--admin-sidebar-text)] hover:bg-slate-800 lg:hidden" aria-label="Fermer le menu latéral">✕</button>
        </div>
    </div>

    <nav class="space-y-2 text-sm" aria-label="Navigation principale">
        <?php foreach ($adminMenu as $index => $item): ?>
            <?php
            $isActive = $currentPage === $item['key'];
            $submenuId = 'submenu-' . $index;
            $hasChildren = !empty($item['children']) && is_array($item['children']);
            $badge = isset($item['badge']) ? (string) $item['badge'] : '';
            ?>
            <div>
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="focus-visible-ring group flex items-center gap-3 rounded-xl border px-3 py-2.5 font-medium transition <?= $isActive ? 'text-white shadow-sm' : 'text-[var(--admin-sidebar-text)] hover:bg-[var(--admin-sidebar-hover)]' ?>" style="<?= $isActive ? 'background: var(--admin-sidebar-active-bg); border-color: var(--admin-sidebar-accent);' : 'background: transparent; border-color: rgba(255,255,255,.12);' ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-base"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="truncate" data-compact-hide><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($badge !== ''): ?>
                        <span class="ml-auto rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-semibold" data-compact-hide><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($hasChildren): ?>
                    <button type="button" class="focus-visible-ring mt-1 flex w-full items-center justify-between rounded px-2 py-1 text-left text-xs font-semibold uppercase tracking-wide text-[var(--admin-sidebar-muted)] hover:bg-white/5 hover:text-white" data-submenu-toggle="<?= $submenuId ?>" aria-controls="<?= $submenuId ?>" aria-expanded="<?= $isActive ? 'true' : 'false' ?>" data-compact-hide>
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?> · modules
                        <span aria-hidden="true">▾</span>
                    </button>
                    <div id="<?= $submenuId ?>" class="ml-4 mt-1 space-y-1 border-l pl-3 <?= $isActive ? '' : 'hidden' ?>" style="border-color: rgba(255,255,255,.16)" data-submenu data-compact-hide>
                        <?php foreach ($item['children'] as $child): ?>
                            <?php $childBadge = isset($child['badge']) ? (string) $child['badge'] : ''; ?>
                            <a href="<?= htmlspecialchars($child['href'], ENT_QUOTES, 'UTF-8') ?>" class="focus-visible-ring flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-xs text-[var(--admin-sidebar-muted)] hover:bg-white/10 hover:text-white">
                                <span><?= htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($childBadge !== ''): ?>
                                    <span class="rounded-full bg-white/15 px-1.5 py-0.5 text-[10px] font-semibold"><?= htmlspecialchars($childBadge, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
</aside>
<main class="min-h-[calc(100vh-4rem)] flex-1 bg-[var(--admin-content-bg)] p-4 md:p-6">
