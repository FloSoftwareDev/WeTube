<?php
/*
 * Variables set by VideoController::show():
 *   $video        — Video
 *   $comments     — Comment[] (top-level, newest first)
 *   $replies      — array<int, Comment[]> keyed by parent commentId
 *   $currentUser  — User|null (logged-in user, or null)
 *   $commentError — string|null (flash error from a failed post)
 */
?>
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

    <section class="wt-comments">
        <h2 class="wt-comments__heading"><?= count($comments) ?> comments</h2>

        <?php if (AuthService::check()): ?>
            <?php if ($commentError): ?>
                <p class="wt-comments__error"><?= htmlspecialchars($commentError) ?></p>
            <?php endif; ?>
            <form method="POST"
                  action="/WeTube/public/video/<?= $video->videoId ?>/comment"
                  class="wt-comments__form">
                <textarea name="content" rows="2" required
                          placeholder="Add a comment&hellip;"
                          class="wt-comments__textarea"></textarea>
                <div class="wt-comments__form-actions">
                    <button type="submit" class="wt-comments__submit">Comment</button>
                </div>
            </form>
        <?php else: ?>
            <p class="wt-comments__signin">
                <a href="#" onclick="openModal('login');return false;">Log in</a>
                to leave a comment.
            </p>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
            <article class="wt-comment">
                <div class="wt-comment__avatar">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($c->username ?? '?', 0, 1))) ?>
                </div>
                <div class="wt-comment__body">
                    <div class="wt-comment__meta">
                        <span class="wt-comment__author"><?= htmlspecialchars($c->username ?? '[deleted]') ?></span>
                        <span class="wt-comment__time"><?= htmlspecialchars($c->createdAt) ?></span>
                    </div>
                    <p class="wt-comment__content"><?= nl2br(htmlspecialchars($c->content)) ?></p>

                    <div class="wt-comment__actions">
                        <?php if (AuthService::check()): ?>
                            <button type="button" class="wt-comment__action"
                                    onclick="document.getElementById('reply-<?= $c->commentId ?>').classList.toggle('wt-comment__reply--open')">
                                Reply
                            </button>
                        <?php endif; ?>
                        <?php if ($c->canDelete($currentUser, $video->userId)): ?>
                            <form method="POST"
                                  action="/WeTube/public/comment/<?= $c->commentId ?>/delete"
                                  class="wt-comment__delete"
                                  onsubmit="return confirm('Delete this comment?');">
                                <button type="submit" class="wt-comment__action wt-comment__action--danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if (AuthService::check()): ?>
                        <form id="reply-<?= $c->commentId ?>"
                              method="POST"
                              action="/WeTube/public/comment/<?= $c->commentId ?>/reply"
                              class="wt-comment__reply">
                            <textarea name="content" rows="2" required
                                      placeholder="Reply&hellip;"
                                      class="wt-comments__textarea"></textarea>
                            <div class="wt-comments__form-actions">
                                <button type="submit" class="wt-comments__submit">Reply</button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($replies[$c->commentId])): ?>
                        <div class="wt-comment__replies">
                            <?php foreach ($replies[$c->commentId] as $r): ?>
                                <article class="wt-comment wt-comment--reply">
                                    <div class="wt-comment__avatar">
                                        <?= htmlspecialchars(mb_strtoupper(mb_substr($r->username ?? '?', 0, 1))) ?>
                                    </div>
                                    <div class="wt-comment__body">
                                        <div class="wt-comment__meta">
                                            <span class="wt-comment__author"><?= htmlspecialchars($r->username ?? '[deleted]') ?></span>
                                            <span class="wt-comment__time"><?= htmlspecialchars($r->createdAt) ?></span>
                                        </div>
                                        <p class="wt-comment__content"><?= nl2br(htmlspecialchars($r->content)) ?></p>
                                        <?php if ($r->canDelete($currentUser, $video->userId)): ?>
                                            <div class="wt-comment__actions">
                                                <form method="POST"
                                                      action="/WeTube/public/comment/<?= $r->commentId ?>/delete"
                                                      class="wt-comment__delete"
                                                      onsubmit="return confirm('Delete this reply?');">
                                                    <button type="submit" class="wt-comment__action wt-comment__action--danger">Delete</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>
