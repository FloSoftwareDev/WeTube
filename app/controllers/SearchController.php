<?php
/**
 * SearchController — the /search page.
 *
 * Reads the search term (?q=), the sort order (?sort=) and the page
 * number (?page=) from the query string, then asks the Video model for
 * the matching slice of results plus the total count for pagination.
 */
class SearchController
{
    // How many results to show per page.
    const PER_PAGE = 12;

    // Sort keys we accept from the URL (anything else falls back to 'newest').
    const SORTS = ['newest', 'oldest', 'views'];

    public function index()
    {
        $q    = isset($_GET['q']) ? trim($_GET['q']) : '';
        $sort = isset($_GET['sort']) && in_array($_GET['sort'], self::SORTS)
            ? $_GET['sort']
            : 'newest';
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

        if ($q === '') {
            $videos = [];
            $total  = 0;
        } else {
            $offset = ($page - 1) * self::PER_PAGE;
            $videos = Video::search($q, $sort, self::PER_PAGE, $offset);
            $total  = Video::countSearch($q);
        }

        // Values the view needs.
        $searchQuery = $q;
        $searchSort  = $sort;
        $currentPage = $page;
        $totalPages  = (int) ceil($total / self::PER_PAGE);
        $totalCount  = $total;

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/search/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }
}
