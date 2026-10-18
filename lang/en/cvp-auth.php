<?php

return [
    'login' => [
        'page_title' => 'Sign in',
        'tagline' => 'Manage domestic worker CVs — from upload to contract, all in one place.',
        'points' => [
            'Upload CVs and organise them by nationality',
            'Reserve CVs for clients and follow them through to hiring',
            'A public page for each nationality to share with your clients',
        ],
        'heading' => 'Sign in',
        'lead' => 'Enter your details to access the CV panel.',
        'email' => 'Email address',
        'password' => 'Password',
        'show_password' => 'Show password',
        'hide_password' => 'Hide password',
        'remember' => 'Remember me',
        'submit' => 'Sign in',
        'submitting' => 'Signing in…',
    ],
    'guide' => [
        'notice' => '<strong>Important:</strong> reservations never expire on their own. A CV stays reserved until a staff member releases it manually from the panel.',
        'sections' => [
            'roles' => [
                'title' => 'Roles',
                'items' => [
                    'Coordination: uploads CVs for their nationalities, deletes them, follows up reserved CVs and marks them hired.',
                    'Customer service: reserves CVs for clients, cancels their own reservations and creates contracts.',
                    'Branch manager: supervises, manages users and assigns coordinators to nationalities.',
                    'General manager: full permissions across all nationalities.',
                ],
            ],
            'upload' => [
                'title' => 'Uploading',
                'items' => [
                    'Choose the nationality, experience and religion — experience and religion are required because the public site filters by them.',
                    'Upload up to 100 PDF files at once, each up to 10 MB.',
                    'The worker\'s name is taken from the file name without the extension.',
                    'Duplicate files (same name) are skipped automatically and listed in the result message.',
                    'The option to delete old CVs never touches reserved CVs or those linked to a client or contract.',
                ],
            ],
            'public' => [
                'title' => 'Public pages',
                'items' => [
                    'Each nationality has a public link you can copy from the home page and send to clients.',
                    'The public site only shows available CVs that have never been withdrawn.',
                    'Passport and phone details never appear on the public site.',
                ],
            ],
            'reserve' => [
                'title' => 'Reserving',
                'items' => [
                    'Pick a registered client, or enter a name and mobile number to create a new client instantly.',
                    'WhatsApp clients are usually unregistered — no passport is needed at this step.',
                    'The CV disappears from the public site as soon as it is reserved.',
                    'A reservation never expires automatically. It stays until a staff member releases it manually.',
                ],
            ],
            'after_reserve' => [
                'title' => 'After reserving',
                'items' => [
                    'The actions (create contract, Tamara, cancel reservation) are available only to whoever reserved, or to the general manager.',
                    '“Create contract” creates the contract, sets the status to “Hired” and removes it from the list.',
                    '“Hired” is the end of the cycle and cannot be undone from the panel.',
                    'Cancelling a reservation sets the status back to “Available”, but the CV stays withdrawn from the public site permanently.',
                ],
            ],
            'follow_up' => [
                'title' => 'Following up reservations',
                'items' => [
                    'The “Reserved” page shows what has been reserved from your nationalities, and what has been hired.',
                    'The “Hired” button ends the cycle — be sure before pressing it.',
                ],
            ],
            'delete' => [
                'title' => 'Deleting',
                'items' => [
                    'Deletion is always soft: the record and the file are both kept.',
                    'A CV that is reserved, linked to a client or has a contract cannot be deleted.',
                    'Bulk delete skips ineligible CVs and tells you how many.',
                ],
            ],
            'users' => [
                'title' => 'Users and nationalities',
                'items' => [
                    'Users are deactivated, never deleted. A new user inherits the branch of whoever created them.',
                    'Assigning a coordinator to nationalities determines what they see, upload and delete.',
                    'When a user\'s department is changed away from coordination, their nationalities are unassigned automatically.',
                ],
            ],
        ],
    ],
];
