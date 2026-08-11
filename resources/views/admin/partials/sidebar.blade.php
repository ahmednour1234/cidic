@php
    use App\Enums\Permission;

    // Badge count is resolved by the AdminNavComposer.
    $newRequests = $newRequestsCount ?? 0;
@endphp

<aside class="admin-sidebar" data-admin-sidebar>
    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand">
        @if ($sidebarMark = setting_image('logo_mark'))
            <img src="{{ $sidebarMark }}" alt="" class="admin-sidebar__brand-img">
        @else
            <span class="admin-sidebar__brand-mark">CIDIC</span>
        @endif
        <span>
            <span class="d-block fw-bold">{{ setting('company_name_ar', 'سدك للإستقدام') }}</span>
            <small class="opacity-75">لوحة التحكم</small>
        </span>
    </a>

    <nav class="admin-sidebar__nav">
        <a href="{{ route('admin.dashboard') }}"
           class="admin-sidebar__link @if(request()->routeIs('admin.dashboard')) active @endif">
            <span class="admin-sidebar__icon" aria-hidden="true">&#9632;</span>
            الرئيسية
        </a>

        @can(Permission::ManageCandidates->value)
            <div class="admin-sidebar__heading">السير الذاتية</div>
            <a href="{{ route('admin.candidates.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.candidates.index') || request()->routeIs('admin.candidates.show') || request()->routeIs('admin.candidates.edit')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9776;</span>
                جميع السير
            </a>
            <a href="{{ route('admin.candidates.create') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.candidates.create')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#43;</span>
                إضافة سيرة
            </a>
            <a href="{{ route('admin.candidates.bulk') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.candidates.bulk')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#8679;</span>
                رفع سير متعددة
            </a>
        @endcan

        @can(Permission::ManageRequests->value)
            <div class="admin-sidebar__heading">الطلبات</div>
            <a href="{{ route('admin.candidate-requests.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.candidate-requests.*') && ! request()->boolean('only_new')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9993;</span>
                طلبات العملاء
                @if ($newRequests > 0)
                    <span class="badge bg-danger rounded-pill">{{ $newRequests }}</span>
                @endif
            </a>
            <a href="{{ route('admin.candidate-requests.index', ['status' => 'new']) }}"
               class="admin-sidebar__link">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9679;</span>
                طلبات جديدة
            </a>
            <a href="{{ route('admin.recruitment-requests.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.recruitment-requests.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9998;</span>
                طلبات الاستقدام
            </a>
        @endcan

        @can(Permission::ManageServices->value)
            <div class="admin-sidebar__heading">البيانات الأساسية</div>
            <a href="{{ route('admin.services.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.services.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9881;</span>
                الخدمات
            </a>
            <a href="{{ route('admin.nationalities.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.nationalities.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9873;</span>
                الجنسيات
            </a>
            <a href="{{ route('admin.categories.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.categories.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9782;</span>
                تصنيفات العمالة
            </a>
        @endcan

        @can(Permission::ManageContent->value)
            <div class="admin-sidebar__heading">المحتوى</div>
            <a href="{{ route('admin.how-it-works.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.how-it-works.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9654;</span>
                كيف نعمل
            </a>
            <a href="{{ route('admin.why-choose-us.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.why-choose-us.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9733;</span>
                لماذا نحن
            </a>
            <a href="{{ route('admin.testimonials.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.testimonials.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#10077;</span>
                آراء العملاء
            </a>
            <a href="{{ route('admin.faqs.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.faqs.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#63;</span>
                الأسئلة الشائعة
            </a>
            <a href="{{ route('admin.pages.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.pages.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9636;</span>
                إدارة صفحات الموقع
            </a>
        @endcan

        @can(Permission::ManageRequests->value)
            <div class="admin-sidebar__heading">التواصل</div>
            <a href="{{ route('admin.contact-messages.index') }}"
               class="admin-sidebar__link @if(request()->routeIs('admin.contact-messages.*')) active @endif">
                <span class="admin-sidebar__icon" aria-hidden="true">&#9990;</span>
                رسائل التواصل
                @if (($newMessagesCount ?? 0) > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $newMessagesCount }}</span>
                @endif
            </a>
        @endcan

        @canany([Permission::ManageSettings->value, Permission::ManageUsers->value])
            <div class="admin-sidebar__heading">النظام</div>
            @can(Permission::ManageSettings->value)
                <a href="{{ route('admin.settings.edit') }}"
                   class="admin-sidebar__link @if(request()->routeIs('admin.settings.*')) active @endif">
                    <span class="admin-sidebar__icon" aria-hidden="true">&#9881;</span>
                    إعدادات الموقع
                </a>
            @endcan
            @can(Permission::ManageUsers->value)
                <a href="{{ route('admin.users.index') }}"
                   class="admin-sidebar__link @if(request()->routeIs('admin.users.*')) active @endif">
                    <span class="admin-sidebar__icon" aria-hidden="true">&#9823;</span>
                    المستخدمون
                </a>
            @endcan
        @endcanany

        <div class="admin-sidebar__heading">الحساب</div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="admin-sidebar__link w-100 border-0 bg-transparent text-start">
                <span class="admin-sidebar__icon" aria-hidden="true">&#8592;</span>
                تسجيل الخروج
            </button>
        </form>
    </nav>
</aside>
