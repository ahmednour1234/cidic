<?php

return [
    'login' => [
        'page_title' => 'Mag-sign in',
        'tagline' => 'Pamahalaan ang mga CV ng mga kasambahay — mula sa pag-upload hanggang sa kontrata, sa iisang lugar.',
        'points' => [
            'Mag-upload ng mga CV at ayusin ayon sa nasyonalidad',
            'Mag-reserve ng mga CV para sa kliyente at subaybayan hanggang ma-hire',
            'Pampublikong pahina para sa bawat nasyonalidad na maibabahagi sa mga kliyente',
        ],
        'heading' => 'Mag-sign in',
        'lead' => 'Ilagay ang iyong detalye para ma-access ang CV panel.',
        'email' => 'Email address',
        'password' => 'Password',
        'show_password' => 'Ipakita ang password',
        'hide_password' => 'Itago ang password',
        'remember' => 'Tandaan ako',
        'submit' => 'Mag-sign in',
        'submitting' => 'Nagsa-sign in…',
    ],
    'guide' => [
        'notice' => '<strong>Mahalaga:</strong> hindi kusang nag-e-expire ang reservation kahit gaano katagal. Mananatiling naka-reserve ang CV hangga\'t hindi ito manu-manong inaalis ng isang staff mula sa panel.',
        'sections' => [
            'roles' => [
                'title' => 'Mga tungkulin',
                'items' => [
                    'Coordination: nag-a-upload ng mga CV para sa kanyang mga nasyonalidad, nagbubura ng mga ito, sinusubaybayan ang mga naka-reserve at minamarkahang hired.',
                    'Customer service: nagre-reserve ng mga CV para sa kliyente, kinakansela ang sariling reservation, at gumagawa ng kontrata.',
                    'Branch manager: nangangasiwa, namamahala ng mga user at nag-uugnay ng mga coordinator sa mga nasyonalidad.',
                    'General manager: lahat ng pahintulot sa lahat ng nasyonalidad.',
                ],
            ],
            'upload' => [
                'title' => 'Pag-upload',
                'items' => [
                    'Piliin ang nasyonalidad, karanasan at relihiyon — kailangan ang karanasan at relihiyon dahil ginagamit ito ng pampublikong site sa pag-filter.',
                    'Mag-upload ng hanggang 100 PDF nang sabay-sabay, bawat isa hanggang 10 MB.',
                    'Kinukuha ang pangalan ng manggagawa mula sa pangalan ng file nang walang extension.',
                    'Ang dobleng file (parehong pangalan) ay kusang nilalaktawan at ipinapakita sa mensahe ng resulta.',
                    'Ang opsyong burahin ang mga lumang CV ay hindi gumagalaw sa mga naka-reserve o naka-ugnay sa kliyente o kontrata.',
                ],
            ],
            'public' => [
                'title' => 'Mga pampublikong pahina',
                'items' => [
                    'Bawat nasyonalidad ay may pampublikong link na makokopya mo mula sa home page at maipapadala sa mga kliyente.',
                    'Ipinapakita lang ng pampublikong site ang mga available na CV na hindi pa kailanman binawi.',
                    'Hindi kailanman lumalabas sa pampublikong site ang detalye ng pasaporte at telepono.',
                ],
            ],
            'reserve' => [
                'title' => 'Pag-reserve',
                'items' => [
                    'Pumili ng rehistradong kliyente, o maglagay ng pangalan at mobile number para agad gumawa ng bagong kliyente.',
                    'Karaniwang hindi rehistrado ang mga kliyente mula sa WhatsApp — hindi kailangan ng pasaporte sa hakbang na ito.',
                    'Agad nawawala ang CV sa pampublikong site kapag na-reserve.',
                    'Hindi kailanman kusang nag-e-expire ang reservation. Mananatili ito hanggang manu-manong alisin ng staff.',
                ],
            ],
            'after_reserve' => [
                'title' => 'Pagkatapos mag-reserve',
                'items' => [
                    'Ang mga aksyon (gumawa ng kontrata, Tamara, kanselahin ang reservation) ay para lang sa nag-reserve, o sa general manager.',
                    'Ang “Gumawa ng kontrata” ay gumagawa ng kontrata, ginagawang “Hired” ang status at inaalis ito sa listahan.',
                    'Ang “Hired” ang katapusan ng proseso at hindi na maibabalik mula sa panel.',
                    'Ibinabalik ng pagkansela ng reservation ang status sa “Available”, pero mananatiling permanenteng binawi ang CV sa pampublikong site.',
                ],
            ],
            'follow_up' => [
                'title' => 'Pagsubaybay sa mga naka-reserve',
                'items' => [
                    'Ipinapakita ng pahinang “Naka-reserve” ang mga na-reserve mula sa iyong mga nasyonalidad, at ang mga na-hire na.',
                    'Tinatapos ng button na “Hired” ang proseso — siguraduhin muna bago pindutin.',
                ],
            ],
            'delete' => [
                'title' => 'Pagbura',
                'items' => [
                    'Laging soft ang pagbura: nananatiling naka-save ang record at ang file.',
                    'Hindi mabubura ang CV na naka-reserve, naka-ugnay sa kliyente, o may kontrata.',
                    'Nilalaktawan ng maramihang pagbura ang hindi kwalipikado at sinasabi sa iyo kung ilan.',
                ],
            ],
            'users' => [
                'title' => 'Mga user at nasyonalidad',
                'items' => [
                    'Dine-deactivate ang user, hindi binubura. Minamana ng bagong user ang branch ng gumawa sa kanya.',
                    'Ang pag-uugnay ng coordinator sa mga nasyonalidad ang nagtatakda kung ano ang nakikita, ina-upload at binubura niya.',
                    'Kapag inilipat ang departamento ng user palayo sa coordination, kusang inaalis ang kanyang mga nasyonalidad.',
                ],
            ],
        ],
    ],
];
