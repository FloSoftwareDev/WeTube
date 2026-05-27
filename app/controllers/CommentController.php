<?php

/**
 * CommentController — posting, replying to, and deleting comments.
 *
 * All three routes are behind the 'auth' middleware (see config/routes.php),
 * so we can trust $_SESSION['user_id'] exists.
 */
class CommentController
{
    // Post a new top-level comment on a video.
    public function store($videoId)
    {
        $videoId = (int) $videoId;
        $video   = Video::findById($videoId);
        if ($video === null) {
            http_response_code(404);
            echo '404 — Video not found';
            return;
        }

        $content = $this->readContent();
        if ($content === null) {
            $_SESSION['flash_error'] = 'Comment cannot be empty.';
            header('Location: /WeTube/public/watch/' . $videoId);
            exit;
        }

        $comment          = new Comment();
        $comment->userId  = (int) $_SESSION['user_id'];
        $comment->videoId = $videoId;
        $comment->content = $content;
        $comment->save();

        header('Location: /WeTube/public/watch/' . $videoId);
        exit;
    }

    // Reply to an existing comment.
    public function reply($commentId)
    {
        $parent = Comment::findById((int) $commentId);
        if ($parent === null) {
            http_response_code(404);
            echo '404 — Comment not found';
            return;
        }

        $content = $this->readContent();
        if ($content === null) {
            $_SESSION['flash_error'] = 'Reply cannot be empty.';
            header('Location: /WeTube/public/watch/' . $parent->videoId);
            exit;
        }

        $reply                  = new Comment();
        $reply->userId          = (int) $_SESSION['user_id'];
        $reply->videoId         = $parent->videoId;
        $reply->parentCommentId = $parent->commentId;
        $reply->content         = $content;
        $reply->save();

        header('Location: /WeTube/public/watch/' . $parent->videoId);
        exit;
    }

    // Delete a comment. Author, video owner, and admins can all delete.
    public function destroy($commentId)
    {
        $comment = Comment::findById((int) $commentId);
        if ($comment === null) {
            http_response_code(404);
            echo '404 — Comment not found';
            return;
        }

        $video        = Video::findById($comment->videoId);
        $videoOwnerId = $video === null ? 0 : $video->userId;
        $currentUser  = User::findById((int) $_SESSION['user_id']);

        if (!$comment->canDelete($currentUser, $videoOwnerId)) {
            http_response_code(403);
            echo '403 — Forbidden';
            return;
        }

        $comment->delete();

        header('Location: /WeTube/public/watch/' . $comment->videoId);
        exit;
    }

    // ---- Private helpers ----

    // Read & trim the submitted comment content. Returns null if empty.
    private function readContent()
    {
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        return $content === '' ? null : $content;
    }
}
