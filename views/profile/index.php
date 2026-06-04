<?php /* $user: User, $videos: Video[] — set by VideoController::profile() */ ?>

<div class="wt-profile">

    <div class="wt-profile__header">
        <div class="wt-profile__avatar">
            <?php if ($user->profilePicture): ?>
                <img src="<?= htmlspecialchars($user->profilePicture) ?>"
                     class="wt-profile__avatar-img" alt="">
            <?php else: ?>
                <span class="wt-profile__avatar-initial">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($user->username, 0, 1))) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="wt-profile__info">
            <h1 class="wt-profile__name"><?= htmlspecialchars($user->username) ?></h1>
            <div class="wt-profile__stats">
                <span class="wt-profile__stat">
                    <span class="wt-profile__stat-value"><?= count($videos) ?></span>
                    <span class="wt-profile__stat-label"><?= count($videos) === 1 ? 'video' : 'videos' ?></span>
                </span>
                <?php if ($user->createdAt): ?>
                    <span class="wt-profile__stat-sep">&middot;</span>
                    <span class="wt-profile__stat">
                        <span class="wt-profile__stat-label">Joined</span>
                        <span class="wt-profile__stat-value"><?= htmlspecialchars(date('M Y', strtotime($user->createdAt))) ?></span>
                    </span>
                <?php endif; ?>
            </div>
            <?php if ($user->bio): ?>
                <p class="wt-profile__bio"><?= htmlspecialchars($user->bio) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="wt-profile__edit-bio">
        <form method="POST" action="/WeTube/public/profile">
            <label class="wt-profile__edit-bio-label">Bio</label>
            <textarea name="bio" class="wt-form__textarea wt-profile__bio-textarea"
                      maxlength="300"
                      placeholder="Tell people a little about yourself..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
            <button type="submit" class="wt-btn wt-btn--primary">Save bio</button>
        </form>
    </div>

    <div class="wt-profile__section">
        <h2 class="wt-profile__section-title">Your videos</h2>

        <?php if (empty($videos)): ?>
            <p class="wt-empty">
                You haven't uploaded any videos yet.
                <a href="/WeTube/public/upload">Upload your first one!</a>
            </p>
        <?php else: ?>
            <div class="video-grid">
                <?php foreach ($videos as $v): ?>
                    <div class="video-card">
                        <a href="/WeTube/public/watch/<?= $v->videoId ?>" class="video-card__thumb-link">
                            <div class="video-card__thumb-wrap">
                                <?php if ($v->thumbnailUrl): ?>
                                    <img class="thumb" src="<?= htmlspecialchars($v->thumbnailUrl) ?>" alt="" onerror="thumbFallback(this)">
                                <?php else: ?>
                                    <div class="thumb-blank"><span class="thumb-blank__icon">&#9654;</span></div>
                                <?php endif; ?>
                                <div class="video-card__progress"></div>
                            </div>
                        </a>
                        <div class="video-card__body">
                            <a href="/WeTube/public/watch/<?= $v->videoId ?>" class="video-card__title-link">
                                <h3 class="video-card__title"><?= htmlspecialchars($v->title) ?></h3>
                            </a>
                            <p class="video-card__meta">
                                <?= number_format($v->viewCount) ?> views &middot;
                                <?= htmlspecialchars($v->createdAt) ?>
                            </p>
                            <a href="/WeTube/public/video/<?= $v->videoId ?>/edit" class="video-card__edit-btn">
                                Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
