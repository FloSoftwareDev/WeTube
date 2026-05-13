<?php

/**
 * Video model.
 *
 * Represents a row in the `videos` table. Use the static finders
 * to load existing videos, or `new Video()` + ->save() to create one.
 */
class Video
{
    public ?int    $videoId      = null;
    public int     $userId       = 0;
    public ?string $username     = null;
    public string  $title        = '';
    public ?string $description  = null;
    public string  $url          = '';
    public ?string $thumbnailUrl = null;
    public int     $durationSec  = 0;
    public int     $viewCount    = 0;
    public ?string $createdAt    = null;

    /**
     * Find a video by its primary key.
     */
    public static function findById(int $id): ?Video
    {
        $row = Database::getInstance()->fetchOne(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.video_id = ?',
            [$id]
        );
        return $row ? self::hydrate($row) : null;
    }

    /**
     * Get the most recent videos. Used on the homepage.
     */
    public static function listLatest(int $limit = 20): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             ORDER BY videos.created_at DESC LIMIT ?',
            [$limit]
        );
        return array_map([self::class, 'hydrate'], $rows);
    }

    /**
     * Insert a new video, or update the existing one.
     */
    public function save(): bool
    {
        $db = Database::getInstance();

        if ($this->videoId === null) {
            // INSERT
            $db->query(
                'INSERT INTO videos
                    (user_id, title, description, url, thumbnail_url, duration_sec)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $this->userId,
                    $this->title,
                    $this->description,
                    $this->url,
                    $this->thumbnailUrl,
                    $this->durationSec,
                ]
            );
            $this->videoId = $db->lastInsertId();
        } else {
            // UPDATE — only fields the owner is allowed to edit
            $db->query(
                'UPDATE videos
                 SET title = ?, description = ?, thumbnail_url = ?
                 WHERE video_id = ?',
                [
                    $this->title,
                    $this->description,
                    $this->thumbnailUrl,
                    $this->videoId,
                ]
            );
        }
        return true;
    }

    /**
     * Get all videos uploaded by a specific user, newest first.
     */
    public static function listByUser(int $userId, int $limit = 50): array
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.user_id = ? ORDER BY videos.created_at DESC LIMIT ?',
            [$userId, $limit]
        );
        return array_map([self::class, 'hydrate'], $rows);
    }

    /**
     * Full-text search across title and description.
     */
    public static function search(string $query, int $limit = 20): array
    {
        $like = '%' . $query . '%';
        $rows = Database::getInstance()->fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.title LIKE ? OR videos.description LIKE ?
             ORDER BY videos.created_at DESC LIMIT ?',
            [$like, $like, $limit]
        );
        return array_map([self::class, 'hydrate'], $rows);
    }

    /**
     * Build a Video object from a DB row.
     */
    private static function hydrate(array $row): Video
    {
        $video = new Video();
        $video->videoId      = (int) $row['video_id'];
        $video->userId       = (int) $row['user_id'];
        $video->username     = $row['username'] ?? null;
        $video->title        = $row['title'];
        $video->description  = $row['description'];
        $video->url          = $row['url'];
        $video->thumbnailUrl = $row['thumbnail_url'];
        $video->durationSec  = (int) $row['duration_sec'];
        $video->viewCount    = (int) $row['view_count'];
        $video->createdAt    = $row['created_at'];
        return $video;
    }
}