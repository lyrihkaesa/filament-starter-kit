# Media Usage Tracking & Deletion Protection System

This document outlines the architecture and implementation of the media protection system, which ensures data integrity by preventing the accidental deletion of media files currently in use within the application.

## Overview

The system consists of three main pillars:
1.  **Usage Tracking**: Automatic recording of which models are using specific media assets.
2.  **Deletion Protection**: UI-level and Policy-level blocks that prevent deleting "In-Use" media.
3.  **Permission-Based Bypass**: A specialized permission (`DeleteUsed:CuratorMedia`) that allows authorized users (like Super Admins) to bypass the protection when necessary.

## 1. Usage Tracking (Automatic)

Media usage is tracked in the `curator_media_usages` table. Instead of manual recording in every controller, we use **Model Observers/Hooks** to ensure consistency.

### Implementation in `Post` Model
The `Post` model automatically synchronizes its `thumbnail_curator_id` with the tracking table:

- **Creating/Updating**: When a post is saved, it checks if the thumbnail has changed and updates the usage record accordingly.
- **Deleting**: Before a post is deleted, its usage records are cleared to "free up" the media for deletion (if no other models are using it).

## 2. Deletion Protection

### UI Level (Filament Curator)
We have overridden the default Curator `MediaResource` with `App\Filament\Curator\MediaResource`. This custom resource uses protected actions:

-   **`CuratorMediaDeleteAction`**: Replaces the standard delete action. It checks if the media is "In-Use" before allowing the deletion modal to proceed.
-   **`CuratorMediaDeleteBulkAction`**: Replaces the bulk delete action. It filters out "In-Use" media from the deletion process unless the user has bypass permissions.

### Visual Feedback
On the **Edit Media** page (`App\Filament\Pages\Media\EditMedia`), a warning message is displayed in the subheading if the media is blocked from deletion.

## 3. Authorization Logic

The protection logic follows this hierarchy:

1.  **Is the media in use?**
    -   **No**: Standard `Delete` or `DeleteOwn` permissions apply.
    -   **Yes**: Check for **Bypass Permission**.
2.  **Does the user have `DeleteUsed:CuratorMedia`?**
    -   **Yes**: Deletion is allowed.
    -   **No**: Deletion is blocked, even for owners or admins.

## 4. Policy Upgrades

### Role Protection (`RolePolicy`)
To prevent accidental lockout or system corruption, the `super_admin` role is protected at the policy level:
-   It cannot be **updated** (permissions/name changed via UI).
-   It cannot be **deleted**.

### Post Ownership (`PostPolicy`)
Supports granular permissions:
-   `UpdateOwn:Post`: User can only edit their own posts.
-   `DeleteOwn:Post`: User can only delete their own posts.

## Configuration

-   **Custom Resource Binding**: Defined in `config/curator.php` under `resources.media`.
-   **Permissions**: Managed via `config/filament-shield.php` and seeded into the database.

---
*Last updated: April 12, 2026*
