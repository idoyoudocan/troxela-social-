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

## Image uploads — if uploads fail on the server

Uploads are stored on the `public` disk under `storage/app/public/avatars`
and `storage/app/public/posts` (those folders ship with the code). If an
image upload fails on a host, check these server settings:

1. `php artisan storage:link` has been run (step 5 above) so uploaded
   files are reachable at `/storage/...`.
2. The web server can write to `storage/app/public`.
3. In the server's `php.ini`, `file_uploads` is On, `upload_tmp_dir`
   exists and is writable, and `upload_max_filesize` / `post_max_size`
   are larger than the photos being uploaded (phone photos are often
   3–8 MB; the app itself allows avatars up to 2 MB and post images up
   to 5 MB).
4. Restart PHP-FPM / the web server after changing `php.ini`.

A failed upload now shows a friendly error on the form instead of a
500 page, and the old avatar/post image is kept until the new one is
stored successfully.

## GitHub

Code is pushed to the public repository for this project (see the repository URL in the
project notes). Pull the latest code with `git pull` before deploying.
