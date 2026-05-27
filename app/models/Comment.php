<?php

/**
 * Comment model — one object = one row in the `comments` table.
 *
 * Use the static finders (findById / listByVideo / findReplies) to load
 * comments from the database, or do `new Comment()` + ->save() to post
 * a brand new one.
 */
class Comment
{
    public $commentId       = null;
    public $userId          = 0;
    public $username        = null;  // joined from the users table
    public $videoId         = 0;
    public $parentCommentId = null;
    public $content         = '';
    public $createdAt       = null;

    // Find one comment by its ID.
    public static function findById($id)
    {
        $row = Database::fetchOne(
            'SELECT comments.*, users.username
             FROM comments
             LEFT JOIN users ON comments.user_id = users.user_id
             WHERE comments.comment_id = ?',
            [$id]
        );
        return self::fromRow($row);
    }

    // Get top-level comments on a video, newest first.
    public static function listByVideo($videoId, $limit = 100)
    {
        $rows = Database::fetchAll(
            'SELECT comments.*, users.username
             FROM comments
             LEFT JOIN users ON comments.user_id = users.user_id
             WHERE comments.video_id = ? AND comments.parent_comment_id IS NULL
             ORDER BY comments.created_at DESC LIMIT ?',
            [$videoId, $limit]
        );
        return self::fromRows($rows);
    }

    // Get replies to a given comment, oldest first.
    public static function findReplies($commentId, $limit = 100)
    {
        $rows = Database::fetchAll(
            'SELECT comments.*, users.username
             FROM comments
             LEFT JOIN users ON comments.user_id = users.user_id
             WHERE comments.parent_comment_id = ?
             ORDER BY comments.created_at ASC LIMIT ?',
            [$commentId, $limit]
        );
        return self::fromRows($rows);
    }

    // Save this comment. New comment -> INSERT, existing -> UPDATE.
    public function save()
    {
        if ($this->commentId === null) {
            Database::query(
                'INSERT INTO comments (user_id, video_id, parent_comment_id, content)
                 VALUES (?, ?, ?, ?)',
                [$this->userId, $this->videoId, $this->parentCommentId, $this->content]
            );
            $this->commentId = Database::lastInsertId();
        } else {
            Database::query(
                'UPDATE comments
                 SET content = ?
                 WHERE comment_id = ?',
                [$this->content, $this->commentId]
            );
        }
    }

    // Delete this comment.
    public function delete()
    {
        if ($this->commentId !== null) {
            Database::query(
                'DELETE FROM comments WHERE comment_id = ?',
                [$this->commentId]
            );
        }
    }

    // Can the given user delete this comment?
    // The author, the owner of the video, and admins all qualify.
    public function canDelete($currentUser, $videoOwnerId)
    {
        if ($currentUser === null) {
            return false;
        }
        return $currentUser->role   === User::ROLE_ADMIN
            || $currentUser->userId === $this->userId
            || $currentUser->userId === $videoOwnerId;
    }

    // Turn one database row into a Comment object (or null).
    private static function fromRow($row)
    {
        if ($row === null) {
            return null;
        }

        $comment = new Comment();
        $comment->commentId       = (int) $row['comment_id'];
        $comment->userId          = (int) $row['user_id'];
        $comment->username        = isset($row['username']) ? $row['username'] : null;
        $comment->videoId         = (int) $row['video_id'];
        $comment->parentCommentId = $row['parent_comment_id'] !== null ? (int) $row['parent_comment_id'] : null;
        $comment->content         = $row['content'];
        $comment->createdAt       = $row['created_at'];
        return $comment;
    }

    // Turn many database rows into an array of Comment objects.
    private static function fromRows($rows)
    {
        $comments = [];
        foreach ($rows as $row) {
            $comments[] = self::fromRow($row);
        }
        return $comments;
    }
}
