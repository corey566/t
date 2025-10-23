@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

<!-- Ambient Background -->
<div class="tw-fixed tw-inset-0 tw-pointer-events-none tw-z-0">
    <div class="tw-absolute tw-top-0 tw-left-1/4 tw-w-96 tw-h-96 tw-bg-blue-400 tw-rounded-full tw-mix-blend-multiply tw-filter tw-blur-3xl tw-opacity-20 tw-animate-float"></div>
    <div class="tw-absolute tw-bottom-0 tw-right-1/4 tw-w-96 tw-h-96 tw-bg-sky-400 tw-rounded-full tw-mix-blend-multiply tw-filter tw-blur-3xl tw-opacity-20 tw-animate-float" style="animation-delay: 2s;"></div>
    <div class="tw-absolute tw-top-1/2 tw-left-1/2 tw-transform tw--translate-x-1/2 tw--translate-y-1/2 tw-w-96 tw-h-96 tw-bg-indigo-400 tw-rounded-full tw-mix-blend-multiply tw-filter tw-blur-3xl tw-opacity-20 tw-animate-float" style="animation-delay: 4s;"></div>
</div>

<!-- Main Content -->
<section class="tw-relative tw-z-10 tw-p-6">

    <!-- Welcome Header with Aurora Effect -->
    <div class="tw-mb-8 tw-relative tw-overflow-hidden tw-rounded-2xl tw-p-8 tw-bg-gradient-to-br tw-from-blue-500/10 tw-via-sky-500/10 tw-to-indigo-500/10 tw-backdrop-blur-sm tw-border tw-border-blue-200/20">
        <div class="aurora-effect"></div>
        <div class="tw-relative tw-z-10">
            <h1 class="tw-text-4xl tw-font-bold tw-mb-2 text-gradient-ambient">
                @lang('home.welcome_message', ['name' => Session::get('user.first_name')])
            </h1>
            <p class="tw-text-gray-600 tw-text-lg">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    @can('dashboard.data')
    <!-- KPI Cards Grid -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">

        <!-- Total Purchase Card -->
        <div class="glass-card tw-rounded-2xl tw-p-6 tw-group tw-cursor-pointer hover:tw-shadow-ambient-lg tw-transition-all tw-duration-300">
            <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
                <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-gradient-to-br tw-from-blue-400 tw-to-blue-600 tw-flex tw-items-center tw-justify-center icon-gradient group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-shopping-cart tw-text-white tw-text-xl"></i>
                </div>
                <span class="tw-text-xs tw-font-semibold tw-px-3 tw-py-1 tw-rounded-full tw-bg-blue-100 tw-text-blue-700">@lang('home.purchase')</span>
            </div>
            <h3 class="tw-text-gray-600 tw-text-sm tw-font-medium tw-mb-2">@lang('home.total_purchase')</h3>
            <div class="tw-flex tw-items-end tw-justify-between">
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900 stat-number">
                    <span class="total_purchase">0</span>
                </p>
                <div class="tw-text-green-600 tw-text-sm tw-font-semibold tw-flex tw-items-center">
                    <i class="fas fa-arrow-up tw-mr-1"></i>
                    <span>12%</span>
                </div>
            </div>
        </div>

        <!-- Total Sell Card -->
        <div class="glass-card tw-rounded-2xl tw-p-6 tw-group tw-cursor-pointer hover:tw-shadow-ambient-lg tw-transition-all tw-duration-300">
            <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
                <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-gradient-to-br tw-from-sky-400 tw-to-cyan-600 tw-flex tw-items-center tw-justify-center icon-gradient group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-money-bill-wave tw-text-white tw-text-xl"></i>
                </div>
                <span class="tw-text-xs tw-font-semibold tw-px-3 tw-py-1 tw-rounded-full tw-bg-sky-100 tw-text-sky-700">@lang('home.sell')</span>
            </div>
            <h3 class="tw-text-gray-600 tw-text-sm tw-font-medium tw-mb-2">@lang('home.total_sell')</h3>
            <div class="tw-flex tw-items-end tw-justify-between">
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900 stat-number">
                    <span class="total_sell">0</span>
                </p>
                <div class="tw-text-green-600 tw-text-sm tw-font-semibold tw-flex tw-items-center">
                    <i class="fas fa-arrow-up tw-mr-1"></i>
                    <span>18%</span>
                </div>
            </div>
        </div>

        <!-- Purchase Due Card -->
        <div class="glass-card tw-rounded-2xl tw-p-6 tw-group tw-cursor-pointer hover:tw-shadow-ambient-lg tw-transition-all tw-duration-300">
            <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
                <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-gradient-to-br tw-from-indigo-400 tw-to-indigo-700 tw-flex tw-items-center tw-justify-center icon-gradient group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-exclamation-circle tw-text-white tw-text-xl"></i>
                </div>
                <span class="tw-text-xs tw-font-semibold tw-px-3 tw-py-1 tw-rounded-full tw-bg-indigo-100 tw-text-indigo-700">@lang('home.due')</span>
            </div>
            <h3 class="tw-text-gray-600 tw-text-sm tw-font-medium tw-mb-2">@lang('home.purchase_due')</h3>
            <div class="tw-flex tw-items-end tw-justify-between">
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900 stat-number">
                    <span class="purchase_due">0</span>
                </p>
                <div class="tw-text-orange-600 tw-text-sm tw-font-semibold tw-flex tw-items-center">
                    <i class="fas fa-arrow-down tw-mr-1"></i>
                    <span>5%</span>
                </div>
            </div>
        </div>

        <!-- Invoice Due Card -->
        <div class="glass-card tw-rounded-2xl tw-p-6 tw-group tw-cursor-pointer hover:tw-shadow-ambient-lg tw-transition-all tw-duration-300">
            <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
                <div class="tw-w-14 tw-h-14 tw-rounded-xl tw-bg-gradient-to-br tw-from-blue-400 tw-to-sky-600 tw-flex tw-items-center tw-justify-center icon-gradient group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-file-invoice-dollar tw-text-white tw-text-xl"></i>
                </div>
                <span class="tw-text-xs tw-font-semibold tw-px-3 tw-py-1 tw-rounded-full tw-bg-blue-100 tw-text-blue-700">@lang('home.invoice')</span>
            </div>
            <h3 class="tw-text-gray-600 tw-text-sm tw-font-medium tw-mb-2">@lang('home.invoice_due')</h3>
            <div class="tw-flex tw-items-end tw-justify-between">
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900 stat-number">
                    <span class="invoice_due">0</span>
                </p>
                <div class="tw-text-orange-600 tw-text-sm tw-font-semibold tw-flex tw-items-center">
                    <i class="fas fa-arrow-down tw-mr-1"></i>
                    <span>3%</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Section -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6 tw-mb-8">

        <!-- Sales Chart -->
        <div class="modern-card tw-rounded-2xl tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">@lang('home.sales_current_month')</h2>
                <button class="tw-px-4 tw-py-2 tw-rounded-lg tw-bg-blue-500 tw-text-white tw-text-sm tw-font-semibold hover:tw-bg-blue-600 tw-transition-colors">
                    @lang('home.view_all')
                </button>
            </div>
            <div id="sell_chart" class="tw-h-80"></div>
        </div>

        <!-- Purchase Chart -->
        <div class="modern-card tw-rounded-2xl tw-p-6">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                <h2 class="tw-text-xl tw-font-bold tw-text-gray-900">@lang('home.purchase_current_month')</h2>
                <button class="tw-px-4 tw-py-2 tw-rounded-lg tw-bg-sky-500 tw-text-white tw-text-sm tw-font-semibold hover:tw-bg-sky-600 tw-transition-colors">
                    @lang('home.view_all')
                </button>
            </div>
            <div id="purchase_chart" class="tw-h-80"></div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="modern-card tw-rounded-2xl tw-p-6">
        <h2 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-6">@lang('home.quick_actions')</h2>
        <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 lg:tw-grid-cols-6 tw-gap-4">
            @can('sell.create')
            <a href="{{action([\App\Http\Controllers\SellPosController::class, 'create'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-blue-50 tw-to-sky-50 hover:tw-from-blue-100 hover:tw-to-sky-100 tw-transition-all tw-border tw-border-blue-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-blue-500 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-plus tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('sale.add_sale')</span>
            </a>
            @endcan

            @can('purchase.create')
            <a href="{{action([\App\Http\Controllers\PurchaseController::class, 'create'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-sky-50 tw-to-cyan-50 hover:tw-from-sky-100 hover:tw-to-cyan-100 tw-transition-all tw-border tw-border-sky-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-sky-500 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-shopping-cart tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('purchase.add_purchase')</span>
            </a>
            @endcan

            @can('product.create')
            <a href="{{action([\App\Http\Controllers\ProductController::class, 'create'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-indigo-50 tw-to-purple-50 hover:tw-from-indigo-100 hover:tw-to-purple-100 tw-transition-all tw-border tw-border-indigo-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-indigo-500 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-box tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('product.add_product')</span>
            </a>
            @endcan

            @can('supplier.create')
            <a href="{{action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'supplier'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-blue-50 tw-to-indigo-50 hover:tw-from-blue-100 hover:tw-to-indigo-100 tw-transition-all tw-border tw-border-blue-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-blue-600 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-user-plus tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('contact.add_supplier')</span>
            </a>
            @endcan

            @can('customer.create')
            <a href="{{action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-sky-50 tw-to-blue-50 hover:tw-from-sky-100 hover:tw-to-blue-100 tw-transition-all tw-border tw-border-sky-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-sky-600 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-user-friends tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('contact.add_customer')</span>
            </a>
            @endcan

            @can('expense.access')
            <a href="{{action([\App\Http\Controllers\ExpenseController::class, 'create'])}}" class="tw-group tw-flex tw-flex-col tw-items-center tw-p-4 tw-rounded-xl tw-bg-gradient-to-br tw-from-indigo-50 tw-to-blue-50 hover:tw-from-indigo-100 hover:tw-to-blue-100 tw-transition-all tw-border tw-border-indigo-200/50">
                <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-indigo-600 tw-flex tw-items-center tw-justify-center tw-mb-3 group-hover:tw-scale-110 tw-transition-transform">
                    <i class="fas fa-money-bill-alt tw-text-white"></i>
                </div>
                <span class="tw-text-sm tw-font-semibold tw-text-gray-700">@lang('expense.add_expense')</span>
            </a>
            @endcan
        </div>
    </div>
    @endcan

</section>

@endsection

@section('javascript')
<script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
@endsection