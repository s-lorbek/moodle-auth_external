# External Authentication (auth_external)

This Moodle authentication plugin is a specialized fork of the standard Email authentication (`auth_email`). It is designed to facilitate a custom onboarding workflow for new users.

When users register through this plugin, they are assigned the custom authentication type **"external"**. This allows administrators to treat them as a distinct group, requiring manual approval or additional onboarding steps via the companion plugin `local_external_users`.

## Key Features

* **Forked from auth_email:** Retains the reliability of the core email-based registration while adding custom logic.
* **Onboarding Integration:** Automatically flags new users for an onboarding process.
* **Custom User Type:** Assigns users to the `external` auth type for easy filtering and management.

## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to *Site administration > Plugins > Install plugins*.
2. Upload the ZIP file with the plugin code. You should only be prompted to add extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can also be installed by putting the contents of this directory into:

`{your/moodle/dirroot}/auth/external`

Afterwards, log in to your Moodle site as an admin and go to *Site administration > Notifications* to complete the installation.

Alternatively, you can run the following command from your CLI:

```bash
$ php admin/cli/upgrade.php
