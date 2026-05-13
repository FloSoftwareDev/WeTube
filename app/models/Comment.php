<?php
class Comment{
    public $comment;
    public $user;
    public $video;
    public $parentComment;

    function __construct($comment, $user, $video, $parentComment = null){
        $this->comment = $comment;
        $this->user = $user;
        $this->video = $video;
        $this->parentComment = $parentComment;
    }
}