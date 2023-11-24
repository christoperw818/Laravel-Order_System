@extends('layout.layout')
@section('content')
    <section id="bn_sec">
        @auth
            @if (auth()->user()->user_type == 'customer' && auth()->user()->id)
                <div class="customer_dashboard">
                    <div style="display: flex; height:32vh;">
                        <button style="display: none;" id="customer_dahsboard_table_reload_button"></button>
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_new_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style="max-height:27.5vh; overflow-y:hidden;">
                                <table id="customer_dashboard_green_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="showAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_complete_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="customer_dashboard_red_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 38% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="showAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; height:32vh; margin-top:2vh;">
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_progress_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="customer_dashboard_yellow_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="min-width: 120px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="showAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_change') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="customer_dashboard_blue_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="showAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 1)
                <div class="embroidery_freelancer_dashboard">
                    <div style="display: flex; height:32vh;">
                        <button style="display: none;" id="em_freelancer_table_reload_btn"></button>
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_new_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style="max-height:27.5vh; overflow-y:hidden;">
                                <table id="em_freelancer_green_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerEmbroideryShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_progress_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="em_freelancer_yellow_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="min-width: 120px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerEmbroideryShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; height:32vh; margin-top:2vh;">
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_complete_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="em_freelancer_red_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 38% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerEmbroideryShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_change') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="em_freelancer_blue_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                            <th style="max-width: 110px !important; text-align:center;">
                                                {{ __('home.request') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerEmbroideryShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->user_type == 'freelancer' && auth()->user()->category_id == 2)
                <div class="vector_freelancer_dashboard">
                    <div style="display: flex; height:32vh;">
                        <button style="display: none;" id="ve_freelancer_table_reload_btn"></button>
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_new_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style="max-height:27.5vh; overflow-y:hidden;">
                                <table id="ve_freelancer_green_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerVectorShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_progress_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="ve_freelancer_yellow_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="min-width: 120px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerVectorShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; height:32vh; margin-top:2vh;">
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_complete_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="ve_freelancer_red_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 38% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerVectorShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_change') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="ve_freelancer_blue_dashboard_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                            <th style="max-width: 110px !important; text-align:center;">
                                                {{ __('home.request') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="freelancerVectorShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->user_type == 'admin')
                <div class="customer_dashboard">
                    <div style="display: flex; height:32vh;">
                        <button style="display: none;" id="admin_dashboard_table_reload_button"></button>
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_new_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style="max-height:27.5vh; overflow-y:hidden;">
                                <table id="admin_dashboard_green_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="adminShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_complete_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="admin_dashboard_red_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 38% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="adminShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; height:32vh; margin-top:2vh;">
                        <div class="col-lg-6 col-md-12" style="padding-right: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_progress_order') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="admin_dashboard_yellow_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="min-width: 120px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="adminShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12" style="padding-left: 2vw;">
                            <div>
                                <h3><strong>{{ __('home.dashboard_change') }}</strong></h3>
                            </div>
                            <div class="responsive-table" style=" max-height:27.5vh; overflow-y:hidden;">
                                <table id="admin_dashboard_blue_table" class="table table-striped"
                                    style="width:100%; font-size:13px;">
                                    <thead>
                                        <tr>
                                            <th style="padding-left:20px;">{{ __('home.art') }}</th>
                                            <th>{{ __('home.delivery_time') }}</th>
                                            <th>{{ __('home.order') }}</th>
                                            <th style="min-width: 40% !important;">{{ __('home.project') }}</th>
                                            <th style="max-width: 100px !important">{{ __('home.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div style="display: flex; justify-content:flex-end; margin-top: -5px; margin-right:10px;">
                                    <button class="customer_dashboard_all_button"
                                        onClick="adminShowAllOrder()">{{ __('home.dashboard_view_all') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="container-fluid p-0">
                <div id="carouselExampleInterval" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active" data-bs-interval="5000"
                            style="display: flex; justify-content:center">
                            <img src="{{ asset('asset/images/image_slide1.jpg') }}" class="d-block custom-width zoomable"
                                alt="CUTTER & BUCK" width="120%" style="height: calc(100vh - 106px);">
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </section>
@endsection
