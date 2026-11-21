# Troxela — Social Media Website

A complete social media website built with **Laravel 13** (PHP 8.3), styled with the
**troxela.store theme** (Instrument Sans font, dark background `#0A0B0D`, card `#1A1D21`,
emerald accent `#10B981`, with a light-theme toggle).

## Features

- **Auth** — register / log in / log out, with username, name, bio, avatar upload and date of birth
- **Profiles** — public pages at `/@username`, follow/unfollow, followers & following lists, edit profile
- **Posts** — text + optional image upload, edit/delete your own posts, share/repost other people's posts to your profile, home feed (people you follow + you)
- **Likes** — like/unlike with counts
- **Comments** — comment on posts, delete your own comments (or comments on your posts)
- **Follow** — follow/unfollow users
- **Search** — search users by name/username and posts by text or by author name/username
- **Settings** — `/settings` page with account details, a password-reset link and notification actions
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
   3–8 MB; the app itself allows avatars and post images up to 5 MB).
   On troxela.store's cPanel (MultiPHP INI Editor) set at least
   `upload_max_filesize = 8M` and `post_max_size = 16M` — with the
   common 2M default, any phone photo over 2 MB is rejected by PHP
   before the app ever sees it and the form reports
   "The image failed to upload."
4. Restart PHP-FPM / the web server after changing `php.ini`.

**Split layout without shell access (troxela.store).** On this host
the app lives in `troxela_social` while the document root is the
sibling `public_html`, and there is no Terminal, so
`php artisan storage:link` cannot be run and `public_html/storage`
is only a static copy. Uploads land in `storage/app/public` but
nothing ever reaches the served folders, so new posts publish
text-only and new avatars fall back to their initial letter — with
no error anywhere. The code handles this: after every successful
store, `App\Support\ImageUpload` also copies the file into each
web-served storage folder that exists as a real directory —
`public/storage` (when it is not a symlink), the folder named by
`PUBLIC_STORAGE_MIRROR_PATH` in `.env` (on troxela.store set it to
`/home/troxelas/public_html/storage`), and `<app-parent>/public_html/storage`
when it exists. A mirror failure never fails the upload; it is only
logged. Deleting or replacing an image deletes its mirror copies too.
One-time, after deploying: copy the existing uploads from
`storage/app/public/avatars` and `storage/app/public/posts` into
`public_html/storage/avatars` and `public_html/storage/posts` so
previously uploaded photos become visible again.

A failed upload now shows a friendly error on the form instead of a
500 page, and the old avatar/post image is kept until the new one is
stored successfully.

If a user record points at an avatar or post image whose file is not
actually under `public/storage` (for example, accounts whose uploaded
files were never copied to the host, or a host where `storage:link`
was never run), the site falls back gracefully instead of showing a
broken image: a missing avatar shows the initial-letter avatar, and a
missing post image is simply omitted. To show the real uploaded
photos again on the host, copy the files into
`storage/app/public/avatars` and `storage/app/public/posts` and make
sure `public/storage` links to `storage/app/public`
(`php artisan storage:link`).

## Password reset

The site has password reset built in (Laravel's Password broker):
`/forgot-password` sends a reset link by email and
`/reset-password/{token}` sets a new password. No extra database table
is needed — the `password_reset_tokens` table ships with the default
migrations.

By default the app uses the `log` mail driver (`MAIL_MAILER=log` in
`.env.example`), so reset links are only written to
`storage/logs/laravel.log` and no email is actually sent. For real
emails on troxela.store, set these in the host's `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=<your SMTP host>
MAIL_PORT=587
MAIL_USERNAME=<your SMTP username>
MAIL_PASSWORD=<your SMTP password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@troxela.store
```

Then restart PHP-FPM / the web server. With the log driver, copy the
reset URL out of `storage/logs/laravel.log` to test the flow.

## GitHub

Code is pushed to the public repository for this project (see the repository URL in the
project notes). Pull the latest code with `git pull` before deploying.
