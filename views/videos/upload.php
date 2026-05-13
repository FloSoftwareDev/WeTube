<div class="wt-form-page">
    <h1 class="wt-form-page__title">Upload a video</h1>

    <?php if (!empty($error)): ?>
        <p class="wt-form__error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/WeTube/public/upload" enctype="multipart/form-data">
        <div class="wt-form__group">
            <label class="wt-form__label">Title *</label>
            <input type="text" name="title" required maxlength="200" class="wt-form__input">
        </div>
        <div class="wt-form__group">
            <label class="wt-form__label">Description</label>
            <textarea name="description" rows="5" class="wt-form__textarea"></textarea>
        </div>
        <div class="wt-form__group">
            <label class="wt-form__label">Video file *</label>
            <span class="wt-form__hint">MP4 / WebM / OGG &mdash; max 500&nbsp;MB</span>
            <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" required class="wt-form__file">
        </div>
        <div class="wt-form__group">
            <label class="wt-form__label">Thumbnail <span class="wt-form__optional">(optional)</span></label>
            <span class="wt-form__hint">JPEG / PNG / WebP</span>
            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="wt-form__file">
        </div>
        <div class="wt-form__actions">
            <button type="submit" class="wt-btn wt-btn--primary">Upload</button>
        </div>
    </form>
</div>
