<?php
/*
 * Search results page.
 * Variables set by SearchController::index():
 *   $videos       Video[]  — the current page of results
 *   $searchQuery  string   — the term that was searched
 *   $searchSort   string   — active sort key (newest|oldest|views)
 *   $currentPage  int      — 1-based page number
 *   $totalPages   int      — total number of pages
 *   $totalCount   int      — total number of matching videos
 */

// Build a /search URL that keeps the query + sort and swaps in a page number.
$pageUrl = function ($page) use ($searchQuery, $searchSort) {
    return '/WeTube/public/search?' . http_build_query([
        'q'    => $searchQuery,
        'sort' => $searchSort,
        'page' => $page,
    ]);
};

$sortLabels = [
    'newest' => 'Newest first',
    'oldest' => 'Oldest first',
    'views'  => 'Most viewed',
];
?>

<?php if ($searchQuery === ''): ?>

    <p class="wt-empty">Type something in the search bar to find videos.</p>

<?php else: ?>

    <div class="search-bar">
        <div class="search-bar__info">
            <?php if ($totalCount > 0): ?>
                <strong><?= number_format($totalCount) ?></strong>
                result<?= $totalCount === 1 ? '' : 's' ?> for
                &ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;
            <?php else: ?>
                No results for &ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;
            <?php endif; ?>
        </div>

        <?php if ($totalCount > 0): ?>
            <form method="GET" action="/WeTube/public/search" class="search-bar__sort">
                <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                <label for="sort">Sort by</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <?php foreach ($sortLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $searchSort === $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($videos)): ?>
        <p class="wt-empty">Try a different search term.</p>
    <?php else: ?>

        <?php include VIEWS_PATH . '/videos/index.php'; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a class="pagination__link" href="<?= $pageUrl($currentPage - 1) ?>">&larr; Prev</a>
                <?php else: ?>
                    <span class="pagination__link pagination__link--off">&larr; Prev</span>
                <?php endif; ?>

                <span class="pagination__status">
                    Page <?= $currentPage ?> of <?= $totalPages ?>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a class="pagination__link" href="<?= $pageUrl($currentPage + 1) ?>">Next &rarr;</a>
                <?php else: ?>
                    <span class="pagination__link pagination__link--off">Next &rarr;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

<?php endif; ?>
