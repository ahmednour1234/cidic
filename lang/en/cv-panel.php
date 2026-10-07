<?php

// English mirror of lang/ar/cv-panel.php. Keys must stay in sync and unique.
return [
    'title' => 'CV Panel',

    'nav' => [
        'home' => 'Home',
        'cvs' => 'CVs',
        'upload' => 'Upload CVs',
        'reserved' => 'Reserved',
        'guide' => 'Guide',
        'coordinators' => 'Coordinators & nationalities',
        'users' => 'Users',
        'logout' => 'Log out',
    ],

    'login' => [
        'inactive' => 'Your account is not active. Please contact your manager.',
        'denied' => 'You do not have access to the CV panel.',
        'failed' => 'These credentials do not match our records.',
    ],

    'reserve' => [
        'unavailable' => 'This CV is no longer available to reserve.',
        'missing_client' => 'Pick a registered client, or enter a client name and phone.',
        'success' => 'The CV has been reserved.',
        'intro' => 'Pick the client who asked for this CV. It leaves the public site as soon as it is reserved, and stays reserved until a staff member releases it.',
        'owner_only' => 'Only the person who reserved it can act on it',
    ],

    'cancel' => [
        'has_contract' => 'This reservation cannot be released here because a contract is linked — unlink it from the contract page.',
        'not_owner' => 'Only the staff member who made the reservation, or a super admin, can cancel it.',
        'success' => 'The reservation was cancelled. The CV stays withdrawn from the public site.',
    ],

    'tamara' => [
        'not_allowed' => 'A Tamara payment cannot be recorded for this CV.',
        'success' => 'The Tamara payment was recorded.',
        'paid_badge' => 'Paid via Tamara',
    ],

    'assigned' => [
        'only_reserved' => 'Only reserved CVs can be marked as assigned.',
        'success' => 'The worker was marked as assigned.',
    ],

    'contract' => [
        'create' => 'Create contract',
        'created' => 'Contract :number was created and the worker assigned.',
        'confirm' => 'Create a recruitment contract for this worker? The status becomes "assigned" and the row leaves this list.',
        'exists' => 'A contract is already linked to this worker.',
    ],

    'upload' => [
        'success' => ':count CVs uploaded.',
        'duplicates' => ':count duplicate files were skipped (:names).',
        'purged' => ':count old CVs were removed.',
        'hint' => 'Up to 100 files, each no larger than 10 MB.',
        'selected' => ':count files selected',
        'purge_label' => 'Delete this nationality\'s old CVs (:count)',
        'purge_note' => 'Reserved CVs, CVs with a client, and CVs with a contract are never deleted.',
    ],

    'delete' => [
        'bulk_result' => 'Deleted :deleted, skipped :skipped.',
        'confirm_bulk' => 'Delete the selected CVs? They disappear from the panel and the website; their records and files are kept.',
        'not_allowed' => 'This CV cannot be deleted.',
    ],

    'list' => [
        'heading' => 'CVs (:total)',
        'search' => 'Search by name or number',
        'all_nationalities' => 'All nationalities',
        'all_statuses' => 'All statuses',
        'all_experiences' => 'All experience levels',
        'all_religions' => 'All religions',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'withdrawn' => 'Withdrawn from the website',
        'reserved_by' => 'Reserved by :name · :time ago',
        'selected' => ':count CVs selected',
        'delete_selected' => 'Delete selected',
        'empty' => 'No CVs found.',
    ],

    'notifications' => [
        'title' => 'Notifications',
        'empty' => 'No new notifications',
        'view_all' => 'View all notifications',
        'mark_all' => 'Mark all as read',
        'unread_only' => 'Unread only',
        'tab_all' => 'All',
        'tab_reservations' => 'Reservations',
        'tab_uploads' => 'Uploads',
        'tab_unassigned' => 'Released',
    ],

    'dashboard' => [
        'available' => 'Available CVs',
        'reserved' => 'Reserved',
        'assigned' => 'Assigned',
        'today' => 'Added today',
        'my_reservations' => 'My current reservations',
        'my_completed' => 'My completed reservations',
        'my_today' => 'Reserved today',
        'nationalities' => 'Nationalities',
        'count_available' => ':count available CVs',
        'public_link' => 'Public link',
        'copy' => 'Copy',
        'copied' => 'Link copied',
        'open_public' => 'Open the public page',
        'no_nationalities' => 'No nationality has been assigned to you yet. Please check with your branch manager.',
        'recent_uploads' => 'Recently uploaded',
        'recent_reservations' => 'My recent reservations',
    ],

    'public' => [
        'reserved_title' => 'This worker is reserved',
        'reserved_body' => 'Sorry, this worker has been reserved for another client and is no longer available.',
        'empty' => 'No CVs are available',
    ],
];
