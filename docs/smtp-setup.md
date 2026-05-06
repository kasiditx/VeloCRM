# SMTP Setup

## Required Fields

- mail host
- mail port
- encryption
- username
- password
- sender email
- sender name

## Where to Configure

- during installer onboarding
- later in `Admin > Settings > SMTP`

## Validation Notes

- `mail_from_address` must be a valid email address
- if your provider does not use encryption, choose `none`
- some shared hosts require the full mailbox address as the SMTP username

## Verification

Use the SMTP test action from admin settings after installation.
