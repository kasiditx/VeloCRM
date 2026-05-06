# Customization

## Safe Customization Areas

- branding settings
- logo and favicon
- color theme
- locale selection
- currency and date format

## Code-Level Changes

If you customize the codebase:

- keep backups before updates
- document any edited files
- avoid changing migrations that already ran in production
- re-test invoices, reports, and recurring billing flows after changes

## Asset and Upload Paths

- branding assets: `public/uploads/branding`
- attachments: `public/uploads/attachments`

## Localization

English and Thai strings are stored in:

- `lang/en.json`
- `lang/th.json`
