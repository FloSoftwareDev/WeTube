# WeTube

A simplified YouTube-style video platform built with vanilla PHP. Upload videos, leave comments, like what you enjoy, and search for more.

## Features

- **User accounts** — register, log in, manage your profile
- **Video uploads** — upload, edit, and delete your own videos
- **Watch page** — view videos with view count and like count
- **Comments** — post comments and reply to others
- **Likes** — like videos (one tap to toggle)
- **Search** — find videos by title
- **Responsive design** — works on desktop and mobile
- **Dark mode** — because it's 2026

## Tech Stack

- **Backend:** PHP 8+ (no framework — custom MVC)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, minimal JavaScript
- **Fonts:** Poppins / Roboto

## Design System

| | |
|---|---|
| Primary accent | `#FF0000` |
| Background | `#111111` |
| Surface | `#222222` |
| Muted / borders | `#666666` |
| Text | `#F5F5F5` |

Typography: Poppins / Roboto · H1, H2 headings · H5-size body text.

## Project Structure

```
/WeTube
 ├── /app
 │   ├── /controllers
 │   │   ├── AuthController.php
 │   │   ├── VideoController.php
 │   │   └── CommentController.php
 │   ├── /services
 │   │   └── AuthService.php
 │   └── /models
 │       ├── User.php
 │       ├── Video.php
 │       └── Comment.php
 ├── /core
 │   ├── Database.php      (PDO singleton)
 │   └── Router.php        (request dispatcher)
 ├── /views
 │   ├── /layouts          (header.php, footer.php)
 │   ├── /auth             (login.php, register.php)
 │   └── /videos           (index, show, upload, edit)
 ├── /public
 │   ├── index.php         (entry point)
 │   ├── /css
 │   ├── /js
 │   └── /uploads          (videos, thumbnails, avatars)
 └── /config
     └── config.php
```

Only `/public` is exposed to the web. Everything else lives above the web root so users can't access your config or source directly.

## Architecture

**MVC with a lightweight service layer:**

- **Models** handle data and database queries (active record style)
- **Controllers** receive HTTP requests, call the right methods, render views
- **AuthService** handles cross-cutting auth logic (session, login, password hashing)
- **Core** contains the plumbing — routing and database connection

Every request flows through `/public/index.php`, which matches the URL against the router, which calls the appropriate controller method.

## Routes

| Method | Path | Action |
|---|---|---|
| GET | `/` | Homepage (latest videos) |
| GET | `/watch/{id}` | Watch a video |
| GET | `/search?q=...` | Search results |
| GET, POST | `/upload` | Upload a video |
| GET | `/video/{id}/edit` | Edit a video |
| POST | `/video/{id}` | Save edits |
| POST | `/video/{id}/delete` | Delete a video |
| POST | `/video/{id}/like` | Like / unlike |
| POST | `/video/{id}/comment` | Post a comment |
| POST | `/comment/{id}/reply` | Reply to a comment |
| POST | `/comment/{id}/delete` | Delete a comment |
| GET, POST | `/login` | Log in |
| GET, POST | `/register` | Register |
| POST | `/logout` | Log out |

## Installation

### Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10+
- Apache with `mod_rewrite` enabled (or Nginx with equivalent config)
- A `.htaccess` file in `/public` that rewrites all requests through `index.php`

### Setup

1. Clone the repo into your web root:
   ```
   git clone <repo-url> wetube
   ```

2. Point your web server's document root at `/WeTube/public`.

3. Create the database and import the schema:
   ```
   mysql -u root -p < schema.sql
   ```

4. Copy `config/config.example.php` to `config/config.php` and fill in your DB credentials.

5. Make sure `public/uploads/` is writable:
   ```
   chmod -R 755 public/uploads
   ```

6. Visit your local domain — the homepage should load.

## Development Notes

- **Entry point:** all requests go through `public/index.php`. This is where routes are defined and dispatched.
- **Path constants:** `ROOT_PATH`, `APP_PATH`, `VIEWS_PATH`, `CONFIG_PATH` are defined in the bootstrap so includes work from anywhere.
- **Authorization:** `canEdit(user)` and `canDelete(user)` live on the models themselves. Controllers check these before performing destructive actions.
- **Sessions:** started in the bootstrap via `session_start()`. Logged-in user is tracked in `$_SESSION['user_id']`.

## Out of Scope

To keep the project simple, the following are intentionally not included:

- Subscriptions / channels
- Playlists
- Video categories and tags
- Notifications
- Comment likes
- Video recommendations / algorithms
- Live streaming

These may be added later if time permits.

## License

Educational project. Not for commercial use.