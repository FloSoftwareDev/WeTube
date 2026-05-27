<?php

/**
 * Video model — one object = one row in the `videos` table.
 *
 * Use the static finders (findById / listLatest / listByUser / search) to
 * load videos from the database, or do `new Video()` + ->save() to upload
 * a brand new one.
 */
class Video
{
    public $videoId      = null;
    public $userId       = 0;
    public $username     = null;  // joined from the users table
    public $title        = '';
    public $description  = null;
    public $url          = '';
    public $thumbnailUrl = null;
    public $durationSec  = 0;
    public $viewCount    = 0;
    public $createdAt    = null;

    // Find one video by its ID.
    public static function findById($id)
    {
        $row = Database::fetchOne(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.video_id = ?',
            [$id]
        );
        return self::fromRow($row);
    }

    // Get the newest videos. Used on the homepage.
    public static function listLatest($limit = 20)
    {
        $rows = Database::fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             ORDER BY videos.created_at DESC LIMIT ?',
            [$limit]
        );
        return self::fromRows($rows);
    }

    // Get all videos uploaded by a specific user.
    public static function listByUser($userId, $limit = 50)
    {
        $rows = Database::fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.user_id = ?
             ORDER BY videos.created_at DESC LIMIT ?',
            [$userId, $limit]
        );
        return self::fromRows($rows);
    }

    // Search videos by title or description.
    public static function search($query, $limit = 20)
    {
        $like = '%' . $query . '%';
        $rows = Database::fetchAll(
            'SELECT videos.*, users.username
             FROM videos
             LEFT JOIN users ON videos.user_id = users.user_id
             WHERE videos.title LIKE ? OR videos.description LIKE ?
             ORDER BY videos.created_at DESC LIMIT ?',
            [$like, $like, $limit]
        );
        return self::fromRows($rows);
    }

    // Save this video. New video -> INSERT, existing -> UPDATE.
    public function save()
    {
        if ($this->videoId === null) {
            Database::query(
                'INSERT INTO videos (user_id, title, description, url, thumbnail_url, duration_sec)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$this->userId, $this->title, $this->description, $this->url, $this->thumbnailUrl, $this->durationSec]
            );
            $this->videoId = Database::lastInsertId();
        } else {
            Database::query(
                'UPDATE videos
                 SET title = ?, description = ?, thumbnail_url = ?
                 WHERE video_id = ?',
                [$this->title, $this->description, $this->thumbnailUrl, $this->videoId]
            );
        }
    }

    // Turn one database row into a Video object (or null).
    private static function fromRow($row)
    {
        if ($row === null) {
            return null;
        }

        $video = new Video();
        $video->videoId      = (int) $row['video_id'];
        $video->userId       = (int) $row['user_id'];
        $video->username     = isset($row['username']) ? $row['username'] : null;
        $video->title        = $row['title'];
        $video->description  = $row['description'];
        $video->url          = $row['url'];
        $video->thumbnailUrl = $row['thumbnail_url'];
        $video->durationSec  = (int) $row['duration_sec'];
        $video->viewCount    = (int) $row['view_count'];
        $video->createdAt    = $row['created_at'];
        return $video;
    }

    // Turn many database rows into an array of Video objects.
    private static function fromRows($rows)
    {
        $videos = [];
        foreach ($rows as $row) {
            $videos[] = self::fromRow($row);
        }
        return $videos;
    }
}
