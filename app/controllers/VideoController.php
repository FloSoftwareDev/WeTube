<?php

/**
 * VideoController — handles all the pages that have to do with videos:
 * homepage, watch page, upload, edit, search, and the user's profile.
 */
class VideoController
{
    // Homepage — show the 20 newest videos.
    public function index()
    {
        $videos = Video::listLatest(20);
        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    // Watch page — show one video and count one new view.
    public function show($id)
    {
        $video = Video::findById((int) $id);
        if ($video === null) {
            http_response_code(404);
            echo '404 — Video not found';
            return;
        }

        // Add one view to the counter
        Database::query(
            'UPDATE videos SET view_count = view_count + 1 WHERE video_id = ?',
            [$video->videoId]
        );
        $video->viewCount = $video->viewCount + 1;

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/show.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    // Search page — show every video that matches the ?q= search term.
    public function search()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($q === '') {
            $videos = [];
        } else {
            $videos = Video::search($q);
        }

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    // Upload page. GET = show the form, POST = handle the uploaded file.
    public function upload()
    {
        $error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
        unset($_SESSION['flash_error']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include VIEWS_PATH . '/layouts/header.php';
            include VIEWS_PATH . '/videos/upload.php';
            include VIEWS_PATH . '/layouts/footer.php';
            return;
        }

        // ---- Form was submitted: validate everything ----
        $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        if ($description === '') {
            $description = null;
        }

        if ($title === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $file = isset($_FILES['video']) ? $_FILES['video'] : null;

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Please select a video file.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $maxBytes = 500 * 1024 * 1024;  // 500 MB
        if ($file['size'] > $maxBytes) {
            $_SESSION['flash_error'] = 'File exceeds the 500 MB limit.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedVideoTypes)) {
            $_SESSION['flash_error'] = 'Only MP4, WebM, and OGG files are accepted.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        // ---- Move the file from PHP's temp folder into our uploads folder ----
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('video_') . '.' . $ext;
        $target   = ROOT_PATH . '/public/uploads/videos/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $_SESSION['flash_error'] = 'Upload failed — could not write the file.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        // ---- Optional thumbnail ----
        $thumbnailUrl = $this->saveThumbnail();

        // ---- Save a new row in the videos table ----
        $video               = new Video();
        $video->userId       = (int) $_SESSION['user_id'];
        $video->title        = $title;
        $video->description  = $description;
        $video->url          = '/WeTube/public/uploads/videos/' . $filename;
        $video->thumbnailUrl = $thumbnailUrl;
        $video->durationSec  = 0;
        $video->save();

        header('Location: /WeTube/public/watch/' . $video->videoId);
        exit;
    }

    // Edit page — show the form to edit a video's title/description/thumbnail.
    public function edit($id)
    {
        $video = $this->findOwnedVideo((int) $id);

        $error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
        unset($_SESSION['flash_error']);

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/edit.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    // Save the edit form.
    public function update($id)
    {
        $video = $this->findOwnedVideo((int) $id);

        $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        if ($description === '') {
            $description = null;
        }

        if ($title === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            header('Location: /WeTube/public/video/' . $video->videoId . '/edit');
            exit;
        }

        $video->title       = $title;
        $video->description = $description;

        // If the user uploaded a new thumbnail, replace the old one
        $newThumb = $this->saveThumbnail();
        if ($newThumb !== null) {
            $video->thumbnailUrl = $newThumb;
        }

        $video->save();

        header('Location: /WeTube/public/watch/' . $video->videoId);
        exit;
    }

    // Profile page. GET = show profile, POST = save the bio text.
    public function profile()
    {
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = User::findById($userId);
            $bio  = isset($_POST['bio']) ? trim($_POST['bio']) : '';
            $user->bio = $bio === '' ? null : $bio;
            $user->save();
            header('Location: /WeTube/public/profile');
            exit;
        }

        $user   = User::findById($userId);
        $videos = Video::listByUser($userId);
        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/profile/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    // ---- Private helpers ----

    // Find a video and make sure the current user owns it.
    // Stops the script with 404 or 403 if not allowed.
    private function findOwnedVideo($id)
    {
        $video = Video::findById($id);
        if ($video === null) {
            http_response_code(404);
            echo '404 — Video not found';
            exit;
        }
        if ($video->userId !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            echo '403 — Forbidden';
            exit;
        }
        return $video;
    }

    // Save an optional thumbnail image. Returns the public URL of the saved
    // image, or null if no thumbnail was uploaded (or it was the wrong type).
    private function saveThumbnail()
    {
        $thumb = isset($_FILES['thumbnail']) ? $_FILES['thumbnail'] : null;

        if ($thumb === null || $thumb['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = mime_content_type($thumb['tmp_name']);
        if (!in_array($mime, $allowedImageTypes)) {
            return null;
        }

        $ext      = strtolower(pathinfo($thumb['name'], PATHINFO_EXTENSION));
        $filename = uniqid('thumb_') . '.' . $ext;
        $target   = ROOT_PATH . '/public/uploads/thumbnails/' . $filename;

        if (!move_uploaded_file($thumb['tmp_name'], $target)) {
            return null;
        }

        return '/WeTube/public/uploads/thumbnails/' . $filename;
    }
}
