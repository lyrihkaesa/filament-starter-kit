# User Resource Improvements

This document tracks the implementation of the requested improvements to the User Resource.

## Changes Made
1. **Password Field (UserForm.php):**
   - Added `->revealable()` to show an eye icon for toggling visibility.
   - Updated validation to `->required(fn (string $operation): bool => $operation === 'create')` to prevent forcing password changes on every edit.
   - Added `->dehydrated(fn (?string $state) => filled($state))` to ensure empty passwords aren't saved when editing.

2. **Roles in Table (UsersTable.php):**
   - Added `TextColumn::make('roles.name')->badge()` for clear role visibility.
   - Updated the query builder eager loading to included `'roles'` alongside `'avatarMedia'`.

3. **Roles in Infolist (UserInfolist.php):**
   - Added `TextEntry::make('roles.name')->badge()` to show roles when viewing user details.

## Status
Completed.
