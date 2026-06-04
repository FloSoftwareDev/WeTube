<?php /* $videos: Video[] — set by VideoController::index() and ::search() */ ?>

<?php if (empty($videos)): ?>
    <p class="wt-empty">
        Couldn't find any videos for: <strong><?= htmlspecialchars($_GET['q'] ?? '') ?></strong>
        <?php if (AuthService::check()): ?>
            <a href="/WeTube/public/upload">Be the first to upload!</a>
        <?php endif; ?>
    </p>
<?php else: ?>
    <div class="video-grid">
        <?php foreach ($videos as $v): ?>
            <div class="video-card">
                <a href="/WeTube/public/watch/<?= $v->videoId ?>">
                    <div class="video-card__thumb-wrap">
                        <?php if ($v->thumbnailUrl): ?>
                            <img class="thumb" src="<?= htmlspecialchars($v->thumbnailUrl) ?>" alt="" onerror="thumbFallback(this)">
                        <?php else: ?>
                            <div class="thumb-blank"><span class="thumb-blank__icon">&#9654;</span></div>
                        <?php endif; ?>
                        <div class="video-card__progress"></div>
                    </div>
                    <div class="video-card__body">
                        <div class="video-card__avatar">
                            <?= htmlspecialchars(mb_strtoupper(mb_substr($v->username ?? '?', 0, 1))) ?>
                        </div>
                        <div class="video-card__info">
                            <h3 class="video-card__title"><?= htmlspecialchars($v->title) ?></h3>
                            <?php if ($v->username): ?>
                                <p class="video-card__channel"><?= htmlspecialchars($v->username) ?></p>
                            <?php endif; ?>
                            <p class="video-card__meta">
                                <?= number_format($v->viewCount) ?> views &middot;
                                <?= htmlspecialchars($v->createdAt) ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
