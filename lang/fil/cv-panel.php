<?php

// Filipino mirror of lang/en/cv-panel.php. Keys must stay in sync and unique.
return [
    'language' => 'Wika',
    'title' => 'CV Panel',

    'nav' => [
        'home' => 'Home',
        'cvs' => 'Mga CV',
        'upload' => 'Mag-upload ng mga CV',
        'reserved' => 'Naka-reserve',
        'guide' => 'Gabay',
        'coordinators' => 'Mga coordinator at nasyonalidad',
        'users' => 'Mga user',
        'logout' => 'Mag-log out',
    ],

    'login' => [
        'inactive' => 'Hindi aktibo ang iyong account. Mangyaring makipag-ugnayan sa iyong manager.',
        'denied' => 'Wala kang access sa CV panel.',
        'failed' => 'Hindi tugma ang mga kredensyal na ito sa aming mga rekord.',
    ],

    'reserve' => [
        'unavailable' => 'Hindi na maaaring i-reserve ang CV na ito.',
        'missing_client' => 'Pumili ng rehistradong kliyente, o ilagay ang pangalan at telepono ng kliyente.',
        'success' => 'Na-reserve na ang CV.',
        'intro' => 'Piliin ang kliyenteng humiling ng CV na ito. Aalisin ito sa pampublikong site sa sandaling ma-reserve, at mananatiling naka-reserve hanggang sa i-release ito ng isang staff.',
        'owner_only' => 'Ang nag-reserve lamang ang maaaring kumilos dito',
    ],

    'cancel' => [
        'has_contract' => 'Hindi maaaring i-release dito ang reservation na ito dahil may naka-link na kontrata — alisin ang link mula sa pahina ng kontrata.',
        'not_owner' => 'Ang staff na gumawa ng reservation, o isang super admin, lamang ang maaaring magkansela nito.',
        'success' => 'Nakansela ang reservation. Nananatiling inalis sa pampublikong site ang CV.',
    ],

    'tamara' => [
        'not_allowed' => 'Hindi maaaring itala ang bayad sa Tamara para sa CV na ito.',
        'success' => 'Naitala ang bayad sa Tamara.',
        'paid_badge' => 'Binayaran sa Tamara',
    ],

    'assigned' => [
        'only_reserved' => 'Ang mga naka-reserve na CV lamang ang maaaring markahan bilang naka-assign.',
        'success' => 'Minarkahang naka-assign ang manggagawa.',
    ],

    'contract' => [
        'create' => 'Gumawa ng kontrata',
        'created' => 'Nagawa ang kontrata :number at na-assign ang manggagawa.',
        'confirm' => 'Gumawa ng kontrata sa recruitment para sa manggagawang ito? Magiging "naka-assign" ang status at aalis ang row sa listahang ito.',
        'exists' => 'May naka-link nang kontrata sa manggagawang ito.',
    ],

    'upload' => [
        'success' => ':count CV ang na-upload.',
        'duplicates' => ':count dobleng file ang nilaktawan (:names).',
        'purged' => ':count lumang CV ang inalis.',
        'hint' => 'Hanggang 100 file, bawat isa ay hindi lalampas sa 10 MB.',
        'selected' => ':count file ang napili',
        'purge_label' => 'Burahin ang mga luma kong CV para sa nasyonalidad na ito (:count)',
        'purge_note' => 'Ang mga CV lang na ikaw ang nag-upload ang mabubura. Hindi kailanman binubura ang mga naka-reserve na CV, may kliyente, o may kontrata.',
    ],

    'delete' => [
        'bulk_result' => 'Nabura ang :deleted, nilaktawan ang :skipped.',
        'confirm_bulk' => 'Burahin ang mga napiling CV? Mawawala ang mga ito sa panel at sa website; mananatili ang kanilang mga rekord at file.',
        'not_allowed' => 'Hindi maaaring burahin ang CV na ito.',
    ],

    'list' => [
        'heading' => 'Mga CV (:total)',
        'search' => 'Maghanap ayon sa pangalan o numero',
        'all_nationalities' => 'Lahat ng nasyonalidad',
        'all_statuses' => 'Lahat ng status',
        'all_experiences' => 'Lahat ng antas ng karanasan',
        'all_religions' => 'Lahat ng relihiyon',
        'filter' => 'I-filter',
        'reset' => 'I-reset',
        'withdrawn' => 'Inalis sa website',
        'reserved_by' => 'Na-reserve ni :name · :time ang nakalipas',
        'selected' => ':count CV ang napili',
        'delete_selected' => 'Burahin ang napili',
        'empty' => 'Walang nakitang CV.',
    ],

    'notifications' => [
        'title' => 'Mga notification',
        'empty' => 'Walang bagong notification',
        'view_all' => 'Tingnan ang lahat ng notification',
        'mark_all' => 'Markahang nabasa lahat',
        'unread_only' => 'Hindi pa nababasa lamang',
        'tab_all' => 'Lahat',
        'tab_reservations' => 'Mga reservation',
        'tab_uploads' => 'Mga upload',
        'tab_unassigned' => 'Na-release',
    ],

    'dashboard' => [
        'available' => 'Mga available na CV',
        'reserved' => 'Naka-reserve',
        'assigned' => 'Naka-assign',
        'today' => 'Idinagdag ngayong araw',
        'my_reservations' => 'Aking mga kasalukuyang reservation',
        'my_completed' => 'Aking mga natapos na reservation',
        'my_today' => 'Na-reserve ngayong araw',
        'nationalities' => 'Mga nasyonalidad',
        'count_available' => ':count available na CV',
        'public_link' => 'Pampublikong link',
        'copy' => 'Kopyahin',
        'copied' => 'Nakopya ang link',
        'open_public' => 'Buksan ang pampublikong pahina',
        'no_nationalities' => 'Wala pang nasyonalidad na naka-assign sa iyo. Mangyaring kumonsulta sa iyong branch manager.',
        'recent_uploads' => 'Mga kamakailang na-upload',
        'recent_reservations' => 'Aking mga kamakailang reservation',
    ],

    'public' => [
        'reserved_title' => 'Naka-reserve na ang manggagawang ito',
        'reserved_body' => 'Paumanhin, na-reserve na ang manggagawang ito para sa ibang kliyente at hindi na available.',
        'empty' => 'Walang available na CV',
    ],
];
