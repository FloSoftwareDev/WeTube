<?php

class VideoController
{
    public function index(): void
    {
        echo "<h1>WeTube homepage</h1>";
        echo "<p>Logged in as: " . htmlspecialchars($_SESSION['username'] ?? 'guest') . "</p>";
        echo '<form method="POST" action="/WeTube/public/logout"><button>Logout</button></form>';
    }
}