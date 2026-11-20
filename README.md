# Troxela — Social Media Website

A complete social media website built with **Laravel 13** (PHP 8.3), styled with the
**troxela.store theme** (Instrument Sans font, dark background `#0A0B0D`, card `#1A1D21`,
emerald accent `#10B981`, with a light-theme toggle).

## Features

- **Auth** — register / log in / log out, with username, name, bio, avatar upload and date of birth
- **Profiles** — public pages at `/@username`, follow/unfollow, followers & following lists, edit profile
- **Posts** — text + optional image upload, edit/delete your own posts, home feed (people you follow + you)
- **Likes** — like/unlike with counts
- **Comments** — comment on posts, delete your own comments (or comments on your posts)
- **Follow** — follow/unfollow users
- **Search** — search users by name/username and posts by text
- **Messages** — direct messages (conversation list + thread, unread counts)
- **Notifications** — database notifications for likes, comments, follows and messages (bell with unread count, mark as read)
- **Explore** — recent posts from everyone
- **Theme** — dark by default (troxela.store colours), light/dark toggle stored in a cookie

## Run it locally

```bash
composer install
cp .env.example .env        # if .env does not exist yet
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve           # open http://127.0.0.1:8000
```

## Demo login (seeded)

- Email: **idoyoudocan@gmail.com**
- Password: **demo-password-123** — this is only a demo placeholder. Change it immediately after first login; do not use it in production.

Sample users (ayesha, tanvir, nusrat, rahim) use the same demo password.

## Deploy on shared hosting / troxela.store

1. Upload the project files to the server (or `git clone` the GitHub repository below).
2. Point the domain's document root at the `public/` directory.
3. Copy `.env.example` to `.env` and set `APP_URL=https://troxela.store`, `APP_ENV=production`,
   `APP_DEBUG=false`, and the production database credentials (MySQL/PostgreSQL).
4. Run: `composer install --no-dev --optimize-autoloader`
5. Run: `php artisan key:generate --force && php artisan migrate --force && php artisan storage:link`
6. Make sure `storage/` and `bootstrap/cache/` are writable by the web server.
7. Set the queue/mail settings in `.env` if you want email notifications.

## GitHub

Code is pushed to the public repository for this project (see the repository URL in the
project notes). Pull the latest code with `git pull` before deploying.
