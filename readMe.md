# Important

## Caching
For performance reasons this extension uses the SimpleFileCache-Backend of TYPO3 to render email-templates.

As a matter of fact the cached files are saved in `typo3temp/Cache/Data/rkw_mailer` and are by default accessable from the web. This may not be the desired behavior in case of sending e-mails with user-sensitive data.

To change this behavior simply add this lines to your `.htaccess`:

```
RewriteRule ^typo3temp/Cache/Data/rkw_mailer/(.*)$ - [NC,F,L]
```
