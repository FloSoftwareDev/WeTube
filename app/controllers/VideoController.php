<?php

class VideoController
{
    private const UPLOAD_DIR      = ROOT_PATH . '/public/uploads/videos/';
    private const THUMB_DIR       = ROOT_PATH . '/public/uploads/thumbnails/';
    private const UPLOAD_URL      = '/WeTube/public/uploads/videos/';
    private const THUMB_URL       = '/WeTube/public/uploads/thumbnails/';
    private const VIDEO_TYPES     = ['video/mp4', 'video/webm', 'video/ogg'];
    private const IMAGE_TYPES     = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_VIDEO_BYTES = 500 * 1024 * 1024;

    public function index(): void
    {
        $videos = Video::listLatest(20);
        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function show(string $id): void
    {
        $video = Video::findById((int) $id);
        if ($video === null) {
            http_response_code(404);
            echo '404 — Video not found';
            return;
        }

        Database::getInstance()->query(
            'UPDATE videos SET view_count = view_count + 1 WHERE video_id = ?',
            [$video->videoId]
        );
        $video->viewCount++;

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/show.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function upload(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpload();
            return;
        }

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/upload.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function search(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $videos = $q !== '' ? Video::search($q) : [];

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function edit(string $id): void
    {
        $video = $this->requireOwned((int) $id);

        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/videos/edit.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function update(string $id): void
    {
        $video = $this->requireOwned((int) $id);

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '') ?: null;

        if ($title === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            header('Location: /WeTube/public/video/' . $video->videoId . '/edit');
            exit;
        }

        $video->title       = $title;
        $video->description = $description;

        $newThumb = $this->saveThumbnail($_FILES['thumbnail'] ?? null);
        if ($newThumb !== null) {
            $video->thumbnailUrl = $newThumb;
        }

        $video->save();

        header('Location: /WeTube/public/watch/' . $video->videoId);
        exit;
    }
    public function profile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user      = User::findById((int) $_SESSION['user_id']);
            $user->bio = trim($_POST['bio'] ?? '') ?: null;
            $user->save();
            header('Location: /WeTube/public/profile');
            exit;
        }

        $user   = User::findById((int) $_SESSION['user_id']);
        $videos = Video::listByUser((int) $_SESSION['user_id']);
        include VIEWS_PATH . '/layouts/header.php';
        include VIEWS_PATH . '/profile/index.php';
        include VIEWS_PATH . '/layouts/footer.php';
    }

    public function destroy(string $id): void {}
    public function like(string $id): void {}

    private function requireOwned(int $id): Video
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

    private function handleUpload(): void
    {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '') ?: null;

        if ($title === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $file = $_FILES['video'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Please select a video file.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        if ($file['size'] > self::MAX_VIDEO_BYTES) {
            $_SESSION['flash_error'] = 'File exceeds the 500 MB limit.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::VIDEO_TYPES, true)) {
            $_SESSION['flash_error'] = 'Only MP4, WebM, and OGG files are accepted.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], self::UPLOAD_DIR . $filename)) {
            $_SESSION['flash_error'] = 'Upload failed — could not write the file.';
            header('Location: /WeTube/public/upload');
            exit;
        }

        $video              = new Video();
        $video->userId      = (int) $_SESSION['user_id'];
        $video->title       = $title;
        $video->description = $description;
        $video->url         = self::UPLOAD_URL . $filename;
        $video->thumbnailUrl = $this->saveThumbnail($_FILES['thumbnail'] ?? null);
        $video->durationSec = 0;
        $video->save();

        header('Location: /WeTube/public/watch/' . $video->videoId);
        exit;
    }

    private function saveThumbnail(?array $thumb): ?string
    {
        if (!$thumb || $thumb['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $mime = mime_content_type($thumb['tmp_name']);
        if (!in_array($mime, self::IMAGE_TYPES, true)) {
            return null;
        }

        $ext      = strtolower(pathinfo($thumb['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($thumb['tmp_name'], self::THUMB_DIR . $filename)) {
            return null;
        }

        return self::THUMB_URL . $filename;
    }
}
