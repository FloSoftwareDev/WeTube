<?php

/**
 * CommentController — placeholder for the comments feature.
 * The methods below are referenced by config/routes.php; they'll
 * be filled in when the comment feature is built.
 */
class CommentController
{
    public function store($videoId)
    {
        // TODO: save a new comment on a video
        header('Location: /WeTube/public/watch/' . (int) $videoId);
        exit;
    }

    public function reply($commentId)
    {
        // TODO: save a reply to an existing comment
        header('Location: /WeTube/public/');
        exit;
    }

    public function destroy($commentId)
    {
        // TODO: delete a comment
        header('Location: /WeTube/public/');
        exit;
    }
}
