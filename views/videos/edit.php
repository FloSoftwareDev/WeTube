<?php /* $video: Video, $error: ?string */ ?>
<div class="wt-form-page">
    <h1 class="wt-form-page__title">Edit video</h1>

    <?php if ($error): ?>
        <p class="wt-form__error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/WeTube/public/video/<?= $video->videoId ?>"
          enctype="multipart/form-data">
        <div class="wt-form__group">
            <label class="wt-form__label">Title *</label>
            <input type="text" name="title" required maxlength="200"
                   value="<?= htmlspecialchars($video->title) ?>"
                   class="wt-form__input">
        </div>
        <div class="wt-form__group">
            <label class="wt-form__label">Description</label>
            <textarea name="description" rows="5"
                      class="wt-form__textarea"><?= htmlspecialchars($video->description ?? '') ?></textarea>
        </div>
        <div class="wt-form__group">
            <label class="wt-form__label">Replace thumbnail <span class="wt-form__optional">(optional)</span></label>
            <?php if ($video->thumbnailUrl): ?>
                <img src="<?= htmlspecialchars($video->thumbnailUrl) ?>"
                     class="wt-form__thumb-preview" alt="">
            <?php endif; ?>
            <span class="wt-form__hint">JPEG / PNG / WebP</span>
            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="wt-form__file">
        </div>
        <div class="wt-form__actions">
            <button type="submit" class="wt-btn wt-btn--primary">Save changes</button>
            <a href="/WeTube/public/watch/<?= $video->videoId ?>" class="wt-btn wt-btn--ghost">Cancel</a>
        </div>
    </form>
</div>
