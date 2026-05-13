<?php /* $video: Video — set by VideoController::show() */ ?>
<div class="wt-player">
    <div class="wt-player__container">
        <video src="<?= htmlspecialchars($video->url) ?>" controls>
            Your browser does not support the video element.
        </video>
    </div>

    <div class="wt-player__meta">
        <div class="wt-player__title-row">
            <h1 class="wt-player__title"><?= htmlspecialchars($video->title) ?></h1>
            <?php if (AuthService::check() && $video->userId === (int) $_SESSION['user_id']): ?>
                <a href="/WeTube/public/video/<?= $video->videoId ?>/edit"
                   class="wt-player__edit-link">Edit</a>
            <?php endif; ?>
        </div>
        <?php if ($video->username): ?>
            <div class="wt-player__channel-row">
                <div class="wt-player__avatar">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($video->username, 0, 1))) ?>
                </div>
                <span class="wt-player__channel"><?= htmlspecialchars($video->username) ?></span>
            </div>
        <?php endif; ?>
        <p class="wt-player__views">
            <?= number_format($video->viewCount) ?> views &middot;
            <?= htmlspecialchars($video->createdAt) ?>
        </p>

        <?php if ($video->description): ?>
            <p class="wt-player__desc"><?= htmlspecialchars($video->description) ?></p>
        <?php endif; ?>
    </div>
</div>
